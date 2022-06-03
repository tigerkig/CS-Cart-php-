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

defined('BOOTSTRAP') or die('Access denied');

if (fn_allowed_for('ULTIMATE')) {
    fn_register_hooks(
    /** @see \fn_store_locator_ult_check_store_permission() */
        'ult_check_store_permission'
    );
}

fn_register_hooks(
    /** @see \fn_store_locator_delete_company() */
    'delete_company',
    /** @see \fn_store_locator_calculate_cart_post() */
    'calculate_cart_post',
    /** @see \fn_store_locator_calculate_cart_taxes_pre() */
    'calculate_cart_taxes_pre',
    /** @see \fn_store_locator_update_cart_by_data_post() */
    'update_cart_by_data_post',
    /** @see \fn_store_locator_pickup_point_variable_init() */
    'pickup_point_variable_init',
    /** @see \fn_store_locator_calculate_cart_content_before_shipping_calculation() */
    'calculate_cart_content_before_shipping_calculation',
    /** @see \fn_store_locator_update_shipping() */
    'update_shipping',
    /** @see \fn_store_locator_store_shipping_rates_post() */
    'store_shipping_rates_post',
    /** @see \fn_store_locator_storefront_rest_api_format_order_prices_post() */
    'storefront_rest_api_format_order_prices_post',
    /** @see \fn_store_locator_storefront_rest_api_strip_service_data_post() */
    'storefront_rest_api_strip_service_data_post',
    /** @see \fn_store_locator_shippings_get_shipping_for_test_post() */
    'shippings_get_shipping_for_test_post',
    /** @see \fn_store_locator_place_suborders_pre() */
    'place_suborders_pre'
);
