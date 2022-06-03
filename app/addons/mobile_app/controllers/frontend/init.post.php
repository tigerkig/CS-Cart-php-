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

use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return [CONTROLLER_STATUS_OK];
}

$cache_key = 'mobile_app_settings';

Registry::registerCache(
    $cache_key,
    ['addons', 'settings_objects'],
    Registry::cacheLevel(['static', 'storefront'])
);

if (Registry::isExist($cache_key)) {
    $mobile_app_settings = Registry::get($cache_key);
} else {
    $storefront_id = StorefrontProvider::getStorefront()->storefront_id;
    $mobile_app_settings = fn_mobile_app_get_mobile_app_settings($storefront_id);
    $mobile_app_logos = fn_mobile_app_get_mobile_app_images($storefront_id);
    if (isset($mobile_app_logos['m_app_icon']['detailed']['relative_path'])) {
        $mobile_app_settings['banner_icon_url'] = fn_generate_thumbnail(
            $mobile_app_logos['m_app_icon']['detailed']['relative_path'],
            128,
            128
        );
    }
    Registry::set($cache_key, $mobile_app_settings);
}

Tygh::$app['view']->assign('mobile_app_settings', $mobile_app_settings);

return [CONTROLLER_STATUS_OK];
