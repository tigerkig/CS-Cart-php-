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

/**
 * Describes a way to redirect from one area to another, if there is not appropriate from_dispatch in the schema, will be redirected to the homepage of to_area
 *
 * Structure:
 *
 * 'from' => [
 *     'dispatch' => 'controller.mode',        //if dispatch from_area equal that string, will be checked another parameters
 *     'required_url_query_param1' => 'value', //url except dispatch must be contains param with defined value, if there is not the parameter or value is not the same as described, that dispatch will not be considered appropriate
 *     'required_url_query_param2'             //url except dispatch must be contains param, value is not important
 * ],
 * 'to_area' => [
 *      'dispatch' => 'controller.mode'              //dispatch to_area will be used for forming to_area url
 *      'url_query_param1' => 'value',               //parameter with defined value will be added to the url
 *      'url_query_param2' => '%value%',             //parameter will be get from from_url, the key of query parameter should be "value"
 *      'url_query_param3' => handler_function_name, //callback that will return value of one parameter
 *      'company_id'       => handler_function_name  //company_id used for to_customer redirects, for choosing the right storefront
 * ],
 * 'to_another_area' => 'handler_function_name', //callback that will return all required parameters
 */

use Tygh\Enum\ProductFeatures;
use Tygh\Tools\Url;
use Tygh\Enum\YesNo;

return [
    'products.manage&cid' => [
        'from' => [
            'dispatch' => 'products.manage',
            'cid'
        ],
        'to_customer' => [
            'dispatch' => 'categories.view',
            'category_id' => '%cid%',
            'company_id'  => function (Url $url) {
                $category_id = $url->getQueryParam('cid');

                $category = fn_get_category_data($category_id);

                return isset($category['company_id']) ? $category['company_id'] : 0;
            }
        ]
    ],
    'products.update' => [
        'from' => [
            'dispatch' => 'products.update',
            'product_id'
        ],
        'to_customer' => [
            'dispatch' => 'products.view',
            'product_id' => '%product_id%',
            'company_id'  => function (Url $url) {
                $product_id = $url->getQueryParam('product_id');

                $product = fn_get_product_data($product_id, Tygh::$app['session']['auth']);

                return isset($product['company_id']) ? $product['company_id'] : 0;
            }
        ]
    ],
    'cart.cart_list' => [
        'from' => [
            'dispatch' => 'cart.cart_list'
        ],
        'to_customer' => [
            'dispatch' => 'checkout.cart'
        ]
    ],
    'profiles.update' => [
        'from' => [
            'dispatch' => 'profiles.update',
        ],
        'to_customer' => [
            'dispatch' => 'profiles.update',
        ]
    ],
    'orders.manage' => [
        'from' => [
            'dispatch' => 'orders.manage',
        ],
        'to_customer' => [
            'dispatch' => 'orders.search',
        ]
    ],
    'orders.details' => [
        'from' => [
            'dispatch' => 'orders.details',
            'order_id'
        ],
        'to_customer' => function (Url $url) {
            $order_id = $url->getQueryParam('order_id');

            if (fn_is_order_allowed($order_id, Tygh::$app['session']['auth'])) {
                return [
                    'dispatch' => 'orders.details',
                    'order_id' => $order_id
                ];
            } else {
                return [
                    'dispatch' => 'orders.search',
                ];
            }
        }
    ],
    'pages.update' => [
        'from' => [
            'dispatch' => 'pages.update',
            'page_id'
        ],
        'to_customer' => [
            'dispatch' => 'pages.view',
            'page_id' => '%page_id%',
            'company_id'  => function (Url $url) {
                $page_id = $url->getQueryParam('page_id');

                $page = fn_get_page_data($page_id);

                return isset($page['company_id']) ? $page['company_id'] : 0;
            }
        ]
    ],
    'product_features.update' => [
        'from' => [
            'dispatch' => 'product_features.update',
            'feature_id'
        ],
        'to_customer' => function (Url $url) {
            $feature_id = $url->getQueryParam('feature_id');

            list($filter) = fn_get_product_filters([
                'feature_id' => $feature_id,
                'short'     => true
            ]);

            $filter = is_array($filter) ? reset($filter) : 0;

            if (!empty($filter) && $filter['feature_type'] == ProductFeatures::EXTENDED) {
                return [
                    'dispatch'   => 'product_features.view_all',
                    'filter_id'  => $filter['filter_id'],
                    'company_id' => isset($filter['company_id']) ? $filter['company_id'] : null
                ];
            } else {
                return false;
            }
        }
    ],
    'sitemap.manage' => [
        'from' => [
            'dispatch' => 'sitemap.manage'
        ],
        'to_customer' => [
            'dispatch'   => 'sitemap.view'
        ]
    ],
    'product_features.manage' => [
        'from' => [
            'dispatch' => 'product_features.manage'
        ],
        'to_customer' => [
            'dispatch'   => 'product_features.compare'
        ]
    ],
    'products.manage' => [
        'from' => 'products.manage',
        'to_customer' => [
            'dispatch' => 'products.search',
            'search_performed' => YesNo::YES
        ]
    ],
    'promotions.manage' => [
        'from' => [
            'dispatch' => 'promotions.manage'
        ],
        'to_customer' => [
            'dispatch'   => 'promotions.list'
        ]
    ]
];