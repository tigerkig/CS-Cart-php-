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


namespace Tygh\Addons\ProductVariations\HookHandlers;


use Tygh;
use Tygh\Addons\ProductVariations\ServiceProvider;
use Tygh\Application;

/**
 * This class describes the hook handlers related to cart, checkout, and order management
 *
 * @package Tygh\Addons\ProductVariations\HookHandlers
 */
class CartsHookHandler
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "get_cart_products_post" hook handler.
     *
     * Actions performed:
     *  - Fetches the information for all products about the variation groups to which they belong.
     *
     * @see fn_get_cart_products
     */
    public function onGetCartProductsPost($user_id, $params, &$cart_products, $fields, $conditions)
    {
        if (empty($cart_products)) {
            return;
        }
    
        $cart_products = ServiceProvider::getProductRepository()->loadProductsGroupInfo($cart_products);
    }

    /**
     * The "get_order_info" hook handler.
     *
     * Actions performed:
     *  - Fetches the information for all products about the variation groups to which they belong.
     *
     * @see fn_get_order_info
     */
    public function onGetOrderInfo(&$order, $additional_data)
    {
        if (empty($order['products'])) {
            return;
        }
        $order['products'] = ServiceProvider::getProductRepository()->loadProductsGroupInfo($order['products']);
    }

    /**
     * The "get_user_edp_post" hook handler.
     *
     * Actions performed:
     *  - Fetches the information for all products about the variation groups to which they belong.
     *
     * @see fn_get_user_edp
     */
    public function onGetUserEdpPost($params, $items_per_page, &$products)
    {
        if (empty($products)) {
            return;
        }

        $products = ServiceProvider::getProductRepository()->loadProductsGroupInfo($products);
    }

    /**
     * The "check_amount_in_stock_before_check" hook handler.
     *
     * Actions performed:
     *  - Actualizes the product name for variation (product name + feature values).
     *
     * @see fn_check_amount_in_stock
     */
    public function onCheckAmountInStockBeforeCheck($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart, $update_id, &$product, $current_amount)
    {
        if (!ServiceProvider::getProductIdMap()->isChildProduct($product_id)) {
            return;
        }

        $product['product'] = fn_get_product_name($product_id);
    }
}
