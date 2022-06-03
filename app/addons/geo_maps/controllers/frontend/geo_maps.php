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

use Tygh\Addons\GeoMaps\ShippingEstimator;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'set_location') {
        $location = isset($_REQUEST['location']) ? (array) $_REQUEST['location'] : [];

        if (!fn_geo_maps_set_location($location) && empty($_REQUEST['auto_detect'])) {
            fn_set_notification('W', __('warning'), __('geo_maps.cannot_select_location'));
        }

        /** @var \Tygh\Location\Manager $manager */
        $manager = Tygh::$app['location'];
        $city_to_display = $manager->getLocation()->getCity();


        if (defined('AJAX_REQUEST')) {
            Tygh::$app['ajax']->assign('city', $city_to_display);
            Tygh::$app['ajax']->assign('is_detected', $manager->getIsDetected());
            exit;
        }
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'customer_geolocation') {
    return [CONTROLLER_STATUS_OK];

} elseif ($mode == 'shipping_estimation') {
    if (Registry::get('addons.geo_maps.show_shippings_on_product') == 'N') {
        return [CONTROLLER_STATUS_OK];
    }

    $location = fn_geo_maps_get_customer_stored_geolocation();
    list($shipping_methods, $shippings_summary) = ShippingEstimator::getShippingEstimation($_REQUEST['product_id'], $location, $auth);

    Tygh::$app['view']->assign([
        'shipping_methods'       => $shipping_methods,
        'shippings_summary'      => $shippings_summary,
        'no_shippings_available' => count($shipping_methods) == 0,
        'location'               => $location,
        'product_id'             => $_REQUEST['product_id'],
    ]);
}
