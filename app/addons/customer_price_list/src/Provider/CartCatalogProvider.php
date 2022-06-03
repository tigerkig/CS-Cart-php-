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

namespace Tygh\Addons\CustomerPriceList\Provider;

/**
 * Class CartCatalogProvider
 *
 * @package Tygh\Addons\CustomerPriceList\Provider
 */
class CartCatalogProvider implements CatalogProviderInterface
{
    /**
     * @var array
     */
    protected $cart = [];

    /**
     * CartProvider constructor.
     *
     * @param array $cart Session cart
     */
    public function __construct(array $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        $products = $this->getProducts();

        foreach ($products as $product) {
            yield $product;
        }
    }

    /**
     * @return array
     */
    protected function getProducts()
    {
        $cart_products = isset($this->cart['products']) ? (array) $this->cart['products'] : [];
        $product_ids = array_column($cart_products, 'product_id');

        if (empty($product_ids)) {
            return $cart_products;
        }

        list($products) = fn_get_products([
            'pid' => $product_ids
        ]);

        foreach ($cart_products as &$cart_product) {
            $product_id = $cart_product['product_id'];

            if (!isset($products[$product_id])) {
                continue;
            }

            $cart_product = array_merge($products[$product_id], $cart_product);
        }
        unset($cart_product);

        return $cart_products;
    }
}
