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

/**
 * Gets list of available fields for price list
 *
 * @return array<string, string>
 */
function fn_settings_variants_addons_customer_price_list_price_list_fields()
{
    $fields = (array) fn_get_schema('customer_price_list', 'fields');
    $result = [];

    foreach ($fields as $field_id => $field) {
        $result[$field_id] = $field['title'];
    }

    return $result;
}

/**
 * Gets list of sorting variants
 *
 * @return array<string, string>
 */
function fn_settings_variants_addons_customer_price_list_price_list_sorting()
{
    $fields = (array) fn_get_schema('customer_price_list', 'fields');
    $result = [];

    foreach ($fields as $field_id => $field) {
        if (!empty($field['sort_by'])) {
            $result[$field_id] = $field['title'];
        }
    }

    return $result;
}
