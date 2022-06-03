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

fn_register_hooks(
    'dispatch_assign_template',
    'update_product_amount',
    'clone_product',
    'update_product_post',
    'global_update_products',
    'delete_product_pre',
    'delete_product_post',
    'tools_change_status',
    'database_restore',
    'get_products',
    'get_products_before_select',
    'products_sorting',
    'update_product_filter_post',
    'delete_product_filter_pre',
    'update_category_post',
    'delete_category_post',
    'update_page_post',
    'clone_page',
    'delete_page',
    'update_language_post',
    'delete_languages_pre',
    'update_company',
    'delete_company_pre',
    'change_company_status_pre',
    'update_product_feature_post',
    'update_addon_status_post',
    'variation_group_save_group',
    'master_products_create_vendor_product',
    ['se_get_children_product_ids', '', 'master_products'],
    ['se_get_parent_product_ids', '', 'product_variations']
);

