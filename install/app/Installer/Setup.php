<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Installer;

use DirectoryIterator;
use FilesystemIterator;
use Imagine\Imagick\Imagine as Imagick;
use Imagine\Gd\Imagine as Gd;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tygh;
use Tygh\BlockManager\Exim;
use Tygh\Bootstrap;
use Tygh\Http;
use Tygh\Languages\Languages;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\Archiver;
use Tygh\Tools\Archivers\ArchiverException;
use Tygh\Tools\Url;

class Setup
{
    const HTTP = 'http';
    const HTTPS = 'https';
    const DEMO_COMPANY_URL = 'acme';
    const MAX_SCRIPT_EXECUTION_TIME = 20;

    private $_cart_settings = array();
    private $_server_settings = array();
    private $_database_settings = array();
    private $_install_demo = true;
    private $_preserve_parsed_files_cache = false;
    private $_is_multivendor = false;
    private $report_data;
    private $updates_server_url;

    /**
     * List of sql scripts that take a long time to import
     * schema.sql cannot be in this list because it has variables declaration inside
     *
     * @var array $_heavy_sql_dumps_list
     */
    private $_heavy_sql_dumps_list = array(
        'data.sql',
        'demo.sql',
        'lang_demo.sql',
    );

    public function __construct($cart_settings = array(), $server_settings = array(), $database_settings = array(), $install_demo = false)
    {
        $this->_cart_settings = $cart_settings;
        $this->_server_settings = $server_settings;
        $this->_database_settings = $database_settings;
        $this->_install_demo = $install_demo;
        $this->_preserve_parsed_files_cache = Registry::get('proceed_exipred_script');
        $this->_is_multivendor = PRODUCT_NAME === 'Multi-Vendor';

        set_error_handler(array($this, 'reportError'));
        register_shutdown_function(array($this, 'reportFatalError'));
    }

    /**
     * Returns list of available languages to install
     *
     * @return array List of languages
     */
    public static function getLanguages()
    {
        $languages = array();

        $langs = fn_get_dir_contents(
            Registry::get('config.dir.install') . APP::DB_LANG,
            true, false
        );

        foreach ($langs as $lang_code) {
            $meta = Languages::getLangPacksMeta(
                Registry::get('config.dir.install') . APP::DB_LANG . '/' . $lang_code . '/',
                $lang_code . '.po',
                false, false
            );
            if (!empty($meta)) {
                $languages[$lang_code] = $meta['name'];
            }
        }

        return $languages;
    }

    /**
     * Returns list of supported DB types
     *
     * @return array List of DB types
     */
    public static function getSupportedDbTypes()
    {
        $supported = array();

        $exts  = get_loaded_extensions();
        $mysqli_support = in_array('mysqli', $exts) ? true : false;
        $pdo_support = in_array('pdo_mysql', $exts) ? true : false;

        if ($mysqli_support) {
            $supported['mysqli'] = 'MySQLi';
        }

        if ($pdo_support) {
            $supported['pdo'] = 'PDO';
        }

        return $supported;
    }

    /**
     * Imports database scheme
     *
     * @return bool Always true
     */
    public function setupScheme()
    {
        $this->_parseSql(Registry::get('config.dir.install') . App::DB_SCHEME, 'creating_scheme');

        $this->_resetParsedFilesCache();

        return true;
    }

    /**
     * Imports database data
     *
     * @return bool Always true
     */
    public function setupData()
    {
        $this->_parseSql(Registry::get('config.dir.install') . App::DB_DATA, 'importing_data');

        if (!App::isTaskComplete('admin_account_created')) {
            $admin_account_created = $this->_createAdminAccount(
                $this->_cart_settings['email'],
                $this->_cart_settings['password']
            );

            App::setSetupTaskProgress('admin_account_created', $admin_account_created);
        }

        $this->_setupAutoFeedback(!empty($this->_cart_settings['feedback_auto']));

        $this->_setupDefaultLanguages($this->_cart_settings['main_language']);

        $this->_resetParsedFilesCache();

        return true;
    }

    /**
     * Imports database demo catalog
     *
     * @return bool Always true
     */
    public function setupDemo()
    {
        $this->_parseSql(Registry::get('config.dir.install') . App::DB_DEMO, 'creating_demo');
        $this->_resetParsedFilesCache();
        $processed_files = isset($_SESSION['processed_files']) ? $_SESSION['processed_files'] : array();

        $demo_data_dir = Registry::get('config.dir.install') . App::DEMO_DIR;

        if (file_exists($demo_data_dir)
            && !in_array('demo_data_dir', $processed_files)
        ) {
            fn_copy($demo_data_dir, Registry::get('config.dir.root'));

            if ($this->isMultivendor()) {
                // remove directory to prevent multiple extractions after redirect
                @unlink($demo_data_dir);

                if (file_exists($demo_data_dir)) {
                    $_SESSION['processed_files'][] = 'demo_data_dir';
                    App::instance()->setNotification(
                        'W',
                        App::instance()->t('error_cannot_remove_dir') . $demo_data_dir,
                        App::instance()->t('error'),
                        true
                    );
                }
            }
        } else {
            $demo_data_archives_path = DIR_ROOT . implode(DIRECTORY_SEPARATOR, array('', 'install', 'demo_archives'));

            if (file_exists($demo_data_archives_path)
                && is_readable($demo_data_archives_path)
            ) {
                /** @var \Tygh\Tools\Archiver $archiver */
                $archiver = new Archiver();

                $iterator = new DirectoryIterator($demo_data_archives_path);

                /** @var DirectoryIterator $fileinfo */
                foreach ($iterator as $fileinfo) {
                    $extracting_error = false;

                    if (!$fileinfo->isDot()
                        && $fileinfo->getExtension() === 'zip'
                        && !in_array($fileinfo->getPathname(), $processed_files)
                    ) {

                        try {
                            $archiver->extractTo($fileinfo->getPathname(), DIR_ROOT);

                            // remove archive to prevent multiple extractions after redirect
                            @unlink($fileinfo->getPathname());

                            if (file_exists($fileinfo->getPathname())) {
                                $extracting_error = true;
                                $message = App::instance()->t('error_cannot_remove_file') . $fileinfo->getPathname();
                            }
                        } catch (ArchiverException $e) {
                            $extracting_error = true;
                            $message = $e->getMessage();
                        }

                        if ($extracting_error) {
                            $_SESSION['processed_files'][] = $fileinfo->getPathname();
                            App::instance()->setNotification(
                                'W',
                                App::instance()->t('error'),
                                $message,
                                true
                            );
                        }

                        if ($this->isMultivendor()
                            && !App::instance()->isConsole()
                        ) {
                            // redirect after transferring demo files as it takes about 20 sec
                            $this->redirectExpiredScript();
                        }
                    }
                }

                if (!in_array('permission_corrected', $processed_files)) {

                    /** @var RecursiveIteratorIterator $files */
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(DIR_ROOT . '/images', FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );

                    foreach ($files as $file) {
                        $name = $file->getFileName();

                        if ($name === 'index.php' || $name === '.htaccess') {
                            continue;
                        }

                        $permissions = DEFAULT_FILE_PERMISSIONS;

                        if ($file->isDir()) {
                            $permissions = DEFAULT_DIR_PERMISSIONS;
                        }

                        @chmod($file->getPathName(), $permissions);
                    }

                    $_SESSION['processed_files'][] = 'permission_corrected';
                }

                @rmdir($demo_data_archives_path);
            }
        }

        return true;
    }

    /**
     * Cleans database. Need be executed if demo catalog not installed
     *
     * @return bool Always true
     */
    public function clean()
    {
        return true;
    }

    /**
     * Returns available themes from repository
     *
     * @return array List of themes with preview images
     */
    public function getAvailableThemes()
    {
        $themes = array();

        $repo_themes = fn_get_dir_contents(Registry::get('config.dir.root') . '/var/themes_repository');

        if (!empty($repo_themes)) {
            foreach ($repo_themes as $theme_name) {
                $themes[$theme_name] = 'var/themes_repository/' . $theme_name . '/customer_screenshot.png';
            }
        }

        return $themes;
    }

    /**
     * Setup themes
     *
     * @return bool True on success, false otherwise
     */
    public function setupThemes()
    {
        if (!empty($this->_cart_settings['theme_name'])) {
            if (fn_allowed_for('ULTIMATE')) {
                fn_install_theme($this->_cart_settings['theme_name'], 1, true, 1);
            } else {
                fn_install_theme($this->_cart_settings['theme_name'], 0, true, 1);
            }

            return true;
        }

        return false;
    }

    /**
     * Writes config co config.local.php
     */
    public function writeConfig()
    {
        $config_contents = file_get_contents(Registry::get('config.dir.root') . '/config.local.php');
        if (!empty($config_contents)) {

            $config = array(
                'db_host' => $this->_database_settings['host'],
                'db_name' => $this->_database_settings['name'],
                'db_user' => $this->_database_settings['user'],
                'db_password' => $this->_database_settings['password'],
                'table_prefix' => $this->_database_settings['table_prefix'],
                'http_host' => Url::normalizeDomain($this->_server_settings['http_host']),
                'http_path' => rtrim($this->_server_settings['http_path'], '/'),
                'https_host' => Url::normalizeDomain($this->_server_settings['https_host']),
                'https_path' => rtrim($this->_server_settings['https_path'], '/'),
                'database_backend' => $this->_database_settings['database_backend'],
                'crypt_key' =>  $this->_cart_settings['secret_key']
            );

            foreach ($config as $paramName => $value) {
                $config_contents = $this->_writeParam($paramName, $value, $config_contents);
            }

            fn_put_contents(Registry::get('config.dir.root') . '/config.local.php', $config_contents);
        }
    }

    /**
     * Changes parameter in content to new value
     *
     * @param  string $paramName Name of needed variable
     * @param  string $value     New value
     * @param  string $config    File content
     * @return string File content
     */
    private function _writeParam($paramName, $value, $config)
    {
        if (strstr($config, '$config[\'' . $paramName . '\'] =')) {
            $value = addcslashes($value, '\'$');
            return preg_replace(
                '/^\$config\[\'' . $paramName . '\'\] =.*;/mi',
                "\$config['" . $paramName . "'] = '" . $value . "';",
                $config
            );
        }

        return $config;
    }

    /**
     * Imports nessesared languages
     *
     * @return bool true on success, false otherwise
     */
    public function setupLanguages($demo)
    {
        $languages = $this->_cart_settings['languages'];

        if (!empty($languages)) {
            foreach ($languages as $lang_code) {
                $pack_path = Registry::get('config.dir.install') . App::DB_LANG . "/$lang_code/$lang_code.po";
                $edition_pack_path = Registry::get('config.dir.install') . App::DB_LANG . "/$lang_code/$lang_code" . '_' . fn_get_edition_acronym(PRODUCT_EDITION) . '.po';

                if (!file_exists($pack_path)) {
                    App::instance()->setNotification('W', 'Missing language pack', 'Unable to find: ' . $pack_path . ' (skipped)', true);

                    continue;
                }

                $this->_parseSql(Registry::get('config.dir.install') . App::DB_LANG . "/$lang_code/" . App::DB_LANG_DATA, 'text_installing_additional_language', array('lang_code' => $lang_code));
                if ($demo) {
                    $this->_parseSql(Registry::get('config.dir.install') . App::DB_LANG . "/$lang_code/" . App::DB_LANG_DEMO, 'text_installing_additional_language', array('lang_code' => $lang_code));
                }

                // Install language variables from PO files
                $params = array(
                    'lang_code' => $lang_code,
                );
                $_langs = Languages::get($params);
                $is_exists = count($_langs) > 0 ? true : false;

                Languages::installLanguagePack($pack_path, array('reinstall' => $is_exists));
                if (file_exists($edition_pack_path)) {
                    Languages::installLanguagePack($edition_pack_path, array('reinstall' => true));
                }
            }

            $languages = db_get_hash_array("SELECT * FROM ?:languages", 'lang_code');
            Registry::set('languages', $languages);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Setups companies
     *
     * @return bool Always true
     */
    public function setupCompanies()
    {
        $this->_setupCompanyEmails($this->_cart_settings['email']);

        $url = $this->_getUrlFromArray($this->_server_settings);
        if (fn_allowed_for('ULTIMATE')) {
            $secure_url = $this->_getUrlFromArray($this->_server_settings, self::HTTPS);
            $this->_updateCompanyURL($url, $secure_url, 1);
            Registry::set('runtime.simple_ultimate', true);
            Registry::set('runtime.forced_company_id', 1);
        }

        $this->updateStorefrontUrl($url, 1);

        return true;
    }

    public function setSimpleMode()
    {
        $company_count = db_get_field("SELECT COUNT(company_id) FROM ?:companies");
        if ($company_count === '1') {
            Registry::set('runtime.simple_ultimate', true);
        } else {
            Registry::set('runtime.simple_ultimate', false);
        }

        return true;
    }

    /**
     * Set currencies data in the Registry
     *
     * @param string $lang_code Two letters language code
     *
     * @return bool Always true
     */
    public function setCurrencies($lang_code)
    {
        $currencies = db_get_hash_array(
            'SELECT a.*, b.description FROM ?:currencies as a'
            . ' LEFT JOIN ?:currency_descriptions as b ON a.currency_id = b.currency_id AND lang_code = ?s',
            'currency_code',
            $lang_code
        );

        Registry::set('currencies', $currencies);

        return true;
    }

    /**
     * Setups company emails
     *
     * @return bool Always true
     */
    private function _setupCompanyEmails($email)
    {
        $company_emails = array (
            'company_users_department',
            'company_site_administrator',
            'company_orders_department',
            'company_support_department',
            'company_newsletter_email',
        );

        db_query("UPDATE ?:settings_objects SET value = ?s WHERE name IN (?a)", $email, $company_emails);

        return true;
    }

    /**
     * Returns URL from hash array width $type_host and $type_path
     *
     * @param  array  $params
     * @param  string $type   name of key (http or https)
     * @return string URL
     */
    private function _getUrlFromArray($params, $type = self::HTTP)
    {
        return (!empty($params[$type . '_host']) ? $params[$type . '_host'] : '') . (!empty($params[$type . '_path']) ? $params[$type . '_path'] : '');
    }

    /**
     * Setups auto feedback
     *
     * @param string $feedback_auto If equals 'Y' auto feedback will be enabled
     *
     * @return bool Always true
     */
    private function _setupAutoFeedback($feedback_auto)
    {
        if ($feedback_auto == 'Y') {
            db_query("UPDATE ?:settings_objects SET value = ?s WHERE name = ?s", 'auto', 'feedback_type');
        }

        return true;
    }

    /**
     * Setups default language for frontend and backend
     *
     * @param $lang 2-letters language code, like 'en'
     *
     * @return bool Always true
     */
    private function _setupDefaultLanguages($lang)
    {
        db_query(
            "UPDATE ?:settings_objects SET value = ?s WHERE name IN (?a)",
            $lang,
            array(
                'frontend_default_language',
                'backend_default_language'
            )
        );

        db_query("UPDATE ?:companies SET lang_code = ?s", $lang);

        return true;
    }

    /**
     * Creates admin account
     * @param string $email    Administrator email
     * @param string $password Administrator password
     *
     * @return bool Always true
     */
    private function _createAdminAccount($email, $password)
    {
        $app = App::instance();
        $salt = fn_generate_salt();
        $password = fn_password_hash($password);

        // Insert root admin
        $user_data = array(
            'user_id' => 1,
            'status' => 'A',
            'user_type' => 'A',
            'is_root' => 'Y',
            'password' => $password,
            'salt' => $salt,
            'email' => $email,
            'user_login' => 'admin',
            'title' => 'mr',
            'firstname' => $app->getLanguageVariable('admin_firstname', ''),
            'lastname' => $app->getLanguageVariable('admin_lastname', ''),
            'company' => $app->getLanguageVariable('admin_company', ''),
            'phone' => $app->getLanguageVariable('admin_phone', ''),
            'lang_code' => $this->_cart_settings['main_language'],
            'profile_name' => 'Main',
        );
        $profile = array(
            'title' => 'mr',
            'firstname' => $app->getLanguageVariable('admin_profile_firstname', ''),
            'lastname' => $app->getLanguageVariable('admin_profile_lastname', ''),
            'address' => $app->getLanguageVariable('admin_profile_address', ''),
            'address_2' => $app->getLanguageVariable('admin_profile_address_2', ''),
            'city' => $app->getLanguageVariable('admin_profile_city', ''),
            'county' => $app->getLanguageVariable('admin_profile_county', ''),
            'state' => $app->getLanguageVariable('admin_profile_state', ''),
            'country' => $app->getLanguageVariable('admin_profile_country', ''),
            'zipcode' => $app->getLanguageVariable('admin_profile_zipcode', ''),
            'phone' => $app->getLanguageVariable('admin_profile_phone', ''),
        );

        foreach ($profile as $k => $v) {
            $user_data['b_' . $k] = $v;
            $user_data['s_' . $k] = $v;
        }

        db_query("REPLACE INTO ?:users ?e", $user_data);
        fn_update_user_profile(1, $user_data, 'add');

        return true;
    }

    /**
     * Setup users
     *
     * @return bool Always true
     */
    public function setupUsers()
    {
        db_query("UPDATE ?:users SET `last_login` = 0, `timestamp` = ?i", TIME);

        return true;
    }

    /**
     * Updates company urls
     *
     * @param  string $url        store url
     * @param  string $secure_url secure store url
     * @param  int    $company_id company identifier
     * @return bool   Always true
     */
    private function _updateCompanyURL($url, $secure_url,  $company_id)
    {
        $company_data = array (
            'storefront' => Url::clean($url),
            'secure_storefront' => Url::clean($secure_url)
        );

        db_query('UPDATE ?:companies SET ?u WHERE company_id = ?i', $company_data, $company_id);

        return true;
    }

    /**
     * Parse and import sql file
     *
     * @param  string $filename path to SQL file
     * @param  string $title    Language value that will be showed on import
     * @param  array  $extra    Extra param
     * @return bool   True on success, false otherwise
     */
    private function _parseSql($filename, $title, $extra = array())
    {
        $app = App::instance();
        $title_shown = false;

        $script_parts = explode('/', $filename);
        $script_name = array_pop($script_parts);

        $is_scheme_dump = $script_name === 'scheme.sql';
        $is_heavy_dump = in_array($script_name, $this->_heavy_sql_dumps_list);

        $fd = fopen($filename, 'r');
        if ($fd) {
            $_sess_name = md5($filename);
            if (!empty($_SESSION['parse_sql'][$_sess_name])) {
                if ($_SESSION['parse_sql'][$_sess_name] == 'COMPLETED') {
                    fclose($fd);

                    return true;
                }

                if ($is_heavy_dump) {
                    fseek($fd, $_SESSION['parse_sql'][$_sess_name]);
                }
            }

            $rest = '';
            $ret = array();
            $counter = 0;
            while (!feof($fd)) {
                $str = $rest.fread($fd, 16384);
                $rest = fn_parse_queries($ret, $str);

                if (!empty($ret)) {
                    if ($title_shown == false) {
                        $app->setNotification('N', '', $app->t($title, $extra), true);
                        $title_shown = true;
                    }

                    foreach ($ret as $query) {
                        $counter ++;
                        if (strpos($query, 'CREATE TABLE') !== false) {
                            preg_match("/CREATE\s+TABLE\s+`(\w*)`/i", $query, $matches);
                            $table_name = str_replace(App::DEFAULT_PREFIX, '', $matches[1]);
                            App::instance()->setInstallProgress('echo', $app->t('creating_table', array('table' => $table_name)));
                        } else {
                            if ($counter > 30 && !App::instance()->isConsole()) {
                                App::instance()->setInstallProgress('echo', '');
                                $counter = 0;
                            }
                        }

                        $query = str_replace(App::DEFAULT_PREFIX, $this->_database_settings['table_prefix'], $query);

                        db_query($query);
                    }
                    $ret = array();
                }

                // Break the connection and re-request
                if ($is_heavy_dump
                    && $this->isMultivendor()
                    && !App::instance()->isConsole()
                    && time() - TIME > self::MAX_SCRIPT_EXECUTION_TIME
                ) {
                    $pos = ftell($fd);
                    $pos = $pos - strlen($rest);
                    fclose($fd);

                    $_SESSION['parse_sql'][$_sess_name] = $pos;

                    $this->redirectExpiredScript();
                }
            }

            fclose($fd);

            $_SESSION['parse_sql'][$_sess_name] = 'COMPLETED';

            if (($is_heavy_dump
                    || $is_scheme_dump)
                && $this->isMultivendor()
                && !App::instance()->isConsole()
            ) {
                // redirect after heavy dump is loaded, because it can be nearly expired
                // and then fail on time consuming demo data archive extracting operation
                $this->redirectExpiredScript();
            }

            return true;
        }

        return false;
    }

    /**
     * Updates license number into Database
     *
     * @param  string $license_number
     * @return bool   Always true
     */
    public function setupLicense($license_number)
    {
        Settings::instance()->updateValue('license_number', $license_number);

        return true;
    }

    public function setupEmailTemplates()
    {
        /** @var \Tygh\Template\Mail\Exim $exim */
        $exim = \Tygh::$app['template.mail.exim'];
        $exim->importFromXmlFile(Registry::get('config.dir.install') . App::RESOURCES_DIR . '/email_templates.xml');

        if (PRODUCT_EDITION == 'MULTIVENDOR') {
            $exim->importFromXmlFile(Registry::get('config.dir.install') . App::RESOURCES_DIR . '/mve_email_templates.xml');
        }
    }

    public function setupDocumentTemplates()
    {
        /** @var \Tygh\Template\Document\Exim $exim */
        $exim = \Tygh::$app['template.document.exim'];
        $exim->importFromXmlFile(Registry::get('config.dir.install') . App::RESOURCES_DIR . '/document_templates.xml');
    }

    /**
     * Checks if the currently installing product is MVE
     *
     * @return bool
     */
    private function isMultivendor() {
        return $this->_is_multivendor;
    }

    /**
     * Sets notification and a variable that will be handled in js event
     */
    private function redirectExpiredScript()
    {
        $_SESSION['request_data'] = Registry::get('request_data');

        if (Registry::get('runtime.comet')) {
            App::instance()->setNotification('W', '', App::instance()->t('script_expired_do_not_reload_redirecting'), true);
            Tygh::$app['ajax']->assign('redirecting', true);
            Tygh::$app['ajax']->assign('comet_is_finished', false);
        }

        die;
    }

    /**
     * Resets parsed sql files cache in session
     *
     * @return bool Always true
     */
    private function _resetParsedFilesCache()
    {
        $result = false;

        if (!$this->_preserve_parsed_files_cache) {
            $_SESSION['parse_sql'] = array();
            $result = true;
        }

        return $result;
    }

    /**
     * FIXME: Used to apply design changes after layouts are installed.
     *
     * @return bool Always true
     */
    public function setupDemoPost()
    {
        $layout_demo = Registry::get('config.dir.install') . App::DEMO_LAYOUT;
        if (file_exists($layout_demo)) {
            $company_id = fn_allowed_for('ULTIMATE') ? 1 : 0;
            Exim::instance($company_id, 1, $this->_cart_settings['theme_name'])->importFromFile($layout_demo, array(
                'import_style' => 'update',
                'override_by_dispatch' => 'Y',
                'clean_up' => 'Y'
            ));
        }

        $post_demo = Registry::get('config.dir.install') . App::DB_DEMO_POST;
        if (file_exists($post_demo)) {
            $this->_parseSql($post_demo, 'creating_demo');
            $this->_resetParsedFilesCache();
        }

        return true;
    }

    /**
     * Collects system data to send with installation report.
     *
     * @return array
     */
    public function getReportData()
    {
        if ($this->report_data === null) {
            $this->report_data = array(
                'sys' => array(
                    'os'     => PHP_OS,
                    'arch'   => 8 * PHP_INT_SIZE,
                    'server' => $_SERVER['SERVER_SOFTWARE'],
                ),

                'php' => array(
                    'version'                 => PHP_VERSION_ID,
                    'ini.safe_mode'           => (int) Bootstrap::getIniParam('safe_mode', true),
                    'ini.max_input_time'      => (int) Bootstrap::getIniParam('max_input_time', true),
                    'ini.max_execution_time'  => (int) Bootstrap::getIniParam('max_execution_time', true),
                    'ini.upload_max_filesize' => fn_return_bytes(Bootstrap::getIniParam('upload_max_filesize', true)),
                    'ini.post_max_size'       => fn_return_bytes(Bootstrap::getIniParam('post_max_size', true)),
                    'image_resize_lib'        => $this->getImageResizeLibs(),
                ),

                'db' => array(
                    'engine'  => isset($this->_database_settings['database_backend']) ? $this->_database_settings['database_backend'] : 'No settings specified',
                    'version' => isset(\Tygh::$app['db']) ? \Tygh::$app['db']->getServerVersion() : 'No database connection established',
                    'strict'  => isset(\Tygh::$app['db']) ? (int) $this->isStrictMode($this->_database_settings['database_backend']) : 'No database connection established',
                ),

                'soft' => array(
                    'software' => PRODUCT_NAME,
                    'edition'  => PRODUCT_EDITION,
                    'version'  => PRODUCT_VERSION,
                    'build'    => PRODUCT_BUILD,
                    'status'   => PRODUCT_STATUS,
                ),

                'store' => array(
                    'url'   => $this->_getUrlFromArray($this->_server_settings),
                    'https' => (int) defined('HTTPS'),
                ),

                'setup' => array(
                    'demo'    => (int) $this->_install_demo,
                    'console' => (int) defined('CONSOLE'),
                    'time'    => TIME,
                ),
            );
        }

        $this->report_data['sys']['time'] = time();

        return $this->report_data;
    }

    /**
     * Sends installation report.
     *
     * @param string $type       Report type
     * @param array  $extra_data Additional report data
     */
    public function sendReport($type, $extra_data = array())
    {
        if ($this->isReportSent($type, $extra_data)) {
            return;
        }

        $payload = array_merge($this->getReportData(), $extra_data);

        $logging = Http::$logging;

        Http::$logging = false;

        Http::post(
            $this->getUpdatesServerUrl() . '/index.php?dispatch=installation_reports.' . $type,
            base64_encode(json_encode($payload))
        );

        Http::$logging = $logging;

        $_SESSION['feedback'][$this->getReportHash($type, $extra_data)] = true;
    }

    /**
     * Obtains updates server URL from main config file.
     *
     * @return null|string
     */
    protected function getUpdatesServerUrl()
    {
        if ($this->updates_server_url === null) {
            $config_contents = explode("\n", file_get_contents(DIR_ROOT . '/config.php'));
            foreach ($config_contents as $line) {
                if ($this->hasUpdatesServerUrlInLine($line)) {
                    list(, $url) = explode('=>', $line);
                    $this->updates_server_url = trim($url, " \t\n\r\0\x0B',");
                    break;
                }
            }
        }

        return $this->updates_server_url;
    }

    /**
     * Checks if plain text line contains Updates Server url
     *
     * @param string $line Line to search in
     *
     * @return bool
     */
    protected function hasUpdatesServerUrlInLine($line)
    {
        return strpos($line, "'updates_server'") !== false
            && preg_match("@'updates_server'\s+?=>@", $line);
    }

    /**
     * Reports installation error.
     *
     * @param int    $errno   Error type
     * @param string $errstr  Error message
     * @param string $errfile File where an error occured
     * @param int    $errline Line in the file
     *
     * @return bool
     */
    public function reportError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $this->sendReport('error', array(
            'error' => array(
                'no'   => $errno,
                'str'  => $this->stripSensitiveInfo($errstr),
                'file' => $this->stripSensitiveInfo($errfile),
                'line' => $errline,
            ),
        ));

        return false;
    }

    /**
     * Checks if fatal error occured and sends notification about it.
     */
    public function reportFatalError()
    {
        $error = error_get_last();

        if ($error !== null && $error['type'] === E_ERROR) {
            $this->reportError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Checks if MySQL server is working in strict mode.
     *
     * @param string $engine Used database engine
     *
     * @return bool
     */
    protected function isStrictMode($engine)
    {
        if (strpos($engine, 'mysql') === 0) {
            $sql_modes = explode(',', strtoupper(\Tygh::$app['db']->getField('SELECT @@sql_mode')));

            return in_array('STRICT_TRANS_TABLES', $sql_modes);
        }

        return false;
    }

    /**
     * Calculates report hash.
     *
     * @param string $type       Report type
     * @param array  $extra_data Additional report data
     *
     * @return string
     */
    protected function getReportHash($type, $extra_data = array())
    {
        return md5($type . ':' . json_encode($extra_data));
    }

    /**
     * Checks if report was sent to prevent duplicate reports.
     *
     * @param string $type       Report type
     * @param array  $extra_data Additional report data
     *
     * @return bool
     */
    protected function isReportSent($type, $extra_data = array())
    {
        $feedback_hash = $this->getReportHash($type, $extra_data);

        return isset($_SESSION['feedback'][$feedback_hash]);
    }

    /**
     * Removes sensitive info (passwords, file paths) from error string.
     *
     * @param string $errstr Error string
     *
     * @return string
     */
    protected function stripSensitiveInfo($errstr)
    {
        $replacements = array(
            DIR_ROOT => '/path/to/store',
        );

        if (isset($this->_cart_settings['email'])) {
            $replacements[$this->_cart_settings['email']] = 'admin@example.com';
        }
        if (isset($this->_cart_settings['password'])) {
            $replacements[$this->_cart_settings['password']] = '********';
        }
        if (isset($this->_database_settings['host'])) {
            $replacements[$this->_database_settings['host']] = 'db_host';
        }
        if (isset($this->_database_settings['name'])) {
            $replacements[$this->_database_settings['name']] = 'db_name';
        }
        if (isset($this->_database_settings['user'])) {
            $replacements[$this->_database_settings['user']] = 'db_user';
        }
        if (isset($this->_database_settings['password'])) {
            $replacements[$this->_database_settings['password']] = 'db_password';
        }

        return strtr($errstr, $replacements);
    }

    /**
     * Enables HTTPS for admin panel and storefront when an installer is accessed via HTTPS.
     *
     * @param bool $is_https Whether HTTPS should be enabled
     */
    public function setupSecurityProtocolSettings($is_https = null)
    {
        if ($is_https === null) {
            $is_https = defined('HTTPS');
        }

        if ($is_https) {
            db_query('UPDATE ?:settings_objects SET value = ?s WHERE name = ?s', 'Y', 'secure_admin');
            db_query('UPDATE ?:settings_objects SET value = ?s WHERE name = ?s', 'Y', 'secure_storefront');
        }
    }

    /**
     * Checks what image resize libraries are installed on the server.
     *
     * @return string Comma-separeted list of image resize libraries
     */
    protected function getImageResizeLibs()
    {
        $libs = array();

        try {
            new Imagick();
            $libs[] = 'imagick';
        } catch (\Exception $e) {}

        try {
            new Gd();
            $libs[] = 'gd';
        } catch (\Exception $e) {}


        return implode(',', $libs);

    }

    protected function updateStorefrontUrl($url, $storefront_id)
    {
        db_query('UPDATE ?:storefronts SET url = ?s WHERE storefront_id = ?i', $url, $storefront_id);
    }
}
