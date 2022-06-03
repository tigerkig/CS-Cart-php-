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

use Installer\Requirements\CachingSupportValidator;
use Installer\Requirements\CryptographyValidator;
use Installer\Requirements\CtypeSupportValidator;
use Installer\Requirements\ExifSupportValidator;
use Installer\Requirements\FileinfoSupportValidator;
use Installer\Requirements\FtpSupportValidator;
use Installer\Requirements\GzipSupportValidator;
use Installer\Requirements\ImageProcessingValidator;
use Installer\Requirements\JsonSupportValidator;
use Installer\Requirements\MultibyteStringSupportValidator;
use Installer\Requirements\MysqlSupportValidator;
use Installer\Requirements\NetworkSupportValidator;
use Installer\Requirements\SoapSupportValidator;
use Installer\Requirements\ValidatorInteface;
use Installer\Requirements\XmlSupportValidator;
use Installer\Requirements\ZipSupportValidator;
use Tygh\Bootstrap;
use Tygh\Common\OperationResult;
use Tygh\Registry;
use Tygh\Validators;

class Validator extends Validators
{
    const READABLE = 1;
    const WRITABLE = 2;
    const READABLE_PERMISSIONS = 0644;
    const WRITABLE_PERMISSIONS = 0777;

     /**
     * Email validator
     *
     * @param  array $email
     * @return bool  true if email is valid
     */
    public function isEmailValid($email)
    {
        $app = App::instance();

        if (!parent::isEmailValid($email)) {
            $app->setNotification('E', $app->t('error'), $app->t('invalid_email'), true, 'administration_settings');

            return false;
        }

        return true;
    }

    /**
     * Languages validator
     *
     * @param  array $languages
     * @return bool  true if selected language is valid
     */
    public function isLanguagesValid($languages)
    {
        $app = App::instance();

        if (is_array($languages)) {
            $available_langs = Setup::getLanguages();
            $isLangExists = false;

            foreach ($languages as $lang_code) {
                if (empty($available_langs[$lang_code])) {
                    $app->setNotification('N', $app->t('notice'), $app->t('language_will_be_ignored', array('lang_code' => $app->t($lang_code))), true, 'administration_settings');
                } else {
                    $isLangExists = true;
                }
            }

            if (!$isLangExists) {
                $app->setNotification('E', $app->t('error'), $app->t('empty_languages'), true, 'administration_settings');
            }

            return $isLangExists;
        } else {
            $app->setNotification('E', $app->t('error'), $app->t('empty_languages'), true, 'administration_settings');

            return false;
        }
    }

    /**
     * Check if mod_rewrite is available
     *
     * @return bool true if available
     */
    public function isModRewriteEnabled()
    {
        if (!App::instance()->isConsole() && !parent::isModRewriteEnabled()) {
            $app = App::instance();
            $app::instance()->setNotification('W', $app->t('warning'), $app->t('mod_rewrite_not_configured'), true, 'server_configuration');
        }

        return true;
    }

    /**
     * Check if register_globals disabled
     *
     * @return bool true if exist
     */
    public function isGlobalsDisabled()
    {
        $checking_result = parent::isGlobalsDisabled();

        return $checking_result;
    }

    /**
     * Secret key validator.
     *
     * @param  string $secret_key Secret key
     * @return bool   true if ket is not empty
     */
    public function isSecretKeyValid($secret_key)
    {
        if (!empty($secret_key)) {
            $result = true;
        } else {
            $result = false;
        }

        if (!$result) {
            $app = App::instance();
            $app->setNotification('E', $app->t('error'), $app->t('secret_key_is_not_valid'), true, 'administration_settings');
        }

        return $result;
    }

    /**
     * Checks that DB scheme dump available for reading
     *
     * @return bool True on success, false otherwise
     */
    public function isSchemeDumpAvailable()
    {
        $result = self::checkFileAccess(Registry::get('config.dir.install') . App::DB_SCHEME);

        if (!$result) {
            $app = App::instance();
            $app->setNotification('E', $app->t('error'), $app->t('scheme_dump_is_not_available'), true);
        }

        return $result;
    }

    /**
     * Checks that DB data dump available for reading
     *
     * @return bool True on success, false otherwise
     */
    public function isDataDumpAvailable()
    {
        $result = self::checkFileAccess(Registry::get('config.dir.install') . App::DB_DATA);

        if (!$result) {
            $app = App::instance();
            $app->setNotification('E', $app->t('error'), $app->t('data_dump_is_not_available'), true);
        }

        return $result;
    }

    /**
     * Checks that DB demo dump available for reading
     *
     * @return bool True on success, false otherwise
     */
    public function isDemoDumpAvailable()
    {
        $result = self::checkFileAccess(Registry::get('config.dir.install') . App::DB_DEMO);

        if (!$result) {
            $app = App::instance();
            $app->setNotification('E', $app->t('error'), $app->t('demo_dump_is_not_available'), true);
        }

        return $result;
    }

    /**
     * Check database connection
     *
     * @param  string $host              Database host
     * @param  string $name              Database name
     * @param  string $user              Database user
     * @param  string $password          Database password
     * @param  string $table_prefix      Database table prefix
     * @param  string $database_backend  Database driver type
     * @param  bool   $notify            Show notification on error
     * @param  string $allow_override    Allow to override tables data if already exists
     *
     * @return bool   true if access information is correct
     */
    public function isMysqlSettingsValid($host, $name, $user, $password, $table_prefix, $database_backend, $notify = true, $allow_override = null)
    {
        $app = App::instance();
        $result = false;

        if (!preg_match('/^[0-9a-zA-Z$_]{1,30}$/', $table_prefix)) {
            $app->setNotification('E', $app->t('error'), $app->t('mysql_table_prefix_not_valid'), true, 'server_configuration');
            return false;
        }

        if (preg_match('/^[0-9a-zA-Z$_]{1,63}$/', $name)) {
            if (!empty($host) && !empty($name) && !empty($user)) {
                $result = $app->connectToDB($host, $name, $user, $password, $table_prefix, $database_backend);

                if ($result && !is_null($allow_override)) {
                    $text = $app->t('database_allow_override');

                    if ($allow_override == 'N') {
                        $data = db_get_fields('SHOW TABLES LIKE ?s', $table_prefix . '%');
                        if (!empty($data)) {
                            $text = str_replace('[checkbox]', '<input type="checkbox" name="database_settings[allow_override]" value="Y">', $text);
                            $app->setNotification('W', $app->t('warning'), $text, true, 'server_configuration', true);

                            $result = false;
                            $notify = false;
                        }
                    } elseif ($notify) {
                        $text = str_replace('[checkbox]', '<input type="checkbox" name="database_settings[allow_override]" value="Y" checked="checked">', $text);
                        $app->setNotification('W', $app->t('warning'), $text, true, 'server_configuration', true);
                    }
                }
            }

            if (!$result && $notify) {
                $app->setNotification('E', $app->t('error'), $app->t('mysql_settings_not_valid'), true, 'server_configuration');
            }

        } else {
            $app->setNotification('E', $app->t('error'), $app->t('mysql_settings_database_name_not_valid'), true, 'server_configuration');
        }

        return $result;
    }

    /**
     * Check if installer have ability to change necessary files
     *
     * @param  bool $correct_permissions Correct permissions automaticly
     * @return bool true if all permissions are correct
     */
    public function isFilesystemWritable($correct_permissions = false)
    {
        $dir_root = Registry::get('config.dir.root');
        $checking_result = self::checkFileAccess($dir_root . '/config.local.php', self::WRITABLE, $correct_permissions);
        $checking_result = $checking_result & self::checkFileAccess($dir_root . '/images', self::WRITABLE, $correct_permissions);
        $checking_result = $checking_result & self::checkFileAccess($dir_root . '/design', self::WRITABLE, $correct_permissions);
        $checking_result = $checking_result & self::checkFileAccess($dir_root . '/var', self::WRITABLE, $correct_permissions);

        return $checking_result;
    }

    /**
     * Checks file exists and script has writable or readable access to this file
     *
     * @param  string $file                Path to file
     * @param  int    $type                Type of check (READABLE or WRITABLE)
     * @param  bool   $correct_permissions
     * @return bool   True on success access, false otherwise
     */
    public static function checkFileAccess($file, $type = self::READABLE, $correct_permissions = false)
    {
        $app = App::instance();
        $checking_result = false;
        $filename = str_replace('./../', '', $file);

        if (file_exists($file)) {
            $filetype = is_dir($file) ? 'dir' : 'file';
            if ($type == self::READABLE) {
                if (!is_readable($file) && $correct_permissions) {
                    @chmod($file, self::READABLE_PERMISSIONS);
                }

                if (!is_readable($file)) {
                    if ($correct_permissions) {
                        $app->setNotification('E', $app->t('error'), $app->t($filetype . '_unable_correct_permissions', array($filetype => $filename)), true, 'file_permissions_section');
                    } else {
                        $app->setNotification('E', $app->t('error'), $app->t($filetype . '_not_readable', array($filetype => $filename)), true, 'file_permissions');
                    }
                } else {
                    $checking_result = true;
                }
            } elseif ($type == self::WRITABLE) {
                if (!is_writable($file) && $correct_permissions) {
                    @chmod($file, self::WRITABLE_PERMISSIONS);
                }

                if (!is_writable($file)) {
                    if ($correct_permissions) {
                        $app->setNotification('E', $app->t('error'), $app->t($filetype . '_unable_correct_permissions', array($filetype => $filename)), true, 'file_permissions_section');
                    } else {
                        $app->setNotification('E', $app->t('error'), $app->t($filetype . '_not_writable', array($filetype => $filename)), true, 'file_permissions');
                    }

                } else {
                    $checking_result = true;
                }
            }
        } else {
            $app->setNotification('E', $app->t('error'), $app->t('file_not_exists', array('file' => $filename)), true, 'file_permissions');
        }

        if ($checking_result && $filetype == 'dir') {
            foreach (scandir($file) as $subfile) {
                $skip_files = array('.', '..', '.htaccess', 'index.php');
                if (!in_array($subfile, $skip_files)) {
                    if (!self::checkFileAccess($file . '/' . $subfile, $type, $correct_permissions)) {
                        $checking_result = false;

                        break;
                    }
                }
            }
        }

        return $checking_result;
    }

    /**
     * Check if necessary PHP version is supported by server
     *
     * @return bool true if supported
     */
    public function isPhpVersionSupported()
    {
        $app = App::instance();
        $php_value = phpversion();
        $php_error = (version_compare($php_value, App::REQUIRED_PHP_VERSION) != -1) ? false : true;
        $checking_result = ($php_error == false) ? true : false;

        return $checking_result;
    }

    /**
     * Check if SafeMode is disabled
     *
     * @return bool true if disabled
     */
    public function isSafeModeDisabled()
    {
        $checking_result = parent::isSafeModeDisabled();

        return $checking_result;
    }

    /**
     * Check if cart can upload files to server
     *
     * @return bool true if can
     */
    public function isFileUploadsSupported()
    {
        $checking_result = parent::isFileUploadsSupported();

        return $checking_result;
    }

    /**
     * Check if ModeSecurity is disabled
     *
     * @return bool true if disabled
     */
    public function isModeSecurityDisabled()
    {
        if (!App::instance()->isConsole() && !parent::isModeSecurityDisabled()) {
            App::instance()->setNotification(
                'W',
                App::instance()->t('warning'),
                App::instance()->t('mod_security_detected'),
                true,
                'server_configuration'
            );
        }

        return true;
    }

    /**
     * Check if session.autostart is disabled
     *
     * @return bool true if disabled
     */
    public function isSessionAutostartDisabled()
    {
        $checking_result = parent::isSessionAutostartDisabled();

        return $checking_result;
    }

    /**
     * Check if host name is not empty
     *
     * @param  string $http_host
     * @return bool   return true if not empty
     */
    public function isHostNameValid($http_host)
    {
        if (empty($http_host)) {
            $result = false;
        } else {
            $result = true;
        }

        if (!$result) {
            $app = App::instance();
            $app->setNotification('E', $app->t('error'), $app->t('host_name_cannot_be_empty'), true, 'server_configuration');
        }

        return $result;
    }

    /**
     * Runs some validate method from $this class
     * Automaticly generates notification if methods accept params that missing in $params
     *
     * @param  string $validator_name
     * @param  array  $params
     * @return bool   Validator result
     */
    public function validate($validator_name, $params)
    {
        $validate_result = false;
        $app = App::instance();

        if (method_exists($this, $validator_name)) {
            $can_call = true;
            $reflection_method = new \ReflectionMethod($this, $validator_name);
            $accepted_params = $reflection_method->getParameters();
            $call_params = array ();

            foreach ($accepted_params as $param) {
                if (isset($params[$param->name])) {
                    $call_params[] = $params[$param->name];
                } else {
                    $can_call = false;

                    $app->setNotification('E', $app->t('error'), $app->t('empty_params', array(
                        'param' => $app->t($param->name),
                    )), true);
                }
            }

            if ($can_call) {
               $validate_result = $reflection_method->invokeArgs($this, $call_params);
            }
        }

        return $validate_result;
    }

    /**
     * Runs all methods with prefix test in current object and returns result
     *
     * @param $params
     * @return bool test methods result
     */
    public function validateAll($params)
    {
        $result = true;

        $this->checkIfOpCacheSettingsIsConformAndSetNotification();

        foreach (get_class_methods($this) as $methodName) {
            if (strpos($methodName, 'is') === 0) {
                $result = $result & $this->validate($methodName, $params);
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isSessionStarted()
    {
        return (defined('CONSOLE') && CONSOLE) || parent::isSessionStarted();
    }

    /**
     * Set warning if current running script's path is not included into the opcache.restrict_api list.
     *
     * @return bool always true
     */
    public function checkIfOpCacheSettingsIsConformAndSetNotification()
    {
        $result = $this->checkScriptPathAllowedForOpcache();

        $app = App::instance();

        if ($result === false) {
            $app->setNotification('W', $app->t('warning'), $this->buildOpcacheCompatibilityNotificationString(), true);
        }

        return true;
    }

    /**
     * Checks whether current running script's path is included into the opcache.restrict_api list
     * @see http://php.net/manual/en/opcache.configuration.php#ini.opcache.restrict-api
     *
     * @return bool
     */
    public function checkScriptPathAllowedForOpcache()
    {
        $opcache_restrict_api_setting = explode(',', Bootstrap::getIniParam('opcache.restrict_api', true));
        $main_script_path = DIR_ROOT;
        $is_current_path_allowed_for_opcache = false;
        foreach ($opcache_restrict_api_setting as $path) {
            if (trim($path) === '' || trim($path) === '/' || strpos($main_script_path, rtrim(trim($path), '/')) !== false) {
                $is_current_path_allowed_for_opcache = true;
                break;
            }
        }
        $result = $is_current_path_allowed_for_opcache;

        return $result;
    }

    /**
     * Build notification string
     *
     * @return string
     */
    private function buildOpcacheCompatibilityNotificationString()
    {
        $product_info = PRODUCT_NAME;

        $main_script_path = DIR_ROOT;

        $result = App::instance()->t(
            'opcache_restrict_api_setting_incompatible',
            array(
                'product' => $product_info,
                'current_script_path' => $main_script_path,
            )
        );

        return $result;
    }

    /**
     * Validates that all necessary extensions are installed.
     *
     * @return \Tygh\Common\OperationResult
     */
    public function validateExtensionsRequirements()
    {
        $result = new OperationResult(true);

        $checks = [
            'mysql_support'             => [new MysqlSupportValidator(), true],
            'network_support'           => [new NetworkSupportValidator(), true],
            'image_processing'          => [new ImageProcessingValidator(), true],
            'exif_support'              => [new ExifSupportValidator(), true],
            'json_support'              => [new JsonSupportValidator(), true],
            'xml_support'               => [new XmlSupportValidator(), true],
            'soap_support'              => [new SoapSupportValidator(), true],
            'multibytye_string_support' => [new MultibyteStringSupportValidator(), true],
            'ctype_support'             => [new CtypeSupportValidator(), true],
            'gzip_support'              => [new GzipSupportValidator(), true],
            'zip_support'               => [new ZipSupportValidator(), true],
            'fileinfo_support'          => [new FileinfoSupportValidator(), true],
            'cryptography'              => [new CryptographyValidator(), true],
            'ftp_support'               => [new FtpSupportValidator(), false],
            'caching_support'           => [new CachingSupportValidator(), false],
        ];

        $data = [];

        /** @var \Installer\Requirements\ValidatorInteface $validator */
        foreach ($checks as $key => list($validator, $is_mandatory)) {
            $is_valid = $validator->validate();

            if (!$is_mandatory) {
                $validator->setWarnings(array_merge($validator->getErrors(), $validator->getWarnings()));
                $validator->setErrors([]);
            }

            if (!$is_valid && $is_mandatory) {
                $result->setSuccess(false);
            }

            if ($validator->getErrors() || $validator->getWarnings()) {
                $data[$key] = $validator;
            }
        }

        $result->setData($data);

        return $result;
    }

    /**
     * Validates that all necessary extensions are installed.
     * Used in the batch checks.
     *
     * @see \Installer\Validator::validateAll
     *
     * @return bool
     */
    public function isRequiredExtensionsInstalled()
    {
        $result = $this->validateExtensionsRequirements();

        $app = App::instance();

        if ($app->isConsole()) {
            /**
             * @var string $validator_id
             * @var \Installer\Requirements\ValidatorInteface $validator
             */
            foreach ($result->getData() as $validator_id => $validator) {

                foreach ($validator->getErrors() as $error_code) {
                    $message = $app->t("extensions.{$validator_id}.error.{$error_code}");

                    if ($error_code === $validator::EXTENSION_MISSING) {
                        $message = $this->attachExtensions(
                            $message,
                            $validator->getRequirements(),
                            $validator->getRequirementsMode(),
                            $app
                        );
                    }

                    $app->setNotification(
                        'E',
                        $app->t("extensions.{$validator_id}"),
                        $message,
                        true,
                        'server_requirements'
                    );
                }

                foreach ($validator->getWarnings() as $warning_code) {
                    $message = $app->t("extensions.{$validator_id}.warning.{$warning_code}");

                    if ($warning_code === $validator::EXTENSION_MISSING) {
                        $message = $this->attachExtensions(
                            $message,
                            $validator->getRequirements(),
                            $validator->getRequirementsMode(),
                            $app
                        );
                    }

                    $app->setNotification(
                        'W',
                        $app->t("extensions.{$validator_id}"),
                        $message,
                        true,
                        'server_requirements'
                    );
                }
            }
        }

        return $result->isSuccess();
    }

    /**
     * Attaches extensions list to the error message for console installer.
     *
     * @param string         $message    Current error message
     * @param string[]       $extensions Extensions list
     * @param int            $requirements_mode
     * @param \Installer\App $app        Installer instance
     *
     * @return string
     */
    protected function attachExtensions($message, array $extensions, $requirements_mode, App $app)
    {
        if (count($extensions) > 1) {
            if ($requirements_mode === ValidatorInteface::REQUIRE_ALL) {
                $message .= "\n" . $app->t('extensions.install_all') . ': ' . implode(', ', $extensions);
            } else {
                $message .= "\n" . $app->t('extensions.install_any') . ': ' . implode(', ', $extensions);
            }
        } else {
            $message .= "\n" . $app->t('extensions.install_one') . ': ' . implode(', ', $extensions);
        }

        return $message;
    }
}
