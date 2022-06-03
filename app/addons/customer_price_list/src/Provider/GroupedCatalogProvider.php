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

use Tygh\Enum\YesNo;

/**
 * Class GroupedCatalogProvider
 *
 * @package Tygh\Addons\CustomerPriceList\Provider
 */
class GroupedCatalogProvider extends CatalogProvider implements GroupedCatalogProviderInterface
{
    /**
     * @var string|null
     */
    protected $category = null;

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        $categories = fn_get_plain_categories_tree(0, false);

        foreach ($categories as $category) {
            $this->category = $this->getCategoryFullName($category);

            $page = 1;

            do {
                $products = $this->getProducts([
                    'page'    => $page++,
                    'cid'     => $category['category_id'],
                    'subcats' => YesNo::NO
                ]);

                foreach ($products as $product) {
                    yield $product;
                }

                if (count($products) < $this->items_per_page) {
                    break;
                }
            } while ($products);
        }
    }

    /**
     * @inheritDoc
     */
    public function getGroup()
    {
        return $this->category;
    }

    /**
     * @param array $category Category
     *
     * @return string
     */
    protected function getCategoryFullName(array $category)
    {
        $result = [];
        $cat_ids = fn_explode('/', $category['id_path']);

        if (!empty($cat_ids)) {
            $cats = fn_get_category_name($cat_ids);

            foreach ($cats as $cat_id => $cat_name) {
                $result[] = $cat_name;
            }
        }

        return implode(' - ', $result);
    }
}
