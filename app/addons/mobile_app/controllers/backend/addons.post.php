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

use Tygh\Addons\MobileApp\GoogleServicesConfig;
use Tygh\Addons\MobileApp\ServiceProvider;
use Tygh\Enum\NotificationSeverity;
use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;
use Tygh\Tygh;
use Tygh\Tools\Url;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$is_mobile_app_addon = !empty($_REQUEST['addon']) && $_REQUEST['addon'] === 'mobile_app';
$storefront_id = !empty($_REQUEST['storefront_id']) && (int) $_REQUEST['storefront_id']
    ? (int) $_REQUEST['storefront_id']
    : StorefrontProvider::getStorefront()->storefront_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        $mode === 'update'
        && $is_mobile_app_addon
        && !empty($_REQUEST['setting_id'])
        && !empty($_REQUEST['m_settings'])
    ) {
        $schema = fn_get_schema('mobile_app', 'app_settings');

        foreach ($schema['images'] as $data) {
            fn_attach_image_pairs($data['name'], $data['type'], $storefront_id);
        }

        fn_mobile_app_update_settings($_REQUEST['setting_id'], $_REQUEST['m_settings'], $storefront_id);

        $uploaded_data = fn_filter_uploaded_data('mobile_app');
        if ($uploaded_data) {
            GoogleServicesConfig::upload($uploaded_data, $storefront_id);
        }

        $translations = fn_filter_uploaded_data('mobile_app_translations');
        if ($translations) {
            $translation_manager = ServiceProvider::getTranslationManager();
            $stats = [];
            foreach ($translations as $language_code => $translation) {
                $language_name = Registry::get("languages.{$language_code}.name");
                if (!$language_name) {
                    continue;
                }

                if ($translation['type'] !== 'application/json') {
                    fn_set_notification(
                        NotificationSeverity::ERROR,
                        __('error'),
                        __('mobile_app.invalid_locale_file', ['[language]' => $language_name])
                    );
                    continue;
                }

                $variables_pack = json_decode(file_get_contents($translation['path']), true);
                if (!is_array($variables_pack)) {
                    fn_set_notification(
                        NotificationSeverity::ERROR,
                        __('error'),
                        __('mobile_app.invalid_locale_file', ['[language]' => $language_name])
                    );
                    continue;
                }


                $variables = $translation_manager->getVariables($variables_pack);

                $translation_manager->update($variables, $language_code);

                $stats[$language_name] = count($variables);
            }

            if ($stats) {
                $message = '';
                foreach ($stats as $language_name => $count) {
                    $message  .= '<br>' . __('mobile_app.app_translation_updated.item', [
                        $count,
                        '[language]' => $language_name,
                    ]);
                }

                fn_set_notification(
                    NotificationSeverity::NOTICE,
                    __('mobile_app.app_translation_updated'),
                    $message,
                    'K'
                );
            }
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

if ($mode === 'update') {
    if ($is_mobile_app_addon) {
        if (fn_allowed_for('MULTIVENDOR') && empty($_REQUEST['storefront_id']) && empty($_REQUEST['colors'])) {
            $url = Url::buildUrn(
                'addons.update',
                [
                    'addon'         => 'mobile_app',
                    'storefront_id' => $storefront_id,
                ]
            );

            return [CONTROLLER_STATUS_REDIRECT, $url];
        } elseif (
            fn_allowed_for('ULTIMATE')
            && !Registry::get('runtime.company_id')
            && Registry::get('runtime.storefronts_count') > 1
        ) {
            $company_ids = fn_get_available_company_ids();

            $url = Url::buildUrn(
                'addons.update',
                [
                    'addon'             => 'mobile_app',
                    'switch_company_id' => reset($company_ids),
                ]
            );

            return [CONTROLLER_STATUS_REDIRECT, $url];
        }

        $options = (array) Tygh::$app['view']->getTemplateVars('options');
        $colors = [];

        list($setting_id, $settings) = fn_mobile_app_extract_settings_from_options($options);
        $settings['bundle_id'] = fn_mobile_app_generate_bundle_id(Registry::get('config.http_location'));
        $settings['google_config_file_uploaded'] = GoogleServicesConfig::isExist($storefront_id);

        $images = fn_mobile_app_get_mobile_app_images($storefront_id);
        $schema = fn_get_schema('mobile_app', 'app_settings');

        Tygh::$app['view']->assign([
            'setting_id'             => $setting_id,
            'config_data'            => $settings,
            'app_images'             => $images,
            'image_types'            => $schema['images'],
            'show_all_storefront'    => false,
            'selected_storefront_id' => $storefront_id,
        ]);

        if (!empty($settings['app_appearance']['colors'])) {

            // write colors from setting to array for less
            foreach ($settings['app_appearance']['colors'] as $key => $type) {
                foreach ($type as $variable => $value) {
                    $colors[$variable] = isset($value['value']) ? $value['value'] : $value;
                }
            }
        }

        if ($action === 'rebuild') {
            $colors = array_merge($colors, $_REQUEST['colors']);
        }

        $mobile_app_styles = fn_mobile_app_compile_app_styles($colors);
        Tygh::$app['view']->assign([
            'mobile_app_styles'            => $mobile_app_styles,
            'apple_pay_supported_networks' => fn_mobile_app_get_apple_pay_supported_networks(),
        ]);
    }
}
