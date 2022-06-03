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
 * Class CatalogProvider
 *
 * @package Tygh\Addons\CustomerPriceList\Provider
 */
class CatalogProvider implements CatalogProviderInterface
{
    /**
     * @var string
     */
    protected $sort_by = 'product_id';

    /**
     * @var array<int, string>
     */
    protected $field_ids = [];

    /**
     * @var array<string, mixed>
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $lang_code = '';

    /**
     * @var int
     */
    protected $items_per_page = 100;

    /**
     * CatalogProvider constructor.
     *
     * @param array<int, string> $field_ids Fields of price list
     * @param string             $sort_by   Sorting
     * @param array              $params    Additional params for fn_get_products
     * @param string             $lang_code Lang code
     */
    public function __construct(array $field_ids, $sort_by, array $params, $lang_code)
    {
        $this->field_ids = $field_ids;
        $this->sort_by = $sort_by;
        $this->lang_code = $lang_code;
        $this->params = array_merge([
            'skip_view' => true,
        ], $params, [
            'sort_by' => $sort_by
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        $page = 1;

        do {
            $products = $this->getProducts([
                'page' => $page++
            ]);

            foreach ($products as $product) {
                yield $product;
            }

            if (count($products) < $this->items_per_page) {
                break;
            }
        } while ($products);
    }

    /**
     * @param array $params Additional params for fn_get_products
     *
     * @return array
     */
    protected function getProducts(array $params = [])
    {
        $params = array_merge($this->params, $params);

        list($products) = fn_get_products($params, $this->items_per_page, $this->lang_code);

        $get_images = in_array('image', $this->field_ids);

        fn_gather_additional_products_data($products, [
            'get_icon'       => $get_images,
            'get_detailed'   => $get_images,
            'get_options'    => false,
            'get_additional' => false,
            'get_discounts'  => true,
        ]);

        return $products;
    }
}
