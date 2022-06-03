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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('DISCUSSION_REVIEW_COMMUNICATION_AND_RATING', 'B');
fn_define('DISCUSSION_REVIEW_RATING', 'R');
fn_define('DISCUSSION_OBJECT_TYPE_PRODUCT', 'P');
fn_define('DISCUSSION_OBJECT_TYPE_PAGE', 'A');
fn_define('DISCUSSION_POST_STATUS_ACTIVE', 'A');
fn_define('DISCUSSION_OBJECT_TYPE_COMPANY', 'M');

fn_register_hooks(
    'update_product_post',
    'delete_product_post',
    'update_category_post',
    'delete_category_after',
    'delete_order',
    'update_page_post',
    'delete_page',
    'update_event',
    'delete_event',
    'clone_product',
    'get_product_data',
    'get_products',
    'load_products_extra_data',
    'get_categories',
    'get_pages',
    'get_companies',
    'delete_company',
    'companies_sorting',
    'get_predefined_statuses',
    'update_company',
    'settings_variants_image_verification_use_for',
    'load_products_extra_data_post',
    'get_companies_post',
    ['add_discussion_post_post', '', 'gdpr'],
    'check_and_update_product_sharing',
    'seo_get_schema_org_markup_items_post'
);
