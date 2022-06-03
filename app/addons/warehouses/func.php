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

use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\Warehouses\Manager;
use Tygh\Addons\Warehouses\ProductWarehouse;
use Tygh\Addons\Warehouses\ServiceProvider;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\ProductTabs;
use Tygh\Enum\SiteArea;
use Tygh\Enum\YesNo;
use Tygh\Languages\Languages;
use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;
use Tygh\Storefront\Storefront;
use Tygh\Addons\Warehouses\CommerceML\Dto\WarehouseDto;
use Tygh\Addons\Warehouses\CommerceML\Dto\ProductWarehouseQuantityDto;
use Tygh\Addons\Warehouses\CommerceML\Dto\ProductWarehouseQuantityDtoCollection;
use Tygh\Addons\CommerceML\ServiceProvider as CommerceMLServiceProvider;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Common\OperationResult;

function fn_warehouses_install()
{
    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_all_companies_ids();
    } else {
        $company_ids = [0];
    }

    $block = Block::instance();
    $product_tabs = ProductTabs::instance();

    foreach ($company_ids as $company_id) {
        $block_data = [
            'type'         => 'availability_in_stores',
            'properties'   => [
                'template' => 'addons/warehouses/blocks/availability_in_stores.tpl',
            ],
            'content_data' => [],
            'company_id'   => $company_id,
        ];

        $block_description = [
            'lang_code' => DEFAULT_LANGUAGE,
            'name'      => __('warehouses.product_availability', [], DEFAULT_LANGUAGE),
            'lang_var' => 'warehouses.product_availability',
        ];

        $block_id = $block->update($block_data, $block_description);

        $tab_data = [
            'tab_type'      => 'B',
            'block_id'      => $block_id,
            'template'      => 'addons/warehouses/blocks/availability_in_stores.tpl',
            'addon'         => 'warehouses',
            'status'        => 'A',
            'is_primary'    => 'N',
            'position'      => false,
            'product_ids'   => null,
            'company_id'    => $company_id,
            'show_in_popup' => YesNo::NO,
            'lang_code'     => DEFAULT_LANGUAGE,
            'name'          => __('warehouses.product_availability', [], DEFAULT_LANGUAGE),
            'lang_var'      => 'warehouses.product_availability',
        ];

        $product_tabs->update($tab_data);
    }
}

/**
 * Fetches destination identifier
 *
 * @param array $location Location
 *
 * @return bool|mixed|null
 */
function fn_warehouses_get_destination_id($location)
{
    $destination_id = fn_get_available_destination($location);
    if (!$destination_id) {
        if (AREA == 'A') {
            return null;
        }

        /** @var \Tygh\Location\Manager $manager */
        $manager = Tygh::$app['location'];
        $destination_id = $manager->getDestinationId();
    }

    return $destination_id;
}

/**
 * Fetches location data from shopping cart data
 *
 * @param array $cart Shopping cart
 *
 * @return array
 */
function fn_warehouses_get_location_from_cart($cart)
{
    $location = [
        'country' => isset($cart['user_data']['s_country']) ? $cart['user_data']['s_country'] : '',
        'state'   => isset($cart['user_data']['s_state']) ? $cart['user_data']['s_state'] : '',
        'city'    => isset($cart['user_data']['s_city']) ? $cart['user_data']['s_city'] : '',
        'zipcode' => isset($cart['user_data']['s_zipcode']) ? $cart['user_data']['s_zipcode'] : '',
        'address' => isset($cart['user_data']['s_address']) ? $cart['user_data']['s_address'] : '',
    ];

    return $location;
}

/**
 * Fetches location data from shopping order info
 *
 * @param array $order_info Order information
 *
 * @return array
 */
function fn_warehouses_get_location_from_order($order_info)
{
    $location = [
        'country' => isset($order_info['s_country']) ? $order_info['s_country'] : '',
        'state'   => isset($order_info['s_state']) ? $order_info['s_state'] : '',
        'city'    => isset($order_info['s_city']) ? $order_info['s_city'] : '',
        'zipcode' => isset($order_info['s_zipcode']) ? $order_info['s_zipcode'] : '',
        'address' => isset($order_info['s_address']) ? $order_info['s_address'] : '',
    ];

    return $location;
}

/**
 * Gets selected the pickup point ID from order info.
 *
 * @param array $order_info
 * @param int   $product_id
 * @param int   $cart_id
 *
 * @return int|null
 */
function fn_warehouses_get_pickup_point_id_from_order(array $order_info, $product_id, $cart_id = null)
{
    foreach ($order_info['product_groups'] as $group_key => $group) {
        if ($cart_id !== null) {
            $is_product_in_group = isset($group['products'][$cart_id]);
        } else {
            $group_products = array_column($group['products'], 'product_id');
            $is_product_in_group = in_array($product_id, $group_products);
        }
        if (!$is_product_in_group || !isset($group['chosen_shippings'])) {
            continue;
        }
        
        foreach ($group['chosen_shippings'] as $shipping) {
            if (isset($shipping['store_location_id'])) {
                return $shipping['store_location_id'];
            }
        }
    }

    return null;
}

/**
 * Gets product availability summary.
 *
 * @param int    $product_id     Product identifier
 * @param int    $destination_id Customer's rate area identifier
 * @param string $lang_code      Two-letter language code
 *
 * @return array
 */
function fn_warehouses_get_availability_summary($product_id, $destination_id, $lang_code = CART_LANGUAGE)
{
    $summary = [
        'in_stock_stores_count'   => null,
        'available_stores_count'  => null,
        'warn_about_delay'        => false,
        'shipping_delay'          => null,
        'show_stock_availability' => false,
        'product_id'              => $product_id,
        'grouped_stores'          => null,
    ];

    /** @var Tygh\Addons\Warehouses\Manager $stock_manager */
    $stock_manager = Tygh::$app['addons.warehouses.manager'];
    $product_stock = $stock_manager->getProductWarehousesStock($product_id);
    if (!$product_stock->hasStockSplitByWarehouses()) {
        return $summary;
    }

    // stores that are shown in the customer's destination
    $stores = $product_stock->getWarehousesForPickupInDestination($destination_id);

    $store_ids = array_map(function(ProductWarehouse $store) {
        return $store->getWarehouseId();
    }, $stores);
    list($locations,) = fn_get_store_locations(['store_location_id' => $store_ids], 0, $lang_code);

    // amount of stores where the product is available right now
    $in_stock_stores_count = 0;
    // amount of stores where the product can be purchased
    $available_stores_count = 0;
    // whether customer must be warned about shipping delay in his destination
    $warn_about_delay = false;
    // shipping delay to show to customer
    $shipping_delay = null;
    // whether stock availability block must be shown
    $show_stock_availability = false;
    // stores where the product can be picked up
    $grouped_stores = [];
    $city_ids = [];
    foreach ($stores as $store) {
        // shipping delay details
        $store_warn_about_delay = false;
        $store_shipping_delay = null;
        $store_is_available = $store->getAmount() > 0;
        $store_destination_id = $store->getMainDestinationId();
        foreach ($product_stock->getWarehousesThatShipToStore($store) as $fallback) {
            $is_fallback_prioritized = $fallback->getPosition($store_destination_id) < $store->getPosition($store_destination_id);

            if ($is_fallback_prioritized && $fallback->isWarnAboutDelay($store_destination_id)) {
                $warn_about_delay = true;
                $shipping_delay = $fallback->getShippingDelay($store_destination_id);
            }

            if (!$store_is_available) {
                if ($fallback->isWarnAboutDelay($store_destination_id)) {
                    $store_warn_about_delay = true;
                }
                if ($fallback->getShippingDelay($store_destination_id)) {
                    $store_shipping_delay = $fallback->getShippingDelay($store_destination_id);
                }
            }

            $store_is_available = true;
            break;
        }

        $store_id = $store->getWarehouseId();
        $location_data = $locations[$store_id];
        $store_city = $location_data['city'];
        if (!isset($city_ids[$location_data['city']])) {
            $city_ids[$store_city] = count($city_ids);
            $grouped_stores[$city_ids[$store_city]] = [
                'name'  => $store_city,
                'items' => [],
            ];
        }

        $grouped_stores[$city_ids[$store_city]]['items'][$store_id] = [
            'store_location_id' => $store_id,
            'name'              => $location_data['name'],
            'description'       => $location_data['description'],
            'latitude'          => $location_data['latitude'],
            'longitude'         => $location_data['longitude'],
            'pickup_address'    => $location_data['pickup_address'],
            'pickup_time'       => $location_data['pickup_time'],
            'pickup_phone'      => $location_data['pickup_phone'],
            'amount'            => $store->getAmount(),
            'is_available'      => $store_is_available,
            'shipping_delay'    => $store_shipping_delay,
        ];

        if ($store->getAmount() > 0) {
            $in_stock_stores_count++;
        }
        if ($store_is_available) {
            $available_stores_count += (int) $store_is_available;
        }
        $warn_about_delay = $warn_about_delay || $store_warn_about_delay;
        $shipping_delay = $shipping_delay ?: $store_shipping_delay;
        $show_stock_availability = $show_stock_availability || $store_is_available;
    }

    if (!$stores) {
        foreach ($product_stock->getWarehousesForShippingInDestination($destination_id) as $fallback) {
            if ($fallback->getAmount() > 0) {

                if ($fallback->isWarnAboutDelay($destination_id)) {
                    $warn_about_delay = true;
                    $shipping_delay = $fallback->getShippingDelay($destination_id);
                }

                break;
            }
        }
    }

    $summary['in_stock_stores_count'] = $in_stock_stores_count;
    $summary['available_stores_count'] = $available_stores_count;
    $summary['warn_about_delay'] = $warn_about_delay;
    $summary['shipping_delay'] = $shipping_delay;
    $summary['show_stock_availability'] = $show_stock_availability;
    $summary['grouped_stores'] = $grouped_stores;

    return $summary;
}

/**
 * Hook handler: updates warehouses stock data
 */
function fn_warehouses_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (!isset($product_data['warehouses'])) {
        return;
    }

    $warehouses_amounts = [];
    $total_amount = 0;
    foreach ($product_data['warehouses'] as $warehouse_id => $amount) {
        $warehouses_amounts[] = [
            'warehouse_id' => $warehouse_id,
            'amount'       => $amount,
        ];
        $total_amount += (int) $amount;
    }

    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    $stock = $manager->getProductWarehousesStock($product_id);
    if ($stock->hasStockSplitByWarehouses()) {
        $amount = $stock->getAmount();
        if ($total_amount > 0 && $amount <= 0) {
            fn_send_product_notifications($product_id);
        }
    }
    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    $product_stock = $manager->createProductStockFromWarehousesData($product_id, $warehouses_amounts);

    $remove_all = empty($product_data['warehouses_update_stock_only']) && (fn_allowed_for('MULTIVENDOR') || !Registry::get('runtime.company_id'));

    $manager->saveProductStock($product_stock, $remove_all);
}

/**
 * Hook handler: actualizes product amount data from warehouses
 */
function fn_warehouses_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (empty($product_data['product_id'])) {
        return;
    }

    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    $product_stock = $manager->getProductWarehousesStock($product_data['product_id']);

    if (!$product_stock->hasStockSplitByWarehouses()) {
        return;
    }

    if (AREA == 'C') {
        /** @var \Tygh\Location\Manager $manager */
        $manager = Tygh::$app['location'];
        $destination_id = $manager->getDestinationId();

        $product_data['amount'] = $product_stock->getAmountForDestination($destination_id);
    } else {
        $product_data['amount'] = $product_stock->getAmount();
    }
}

/**
 * The "get_products" hook handler.
 *
 * Actions performed:
 *  - Extends filter by product.amount with filter by warehouse product amount
 *
 * @param array<string, \Tygh\Storefront\Storefront|string> $params    Parameters of request.
 * @param array<string, string>                             $fields    Requested fields.
 * @param array<string, string>                             $sortings  Parameters for sortings request data.
 * @param string                                            $condition Condition for request.
 * @param string                                            $join      Join parameter for request.
 * @param string                                            $sorting   Specified sorting field.
 * @param string                                            $group_by  Specified group field.
 * @param string                                            $lang_code Language code.
 * @param string                                            $having    Having sql query parameter.
 *
 * @psalm-param array{
 *                  area: string,
 *                  amount_from: string,
 *                  amount_to: string,
 *                  storefront: \Tygh\Storefront\Storefront|string,
 *                  sort_by: string
 *              } $params
 * @see \fn_get_products()
 */
function fn_warehouses_get_products(array &$params, array &$fields, array &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (!empty($params['ignore_warehouses'])) {
        return;
    }
    $join .= db_quote(
        ' LEFT JOIN ?:warehouses_sum_products_amount as war_sum_amount'
        . ' ON war_sum_amount.product_id = products.product_id'
    );
    $detailed_search_in_the_admin_panel = !SiteArea::isStorefront($params['area']);
    if ($detailed_search_in_the_admin_panel) {
        $condition = str_replace(
            'products.amount',
            db_quote(
                '(CASE products.is_stock_split_by_warehouses WHEN ?s'
                . ' THEN war_sum_amount.amount'
                . ' ELSE products.amount END)',
                YesNo::YES
            ),
            $condition
        );
    }

    $check_warehouse_product_amount = SiteArea::isStorefront($params['area']) && (
        (
            Registry::get('settings.General.inventory_tracking') !== YesNo::NO
            && Registry::get('settings.General.show_out_of_stock_products') === YesNo::NO
        )
        || (isset($params['amount_from']) && fn_is_numeric($params['amount_from']))
        || (isset($params['amount_to']) && fn_is_numeric($params['amount_to']))
    );

    if ($check_warehouse_product_amount) {
        if (fn_allowed_for('MULTIVENDOR')) {
            // In MVE the warehouses were not shareable
            $storefront_id = 0;
        } else {
            /** @var \Tygh\Storefront\Storefront $storefront */
            $storefront = $params['storefront'] instanceof Storefront
                ? $params['storefront']
                : Tygh::$app['storefront'];

            $storefront_id = $storefront->storefront_id;
        }

        $destination_id = fn_warehouses_get_destination_id_by_product_params($params);

        $join .= db_quote(
            ' LEFT JOIN ?:warehouses_destination_products_amount AS warehouses_destination_products_amount'
            . ' ON warehouses_destination_products_amount.product_id = products.product_id'
            . ' AND warehouses_destination_products_amount.destination_id = ?i'
            . ' AND warehouses_destination_products_amount.storefront_id = ?i',
            $destination_id,
            $storefront_id
        );

        // FIXME Dirty hack
        $condition = str_replace(
            'products.amount',
            db_quote(
                '(CASE products.is_stock_split_by_warehouses WHEN ?s'
                . ' THEN warehouses_destination_products_amount.amount'
                . ' ELSE products.amount END)',
                YesNo::YES
            ),
            $condition
        );
    }
    $sort_products_by_amount = !SiteArea::isStorefront($params['area']) && $params['sort_by'] === 'amount';
    if (!$sort_products_by_amount) {
        return;
    }

    $fields['complex_amount'] = db_quote(
        '(CASE products.is_stock_split_by_warehouses WHEN ?s'
        . ' THEN war_sum_amount.amount'
        . ' ELSE products.amount END) as complex_amount',
        YesNo::YES
    );
    $params['sort_by'] = 'complex_amount';
    $sortings[$params['sort_by']] = $params['sort_by'];
}

/**
 * The "delete_product_post" hook handler.
 *
 * Actions performed:
 *  - Removes product warehouse relations
 *
 * @see \fn_delete_product()
 */
function fn_warehouses_delete_product_post($product_id, $product_deleted)
{
    if (!$product_deleted) {
        return;
    }

    ServiceProvider::getManager()->removeProductStocks($product_id);
}

/**
 * Hook handler: excludes warehouses from the list
 */
function fn_warehouses_get_store_locations_before_select($params, &$fields, &$joins, &$conditions, &$sortings, $items_per_page, $lang_code)
{
    if (!empty($params['store_types'])) {
        $store_types = (array) $params['store_types'];
        $conditions['store_types'] = db_quote('store_type IN (?a)', $store_types);
    } elseif (AREA == 'C') {
        $conditions['not_warehouse'] = db_quote('store_type <> ?s', Manager::STORE_LOCATOR_TYPE_WAREHOUSE);
    }

    $sortings['destination_position_name'] = 'position asc, ?:store_location_descriptions.name';

    $get_destinations_data = !empty($params['shipping_destination_id'])
        || !empty($params['pickup_destination_id']);
    $destination_id = null;

    if (!empty($params['shipping_destination_id'])) {
        $destination_id = $params['shipping_destination_id'];
        $conditions['destination_id'] = db_quote(
            'FIND_IN_SET(?i, shipping_destinations_ids)',
            $params['shipping_destination_id']
        );
    }

    if ($get_destinations_data) {
        $fields['link_id'] = 'destination_links.link_id';
        $fields['position'] = '(CASE'
            . ' WHEN destination_links.position IS NOT NULL'
            . ' THEN destination_links.position'
            . ' ELSE ?:store_locations.position'
            . ' END) AS position';
        $fields['warn_about_delay'] = 'destination_links.warn_about_delay';
        $joins['destination_links'] = db_quote(
            ' LEFT JOIN ?:store_location_destination_links AS destination_links'
            . ' ON destination_links.store_location_id = ?:store_locations.store_location_id'
            . ' AND destination_links.destination_id = ?i',
            $destination_id
        );

        $fields['shipping_delay'] = 'shipping_delays.shipping_delay';
        $joins['store_location_shipping_delays'] = db_quote(
            ' LEFT JOIN ?:store_location_shipping_delays AS shipping_delays'
            . ' ON shipping_delays.store_location_id = ?:store_locations.store_location_id'
            . ' AND shipping_delays.destination_id = ?i'
            . ' AND shipping_delays.lang_code = ?s',
            $destination_id,
            $lang_code
        );

        $fields['main_destination'] = 'destination_descriptions.destination AS main_destination';
        $joins['destination_descriptions'] = db_quote(
            ' LEFT JOIN ?:destination_descriptions AS destination_descriptions'
            . ' ON destination_descriptions.destination_id = ?:store_locations.main_destination_id'
            . ' AND destination_descriptions.lang_code = ?s',
            $lang_code
        );
    }
}

/**
 * Hook handler: excludes warehouses from stores list for shipping
 */
function fn_warehouses_get_store_locations_for_shipping_before_select($destination_id, $fields, $joins, &$conditions)
{
    $conditions['not_warehouse'] = db_quote('store_type <> ?s', Manager::STORE_LOCATOR_TYPE_WAREHOUSE);
}

/**
 * Hook handler: actualizes product amount data from warehouses before checking available amount
 */
function fn_warehouses_check_amount_in_stock_before_check($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, $product, &$current_amount)
{
    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    $product_stock = $manager->getProductWarehousesStock($product_id);

    if (!$product_stock->hasStockSplitByWarehouses()) {
        return;
    }

    $location = fn_warehouses_get_location_from_cart($cart);
    $pickup_point_id = fn_warehouses_get_pickup_point_id_from_cart($cart, $cart_id);
    $destination_id = fn_warehouses_get_destination_id($location);

    $product_amount = $product_stock->getAmount();
    if ($pickup_point_id && $product_stock->getWarehousesById($pickup_point_id)) {
        $store = $product_stock->getWarehousesById($pickup_point_id);
        $store = reset($store);
        $product_amount = $product_stock->getAmountForDestination($store->getMainDestinationId());
    } elseif ($destination_id) {
        $product_amount = $product_stock->getAmountForDestination($destination_id);
    }

    $current_amount = $product_amount;

    if (!empty($cart['products'][$cart_id]['amount']) && !$current_amount) {
        Tygh::$app['session']['warehouses']['out_of_stock_products'][$product_id] = $product_id;
    }
}

/**
 * Hook handler: preserves original product amount
 */
function fn_warehouses_update_product_amount_pre($product_id, $amount_delta, $product_options, $sign, $tracking, &$current_amount, $product_code, $notify, $order_info, $cart_id)
{
    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    $product_stock = $manager->getProductWarehousesStock($product_id);

    if (!$product_stock->hasStockSplitByWarehouses()) {
        return;
    }

    if ($order_info) {
        $location = fn_warehouses_get_location_from_order($order_info);
        $destination_id = fn_warehouses_get_destination_id($location);
    }

    if (!empty($destination_id)) {
        $warehouses_product_amount = $product_stock->getAmountForDestination($destination_id);
    } else {
        $warehouses_product_amount = $product_stock->getAmount();
    }

    // Set current amount to be equal to amount from warehouses, for proper routines (execution end or notification) in the "parent" function.
    $current_amount = $warehouses_product_amount;
}

/**
 * Hook handler: splits product amount changes between warehouses amounts
 */
function fn_warehouses_update_product_amount(&$new_amount, $product_id, $cart_id, $tracking, $notify, $order_info, $amount_delta, $current_amount, $original_amount, $sign)
{
    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    /** @var Tygh\Addons\Warehouses\ProductStock $product_stock */
    $product_stock = $manager->getProductWarehousesStock($product_id);

    if (!$product_stock->hasStockSplitByWarehouses()) {
        return;
    }

    // return amount that will be save to main table to its original amount
    $new_amount = $original_amount;

    if ($order_info) {
        $location = fn_warehouses_get_location_from_order($order_info);
        $pickup_point_id = fn_warehouses_get_pickup_point_id_from_order($order_info, $product_id, $cart_id);
        $destination_id = fn_warehouses_get_destination_id($location);
    }

    if ($sign == '-') {
        if (!empty($pickup_point_id) && $product_stock->getWarehousesById($pickup_point_id)) {
            $product_stock->reduceStockByAmountForStore($amount_delta, $pickup_point_id);
        } elseif (!empty($destination_id)) {
            $product_stock->reduceStockByAmountForDestination($amount_delta, $destination_id);
        } else {
            $product_stock->reduceStockByAmount($amount_delta);
        }
    } else {
        if ($product_stock->getAmount() <= 0 && $amount_delta > 0) {
            fn_send_product_notifications($product_id);
        }
        $product_stock->increaseStockByAmount($amount_delta);
    }

    $manager->saveProductStock($product_stock, false);
}

/**
 * Hook handler: sets flag if fetching warehouses amount for products is required
 */
function fn_warehouses_gather_additional_products_data_pre($products, &$params, $lang_code)
{
    if (!isset($params['get_warehouse_amount'])) {
        $params['get_warehouse_amount'] = false;
    }

    if (!isset($params['get_warehouse_amount_by_destination'])) {
        $params['get_warehouse_amount_by_destination'] = false;
    }

    if (!isset($params['get_warehouse_total_amount'])) {
        $params['get_warehouse_total_amount'] = false;
    }

    if (AREA === 'A') {
        $params['get_warehouse_amount'] = true;
    } elseif (AREA === 'C') {
        $product = isset($products['product_id']) ? $products : reset($products);
        $params['get_warehouse_amount_by_destination'] = $product && !isset($product['subtotal']);
    }
}

/**
 * Hook handler: fetching warehouses amount for products is required
 */
function fn_warehouses_gather_additional_products_data_post($product_ids, $params, &$products, $auth, $lang_code)
{
    if (empty($product_ids)) {
        return;
    }

    /** @var Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];

    if ($params['get_warehouse_amount']) {
        $products = $manager->fetchProductsWarehousesAmounts($products);
    }

    if ($params['get_warehouse_total_amount']) {
        $products = $manager->fetchProductsWarehousesTotalAmounts($products);
    } elseif ($params['get_warehouse_amount_by_destination']) {
        /** @var \Tygh\Storefront\Storefront $storefront */
        $storefront = isset($params['storefront']) && $params['storefront'] instanceof Storefront
            ? $params['storefront']
            : Tygh::$app['storefront'];

        $storefront_id = $storefront->storefront_id;

        $destination_id = fn_warehouses_get_destination_id_by_product_params($params);
        $products = $manager->fetchProductsWarehousesAmountsByDestination($products, $destination_id, $storefront_id);
    }
}

/**
* The "delete_destinations_post" hook handler.
 *
 * Actions performed:
 *  - Removes destination links and shipping delay information for deleted destinations.
 *
 * @see \fn_delete_destinations()
 */
function fn_warehouses_delete_destinations_post($destination_ids)
{
    db_query('DELETE FROM ?:store_location_destination_links WHERE destination_id IN (?n)', $destination_ids);
    db_query('DELETE FROM ?:store_location_shipping_delays WHERE destination_id IN (?n)', $destination_ids);
}

/**
 * The "store_locator_delete_store_location_post" hook handler.
 *
 * Actions performed:
 *  - Removes destination links and shipping delay information for deleted store locations.
 *
 * @see \fn_delete_store_location()
 */
function fn_warehouses_store_locator_delete_store_location_post($store_location_id, $affected_rows, $deleted)
{
    /** @var \Tygh\Addons\Warehouses\Manager $manager */
    $manager = Tygh::$app['addons.warehouses.manager'];
    $manager->removeWarehouse($store_location_id);
}

/**
 * The "update_store_location_before_update" hook handler.
 *
 * Actions performed:
 *     - Joins "Ship to" values to the comma-separated list of IDs.
 *     - Removes "Show to" values from Warehouses and "Ship to" values from Pickup points.
 *     - Saves Shipping delay and Warn about delay settings.
 *
 * @see \fn_update_store_location()
 */
function fn_warehouses_store_locator_update_store_location_before_update(&$store_location_data, $store_location_id, $lang_code)
{
    $store_location_data['recalucalate_products_amounts'] = false;

    if ($store_location_data['store_type'] === Manager::STORE_LOCATOR_TYPE_WAREHOUSE) {
        $store_location_data['pickup_destinations_ids'] = '0';
    } elseif ($store_location_data['store_type'] === Manager::STORE_LOCATOR_TYPE_PICKUP) {
        $store_location_data['shipping_destinations_ids'] = '0';
    }

    if ($store_location_id) {
        $current_location_data = db_get_row(
            'SELECT status FROM ?:store_locations WHERE store_location_id = ?i',
            $store_location_id
        );

        if (
            isset($store_location_data['status'])
            && $store_location_data['status'] !== $current_location_data['status']
        ) {
            $store_location_data['recalucalate_products_amounts'] = true;
        }
    }

    if (isset($store_location_data['shipping_destinations'])) {
        $current_destinations_ids = db_get_fields(
            'SELECT destination_id FROM ?:store_location_destination_links WHERE store_location_id = ?i',
            $store_location_id
        );

        $store_location_data['shipping_destinations_ids'] = [];

        $destinations = $store_location_data['shipping_destinations'] ?: [];
        $destinations_ids = [];

        foreach ($destinations as $destination) {
            $destination_id = $destination['destination_id'];
            $store_location_data['shipping_destinations_ids'][] = $destination_id;

            $destination['store_location_id'] = $store_location_id;
            if (empty($destination['position'])) {
                $destination['position'] = 1 + (int) db_get_field(
                    'SELECT MAX(position)'
                    . ' FROM ?:store_location_destination_links'
                    . ' WHERE destination_id = ?i',
                    $destination['destination_id']
                );
            }
            db_replace_into('store_location_destination_links', $destination);

            $shipping_delay_exists = (bool) db_get_field(
                'SELECT COUNT(*) FROM ?:store_location_shipping_delays'
                . ' WHERE destination_id = ?i'
                . ' AND store_location_id = ?i',
                $destination_id,
                $store_location_id
            );

            $language_codes_list = [$lang_code];
            if (!$shipping_delay_exists) {
                $language_codes_list = array_keys(Languages::getAll());
            }

            foreach ($language_codes_list as $language_code) {
                $destination['lang_code'] = $language_code;
                db_replace_into('store_location_shipping_delays', $destination);
            }

            $destinations_ids[] = (int) $destination_id;
        }

        $store_location_data['shipping_destinations_ids'] = $store_location_data['shipping_destinations_ids'] ?: [0];

        db_query(
            'DELETE FROM ?:store_location_destination_links WHERE store_location_id = ?i AND destination_id NOT IN (?n)',
            $store_location_id,
            $store_location_data['shipping_destinations_ids']
        );

        db_query(
            'DELETE FROM ?:store_location_shipping_delays WHERE store_location_id = ?i AND destination_id NOT IN (?n)',
            $store_location_id,
            $store_location_data['shipping_destinations_ids']
        );

        $store_location_data['shipping_destinations_ids'] = implode(',', $store_location_data['shipping_destinations_ids']);

        array_walk($current_destinations_ids, 'intval');
        sort($current_destinations_ids);
        sort($destinations_ids);

        $store_location_data['recalucalate_products_amounts'] = $store_location_data['recalucalate_products_amounts']
            || $current_destinations_ids != $destinations_ids;
    }
}

/**
 * The "update_store_location_before_update" hook handler.
 *
 * Actions performed:
 *     - Removes warehouse data from a store location that is not a store nor a warehouse
 *
 * @see \fn_update_store_location()
 */
function fn_warehouses_store_locator_update_store_location_post($store_location_data, $store_location_id, $lang_code, $action)
{
    if (
        isset($store_location_data['store_type'])
        && $store_location_data['store_type'] === Manager::STORE_LOCATOR_TYPE_PICKUP
    ) {
        ServiceProvider::getManager()->removeWarehouse($store_location_id);
    } elseif (!empty($store_location_data['recalucalate_products_amounts'])) {
        ServiceProvider::getManager()->recalculateDestinationProductsStocksByWarehouseIds([$store_location_id]);
    }
}

/**
 * The "store_locator_get_store_location_post" hook handler.
 *
 * Actions performed:
 *     - Splits "Ship to" value to the list of IDs.
 *
 * @see \fn_get_store_location()
 */
function fn_warehouses_store_locator_get_store_location_post($store_location_id, $lang_code, &$store_location)
{
    if (!empty($store_location['shipping_destinations_ids'])) {
        $store_location['shipping_destinations_ids'] = explode(',', $store_location['shipping_destinations_ids']);
    } elseif (isset($store_location['shipping_destinations_ids'])) {
        $store_location['shipping_destinations_ids'] = [];
    }
}

/**
 * The "render_block_pre" hook handler.
 *
 * Actions performed:
 *     - Marks blocks that depends by customer location
 *
 * @see \Tygh\BlockManager\RenderManager::renderBlockContent()
 */
function fn_warehouses_render_block_pre(&$block, $block_schema, $params, $block_content)
{
    if (empty($block_schema['depends_by_customer_location'])) {
        return;
    }

    $dispatch = Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode');

    if ($block_schema['type'] !== 'main' || in_array($dispatch, $block_schema['depends_by_customer_location'])) {
        if (!isset($block['user_class'])) {
            $block['user_class'] = '';
        }

        $block['user_class'] .= ' cm-warehouse-block-depends-by-location';
    }
}

/**
 * The "ult_delete_company" hook handler.
 *
 * Actions performed:
 *     - Removes records related to storefronts
 *
 * @see \fn_ult_delete_company()
 */
function fn_warehouses_ult_delete_company($company_id, $result, $storefronts)
{
    $storefront_ids = [];

    /** @var Storefront $storefront */
    foreach ($storefronts as $storefront) {
        $storefront_ids[] = $storefront->storefront_id;
    }

    if ($storefront_ids) {
        db_query('DELETE FROM ?:warehouses_destination_products_amount WHERE storefront_id IN (?n)', $storefront_ids);
    }
}

/**
 * The "tools_change_status" hook handler.
 *
 * Actions performed:
 *     - if store location status changed than recalculates products amount by storefront and destination
 *
 * @see \fn_tools_update_status()
 */
function fn_warehouses_tools_change_status($params, $result)
{
    if (!$result || $params['table'] !== 'store_locations' || empty($params['id'])) {
        return;
    }

    ServiceProvider::getManager()->recalculateDestinationProductsStocksByWarehouseIds([$params['id']]);
}

/**
 * Extracts selected pickup point ID from the cart contents.
 *
 * @param array $cart    Cart contents
 * @param int   $cart_id Product cart ID
 *
 * @return int|null
 */
function fn_warehouses_get_pickup_point_id_from_cart(array $cart, $cart_id)
{
    if (empty($cart['product_groups'])) {
        return null;
    }

    foreach ($cart['product_groups'] as $group_id => $product_group) {
        if (!isset($product_group['products'][$cart_id])) {
            continue;
        }

        if (empty($product_group['chosen_shippings'])) {
            return null;
        }

        $shipping_id = reset($product_group['chosen_shippings'])['shipping_id'];
        if (isset($cart['select_store'][$group_id][$shipping_id])) {
            return $cart['select_store'][$group_id][$shipping_id];
        }
    }

    return null;
}

/**
 * Gets destinations list for destinations picker.
 *
 * @param array $params
 *
 * @return array
 * @internal
 */
function fn_warehouses_get_destinations_for_picker(array $params)
{
    $params = array_merge(
        [
            'ids'               => [],
            'store_location_id' => null,
            'page'              => null,
            'page_size'         => null,
            'q'                 => '',
        ],
        $params
    );

    $destinations = fn_get_destinations(DESCR_SL);
    if ($params['ids']) {
        $destinations = array_filter(
            $destinations,
            function ($destination) use ($params) {
                return in_array($destination['destination_id'], $params['ids']);
            }
        );
    }
    if ($params['q'] !== '') {
        $destinations = array_filter(
            $destinations,
            function ($destination) use ($params) {
                $search_critiera = fn_strtolower($params['q']);
                $destination_name = fn_strtolower($destination['destination']);

                return strstr($destination_name, $search_critiera) !== false;
            }
        );
    }

    $manager = ServiceProvider::getManager();
    $warehouse_destinations = $manager->initializeDestinationsByWarehouseId($params['store_location_id']);
    $destinations = array_map(
        function ($destination) use ($warehouse_destinations) {
            $destination_id = (int) $destination['destination_id'];
            $destination_name = $destination['destination'];
            /** @var \Tygh\Addons\Warehouses\Destination|null $warehouse_destination */
            $warehouse_destination = isset($warehouse_destinations[$destination_id])
                ? $warehouse_destinations[$destination_id]
                : null;

            return [
                'id'   => $destination_id,
                'text' => $destination_name,
                'data' => [
                    'url'              => fn_url('destinations.update?destination_id=' . $destination_id),
                    'destination'      => $destination_name,
                    'destination_id'   => $destination_id,
                    'warn_about_delay' => $warehouse_destination
                        ? (int) $warehouse_destination->isWarnAboutDelay()
                        : 0,
                    'shipping_delay'   => $warehouse_destination
                        ? $warehouse_destination->getShippingDelay()
                        : '',
                    'position' => $warehouse_destination
                        ? $warehouse_destination->getPosition()
                        : 0,
                ],
            ];
        },
        $destinations
    );

    $objects = $destinations;
    if ($params['page'] && $params['page_size']) {
        $objects = array_slice($objects, ($params['page'] - 1) * $params['page_size'], $params['page_size']);
    }
    $total_objects = count($destinations);

    return [$objects, $total_objects];
}

/**
 * Gets destination ID by products search params
 *
 * @param array $params
 *
 * @return int
 * @internal
 */
function fn_warehouses_get_destination_id_by_product_params(array $params)
{
    if (isset($params['warehouses_destination_id'])) {
        $destination_id = (int) $params['warehouses_destination_id'];
    } else {
        /** @var \Tygh\Location\Manager $manager */
        $manager = Tygh::$app['location'];
        $destination_id = $manager->getDestinationId();
    }

    return $destination_id;
}

/**
 * Recalculates products amount by storefront and destination
 *
 * @param int        $object_id   Object id
 * @param string     $object_type Object type
 * @param array<int> $companies   Company ids
 */
function fn_warehouses_update_share_objects_post_processing($object_id, $object_type, array $companies)
{
    if ($object_type !== 'store_locations') {
        return;
    }

    ServiceProvider::getManager()->recalculateDestinationProductsStocksByWarehouseIds([$object_id]);
}

/**
 * The "commerceml_product_importer_import_pre" hook handler.
 *
 * Actions performed:
 *  - Saves quantity by warehouses into ProductDto properties
 *
 * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement          $element        Xml element
 * @param \Tygh\Addons\CommerceML\Storages\ImportStorage        $import_storage Import storage instance
 * @param \Tygh\Addons\CommerceML\Dto\ProductDto                $product        Product DTO
 * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities       Other entites data
 *
 * @see \Tygh\Addons\CommerceML\Convertors\ProductConvertor::convert
 */
function fn_warehouses_commerceml_product_convertor_convert(SimpleXmlElement $element, ImportStorage $import_storage, ProductDto &$product, array $entities)
{
    /** @var \Tygh\Addons\Warehouses\CommerceML\Dto\ProductWarehouseQuantityDtoCollection $product_warehouses_qty */
    $product_warehouses_qty = new ProductWarehouseQuantityDtoCollection();

    if (!$element->has('warehouse')) {
        return;
    }

    /**
     * @psalm-suppress PossiblyNullIterator
     */
    foreach ($element->get('warehouse', []) as $item) {
        if (!$item->has('@warehouse_id')) {
            return;
        }
        $warehouse_quantity_dto = ProductWarehouseQuantityDto::create(
            IdDto::createByExternalId($item->getAsString('@warehouse_id')),
            $item->getAsInt('@warehouse_in_stock', 0)
        );
        $product_warehouses_qty->add($warehouse_quantity_dto);
    }

    $product->properties->add(PropertyDto::create(
        'warehouses',
        $product_warehouses_qty
    ));
}

/**
 * The "commerceml_product_importer_import_pre" hook handler.
 *
 * Actions performed:
 *  - Adds import warehouses in CommerceML format
 *
 * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
 * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
 * @param \Tygh\Common\OperationResult                   $main_result    Parent category DTO
 *
 * @see \Tygh\Addons\CommerceML\Importers\ProductImporter::import
 */
function fn_warehouses_commerceml_product_importer_import_pre(ProductDto &$product, ImportStorage $import_storage, OperationResult &$main_result)
{
    if (!$product->properties->has('warehouses')) {
        return;
    }

    $allow_import_warehouses = $import_storage->getSetting('catalog_importer.allow_import_warehouses', true);
    $warehouse_importer = ServiceProvider::getWarehouseImporter();

    /**
     * @var \Tygh\Addons\Warehouses\CommerceML\Dto\ProductWarehouseQuantityDtoCollection $warehouse_quantity_dto_collections
     */
    $warehouse_quantity_dto_collections = $product->properties->get('warehouses', [])->value;
    if (!$warehouse_quantity_dto_collections instanceof ProductWarehouseQuantityDtoCollection) {
        return;
    }

    $product->properties->add(PropertyDto::create('warehouses_update_stock_only', true));

    foreach ($warehouse_quantity_dto_collections as $warehouse_quantity_dto) {
        $warehouse_id_dto = $warehouse_quantity_dto->warehouse_id;
        if ($warehouse_id_dto->hasLocalId()) {
            continue;
        }

        $warehouse = $import_storage->findEntity(WarehouseDto::REPRESENT_ENTITY_TYPE, $warehouse_id_dto->getId());

        if ($warehouse && $warehouse instanceof WarehouseDto && $allow_import_warehouses === true) {
            $result = $warehouse_importer->import($warehouse, $import_storage);

            $main_result->merge($result);

            if ($result->isFailure()) {
                $main_result->setSuccess(false);
                return;
            }
        }

        $warehouse_local_id = $import_storage->findEntityLocalId(WarehouseDto::REPRESENT_ENTITY_TYPE, $warehouse_id_dto);

        if ($warehouse_local_id->hasNotValue()) {
            $main_result->setSuccess(false);
            $main_result->addError('product.warehouse_not_found', __('warehouses.commerceml.import.error.product.warehouse_not_found', [
                '[id]' => $warehouse_id_dto->getId()
            ]));
            return;
        }

        $warehouse_id_dto->local_id = $warehouse_local_id->asInt();
    }
}

/**
 * The "warehouses_manager_remove_warehouse" hook handler.
 *
 * Actions performed:
 *  - Delete CommerceML warehouse entity from entities map
 *
 * @param int $warehouse_id Warehouse identifier
 *
 * @see \Tygh\Addons\Warehouses\Manager::removeWarehouse
 */
function fn_commerceml_warehouses_manager_remove_warehouse($warehouse_id)
{
    CommerceMLServiceProvider::getImportEntityMapRepository()->removeByLocalId(WarehouseDto::REPRESENT_ENTITY_TYPE, $warehouse_id);
}

/**
 * @param int    $object_id           Shareable object ID
 * @param string $object_type         Shareable object type
 * @param int    $company_id          Company ID
 * @param int    $affected_rows_count Affected rows count
 *
 * @return void
 */
function fn_warehouses_ult_update_share_object($object_id, $object_type, $company_id, $affected_rows_count)
{
    if ($object_type !== 'store_locations' || !$affected_rows_count) {
        return;
    }
    $repository = StorefrontProvider::getRepository();
    /** @var Storefront $storefront */
    $storefront = $repository->findByCompanyId($company_id, true);
    if (!$storefront) {
        return;
    }
    $products = db_get_hash_array(
        'SELECT war_pro.product_id, war_pro.amount as warehouse_amount FROM ?:warehouses_products_amount as war_pro WHERE warehouse_id = ?i',
        'product_id',
        $object_id
    );
    $totals = db_get_hash_array(
        'SELECT product_id, amount FROM ?:warehouses_sum_products_amount WHERE storefront_id = ?i',
        'product_id',
        $storefront->storefront_id
    );
    $data = [];
    foreach ($products as $product) {
        $total = isset($totals[$product['product_id']]) ? $totals[$product['product_id']]['amount'] : 0;
        $data[] = [
            'product_id' => $product['product_id'],
            'amount'     => $total + $product['warehouse_amount'],
            'storefront_id' => $storefront->storefront_id,
        ];
    }
    foreach ($data as $product_data) {
        db_replace_into('warehouses_sum_products_amount', $product_data);
    }
}

/**
 * @param int      $object_id           Shareable object ID
 * @param string   $object_type         Shareable object type
 * @param int|null $company_id          Company ID. If not specified it mean object unshared for all
 * @param int      $affected_rows_count Affected rows count
 *
 * @return void
 */
function fn_warehouses_ult_unshare_object($object_id, $object_type, $company_id, $affected_rows_count)
{
    if ($object_type !== 'store_locations' || !$affected_rows_count || !$company_id) {
        return;
    }
    $repository = StorefrontProvider::getRepository();
    /** @var Storefront $storefront */
    $storefront = $repository->findByCompanyId($company_id, true);
    if (!$storefront) {
        return;
    }
    $products = db_get_hash_array(
        'SELECT war_pro.product_id, war_pro.amount as warehouse_amount FROM ?:warehouses_products_amount as war_pro WHERE warehouse_id = ?i',
        'product_id',
        $object_id
    );
    $totals = db_get_hash_array(
        'SELECT product_id, amount FROM ?:warehouses_sum_products_amount WHERE storefront_id = ?i',
        'product_id',
        $storefront->storefront_id
    );
    $data = [];
    foreach ($products as $product) {
        $amount = $totals[$product['product_id']]['amount'] - $product['warehouse_amount'];
        $data[] = [
            'product_id' => $product['product_id'],
            'amount'     => $amount,
            'storefront_id' => $storefront->storefront_id,
        ];
    }
    foreach ($data as $product_data) {
        if ($product_data['amount']) {
            db_replace_into('warehouses_sum_products_amount', $product_data);
        } else {
            db_query(
                'DELETE FROM ?:warehouses_sum_products_amount WHERE product_id = ?i AND storefront_id',
                $product_data['product_id'],
                $storefront->storefront_id
            );
        }
    }
}

/**
 * The "get_filters_products_count_pre" hook handler.
 *
 * @param array<string, int> $params       Params
 * @param array<string>      $cache_params Cache params
 * @param array<string>      $cache_tables Cache tables
 *
 * @return void
 */
function fn_warehouses_get_filters_products_count_pre(array &$params, array &$cache_params, array &$cache_tables)
{
    $cache_tables[] = 'store_locations';
    $cache_tables[] = 'store_location_destination_links';
    $cache_tables[] = 'warehouses_destination_products_amount';

    $cache_params[] = 'customer_destination_id';

    /** @var \Tygh\Location\Manager $location */
    $location = Tygh::$app['location'];
    $params['customer_destination_id'] = $location->getDestinationId();
}
