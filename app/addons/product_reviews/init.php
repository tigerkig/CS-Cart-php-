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

use Tygh\Addons\ProductReviews\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'delete_product_post',
    'get_product_data',
    'get_products',
    'tools_change_status',
    'get_product_filter_fields',
    /** @see \fn_product_reviews_seo_get_schema_org_markup_items_post */
    'seo_get_schema_org_markup_items_post',
    /** @see \fn_product_reviews_seo_dispatch_before_display_before_cache */
    'seo_dispatch_before_display_before_cache',
    /** @see \fn_product_reviews_storefront_rest_api_get_storefront */
    'storefront_rest_api_get_storefront',
    /** @see \fn_product_reviews_storefront_rest_api_gather_additional_products_data_pre */
    'storefront_rest_api_gather_additional_products_data_pre',
    /** @see \fn_product_variations_product_reviews_actualize_product_prepared_data_pre */
    ['product_reviews_actualize_product_prepared_data_pre', '', 'product_variations'],
    /** @see \fn_product_variations_product_reviews_actualize_product_prepared_data_post */
    ['product_reviews_actualize_product_prepared_data_post', '', 'product_variations'],
    /** @see \fn_product_reviews_storefront_repository_delete_post */
    'storefront_repository_delete_post',
    /** @see \fn_master_products_product_reviews_actualize_product_prepared_data_pre */
    ['product_reviews_actualize_product_prepared_data_pre', '', 'master_products'],
    /** @see \fn_master_products_product_reviews_actualize_product_prepared_data_post */
    ['product_reviews_actualize_product_prepared_data_post', '', 'master_products']
);
