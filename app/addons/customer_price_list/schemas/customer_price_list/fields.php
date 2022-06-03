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

use Tygh\Enum\YesNo;
use Tygh\Registry;

return [
    'product_id'   => ['title' => __('product_id'), 'sort_by' => ''],
    'product'      => ['title' => __('product_name'), 'sort_by' => 'product'],
    'min_qty'      => ['title' => __('min_order_qty'), 'sort_by' => ''],
    'max_qty'      => ['title' => __('max_order_qty'), 'sort_by' => ''],
    'product_code' => ['title' => __('sku'), 'sort_by' => 'code'],
    'amount'       => ['title' => __('quantity'), 'sort_by' => 'amount'],
    'weight'       => ['title' => __('weight'), 'sort_by' => 'weight'],
    'price'        => [
        'title'   => __('price'),
        'sort_by' => 'price',
        'type'    => function () {
            $currencies = Registry::get('currencies');
            $currency = $currencies[CART_PRIMARY_CURRENCY];

            $currency_format = '#' . html_entity_decode($currency['thousands_separator'])
                . '##0.' . str_repeat('0', $currency['decimals']);

            return $currency['after'] == YesNo::YES
                ? $currency_format . strip_tags($currency['symbol'])
                : strip_tags($currency['symbol']) . $currency_format;
        }
    ],
    'image'        => [
        'title'     => __('image'),
        'sort_by'   => '',
        'formatter' => function (array $product) {
            if (empty($product['main_pair'])) {
                return '';
            }

            if ($image_data = fn_image_to_display($product['main_pair'])) {
                return !empty($image_data['detailed_image_path']) ? $image_data['detailed_image_path'] : '';
            }

            return '';
        }
    ],
];
