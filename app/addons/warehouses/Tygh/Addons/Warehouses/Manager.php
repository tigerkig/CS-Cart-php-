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

namespace Tygh\Addons\Warehouses;

use Tygh\Database\Connection;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;
use Tygh\Providers\StorefrontProvider;

class Manager
{
    const STORE_LOCATOR_TYPE_WAREHOUSE = 'W';
    const STORE_LOCATOR_TYPE_STORE = 'S';
    const STORE_LOCATOR_TYPE_PICKUP = 'P';

    /** @var Connection */
    protected $db;

    /** @var string */
    protected $language_code;

    /** @var bool */
    protected $is_mve;

    public function __construct(Connection $db, $language_code, $is_mve)
    {
        $this->db = $db;
        $this->language_code = $language_code;
        $this->is_mve = (bool) $is_mve;
    }

    /**
     * Fetches available warehouses
     *
     * @param int|null $company_id Company identifier
     *
     * @return mixed
     */
    public function getWarehouses($company_id = null)
    {
        $params = ['store_types' => [self::STORE_LOCATOR_TYPE_WAREHOUSE, self::STORE_LOCATOR_TYPE_STORE]];
        if ($company_id !== null) {
            $params['company_id'] = $company_id;
        }

        list($warehouses) = fn_get_store_locations($params, 0, $this->language_code);
        foreach ($warehouses as &$warehouse) {
            $warehouse['warehouse_id'] = $warehouse['store_location_id'];
        }

        return $warehouses;
    }

    /**
     * Creates product store manager
     *
     * @param int   $product_id         Product identifier
     *
     * @return \Tygh\Addons\Warehouses\ProductStock
     */
    public function getProductWarehousesStock($product_id)
    {
        $product_warehouses = $this->getProductWarehousesData($product_id);
        $is_stock_split_by_warehouses = $this->isProductStockSplitByWarehouses($product_id);

        return new ProductStock($product_id, $product_warehouses, $is_stock_split_by_warehouses);
    }

    /**
     * Creates product store manager based on prowided warehouses stock data
     *
     * @param int   $product_id         Product identifier
     * @param array $warehouses_amounts Product warehouses amount values
     *
     * @return \Tygh\Addons\Warehouses\ProductStock
     */
    public function createProductStockFromWarehousesData($product_id, $warehouses_amounts)
    {
        $warehouse_ids = array_column($warehouses_amounts, 'warehouse_id');

        if ($warehouse_ids) {
            $warehouses = $this->db->getHash(
                'SELECT store_location_id AS warehouse_id, store_type, position, main_destination_id, pickup_destinations_ids, shipping_destinations_ids, status'
                . ' FROM ?:store_locations'
                . ' WHERE store_location_id IN (?n)',
                'warehouse_id',
                $warehouse_ids
            );

            $valid_warehouse_amounts = array_filter($warehouses_amounts, function ($warehouse) use ($warehouses) {
                return isset($warehouses[$warehouse['warehouse_id']]);
            });

            $product_warehouses = [];
            foreach ($valid_warehouse_amounts as $warehouse) {
                $product_warehouses[] = array_merge($warehouses[$warehouse['warehouse_id']], $warehouse);
            }
        } else {
            $product_warehouses = [];
        }

        $product_warehouses = $this->initializeDestinations($product_warehouses);

        return new ProductStock($product_id, $product_warehouses);
    }

    /**
     * Fetches product warehouses amounts for group of products
     *
     * @param array $products Products
     *
     * @return array
     */
    public function fetchProductsWarehousesAmounts($products)
    {
        if (empty($products)) {
            return $products;
        }

        $product_ids = array_column($products, 'product_id');
        $conditions = [$this->db->quote('?:warehouses_sum_products_amount.product_id IN (?n)', $product_ids)];
        if (!$this->is_mve) {
            $product_ids = $this->db->getColumn(
                'SELECT product_id FROM ?:products WHERE product_id IN (?n) AND is_stock_split_by_warehouses = ?s',
                $product_ids,
                YesNo::YES
            );
            $store = StorefrontProvider::getStorefront();
            if ($store) {
                $conditions = [$this->db->quote('?:warehouses_sum_products_amount.product_id IN (?n)', $product_ids)];
                $conditions[] = $this->db->quote('storefront_id = ?i', $store->storefront_id);
            }
        }
        $products_warehouses_amounts = $this->db->getHash(
            'SELECT product_id, amount FROM ?:warehouses_sum_products_amount WHERE ?p',
            'product_id',
            implode(' AND ', $conditions)
        );

        foreach ($products as &$product) {
            $product_id = $product['product_id'];
            if (isset($products_warehouses_amounts[$product_id])) {
                $product['warehouse_amount'] = $products_warehouses_amounts[$product_id]['amount'];
            } elseif (!$this->is_mve && in_array($product_id, $product_ids)) {
                $product['warehouse_amount'] = 0;
            }
        }

        return $products;
    }

    /**
     * Fetches product warehouses amounts by destination ID for group of products
     *
     * @param array $products       Products
     * @param int   $destination_id Customer destination ID
     * @param int   $storefront_id  Storefront ID
     *
     * @return array
     */
    public function fetchProductsWarehousesAmountsByDestination(array $products, $destination_id, $storefront_id)
    {
        $destination_id = (int) $destination_id;
        $storefront_id = (int) $storefront_id;

        if (empty($products) || !$destination_id || !$storefront_id) {
            return $products;
        }

        if ($this->is_mve) {
            $storefront_id = 0;
        }

        $products = $this->fetchProductsFlagIsStockSplitByWarehouses($products);

        $product_ids = array_column($products, 'product_id');
        $products_amounts = $this->db->getSingleHash(
            'SELECT product_id, amount FROM ?:warehouses_destination_products_amount'
            . ' WHERE product_id IN (?n) AND destination_id = ?i AND storefront_id = ?i',
            ['product_id', 'amount'],
            $product_ids,
            $destination_id,
            $storefront_id
        );

        foreach ($products as &$product) {
            $product_id = $product['product_id'];
            $is_stock_split_by_warehouses = !empty($product['is_stock_split_by_warehouses'])
                && $product['is_stock_split_by_warehouses'] === YesNo::YES;

            if (!$is_stock_split_by_warehouses) {
                continue;
            }

            $product['amount'] = isset($products_amounts[$product_id]) ? (int) $products_amounts[$product_id] : 0;
        }
        unset($product);

        return $products;
    }

    /**
     * Fetches product warehouses total amounts for group of products
     *
     * @param array $products Products
     *
     * @return array
     */
    public function fetchProductsWarehousesTotalAmounts(array $products)
    {
        if (empty($products)) {
            return $products;
        }

        $products = $this->fetchProductsFlagIsStockSplitByWarehouses($products);

        $product_ids = array_column($products, 'product_id');
        $products_amounts = $this->db->getSingleHash(
            'SELECT product_id, SUM(amount) AS amount FROM ?:warehouses_products_amount'
            . ' WHERE product_id IN (?n)'
            . ' GROUP BY product_id',
            ['product_id', 'amount'],
            $product_ids
        );

        foreach ($products as &$product) {
            $product_id = $product['product_id'];
            $is_stock_split_by_warehouses = !empty($product['is_stock_split_by_warehouses'])
                && $product['is_stock_split_by_warehouses'] === YesNo::YES;

            if (!$is_stock_split_by_warehouses) {
                continue;
            }

            $product['amount'] = isset($products_amounts[$product_id]) ? (int) $products_amounts[$product_id] : 0;
        }
        unset($product);

        return $products;
    }

    /**
     * Fetches product flag "is_stock_split_by_warehouses" if it not presented
     *
     * @param array $products
     *
     * @return array
     */
    public function fetchProductsFlagIsStockSplitByWarehouses(array $products)
    {
        if (empty($products)) {
            return $products;
        }

        $product_ids = [];

        foreach ($products as $product) {
            if (isset($product['is_stock_split_by_warehouses'])) {
                continue;
            }

            $product_ids[] = $product['product_id'];
        }

        if (empty($product_ids)) {
            return $products;
        }

        $is_stock_split_by_warehouses_map = $this->db->getSingleHash(
            'SELECT product_id, is_stock_split_by_warehouses FROM ?:products WHERE product_id IN (?n)',
            ['product_id', 'is_stock_split_by_warehouses'],
            $product_ids
        );

        foreach ($products as &$product) {
            $product['is_stock_split_by_warehouses'] = isset($is_stock_split_by_warehouses_map[$product['product_id']])
                ? $is_stock_split_by_warehouses_map[$product['product_id']]
                : YesNo::NO;
        }
        unset($product);

        return $products;
    }

    /**
     * Saves product stock data
     *
     * @param \Tygh\Addons\Warehouses\ProductStock $product_stock Product stock
     * @param bool                                 $remove_all    Remove all records before save
     *
     * @return bool
     */
    public function saveProductStock(ProductStock $product_stock, $remove_all = true)
    {
        $product_id = $product_stock->getProductId();

        if ($remove_all) {
            $this->removeProductStocks($product_id);
        }

        $warehouses_amounts = $product_stock->getStockAsArray();
        $empty_warehouse_ids = [];

        if (empty($warehouses_amounts)) {
            return false;
        }

        foreach ($product_stock->getWarehouses() as $product_warehouse) {
            if (!$product_warehouse->isMarkedToRemove()) {
                continue;
            }

            $warehouse_id = $product_warehouse->getWarehouseId();
            unset($warehouses_amounts[$warehouse_id]);
            $empty_warehouse_ids[$warehouse_id] = $warehouse_id;
        }

        if (!$remove_all && $empty_warehouse_ids) {
            $this->db->query(
                'DELETE FROM ?:warehouses_products_amount WHERE product_id = ?i AND warehouse_id IN (?n)',
                $product_id,
                $empty_warehouse_ids
            );
        }

        if ($warehouses_amounts) {
            $this->db->replaceInto('warehouses_products_amount', $warehouses_amounts, true);
        }

        $this->recalculateDestinationProductsStocksByProductIds([$product_id]);
        $this->saveTotalAmount($product_id, $product_stock);

        return true;
    }

    /**
     * Fetches product warehouses amounts
     *
     * @param int $product_id Product identifier
     *
     * @return array
     */
    protected function getProductWarehousesData($product_id)
    {
        $product_warehouses = $this->db->getArray(
            'SELECT warehouse_id, amount, store_type, position, main_destination_id, '
            .   ' pickup_destinations_ids, shipping_destinations_ids, status'
            . ' FROM ?:store_locations AS sl'
            . ' INNER JOIN ?:warehouses_products_amount AS wpa ON wpa.warehouse_id = sl.store_location_id'
            . ' WHERE wpa.product_id = ?i'
            . ' ORDER BY sl.position ASC',
            $product_id
        );

        $product_warehouses = $this->initializeDestinations($product_warehouses);

        return $product_warehouses;
    }

    protected function isProductStockSplitByWarehouses($product_id)
    {
        return YesNo::toBool($this->db->getField(
            'SELECT is_stock_split_by_warehouses FROM ?:products WHERE product_id = ?i',
            $product_id
        ));
    }

    protected function initializeDestinations(array $product_warehouses)
    {
        if (!$product_warehouses) {
            return [];
        }

        $destinations = $this->db->getMultiHash(
            'SELECT destination_links.*, shipping_delays.*'
            . ' FROM ?:store_location_destination_links AS destination_links'
            . ' LEFT JOIN ?:store_location_shipping_delays AS shipping_delays'
            . ' ON shipping_delays.store_location_id = destination_links.store_location_id'
            . ' AND shipping_delays.destination_id = destination_links.destination_id'
            . ' AND shipping_delays.lang_code = ?s'
            . ' WHERE destination_links.store_location_id IN (?n)',
            ['store_location_id', 'destination_id'],
            $this->language_code,
            array_column($product_warehouses, 'warehouse_id')
        );

        foreach ($product_warehouses as &$warehouse) {
            $warehouse['destinations'] = [];
            if (!empty($destinations[$warehouse['warehouse_id']])) {
                $warehouse['destinations'] = $destinations[$warehouse['warehouse_id']];
            }
        }
        unset($warehouse);

        return $product_warehouses;
    }

    /**
     * @param int $warehouse_id
     *
     * @return \Tygh\Addons\Warehouses\Destination[]
     */
    public function initializeDestinationsByWarehouseId($warehouse_id)
    {
        $warehouses = $this->initializeDestinations([['warehouse_id' => $warehouse_id]]);

        if (!$warehouses) {
            return [];
        }

        $warehouse = reset($warehouses);

        foreach ($warehouse['destinations'] as &$destination) {
            $destination = new Destination($destination);
        }
        unset($destination);

        return $warehouse['destinations'];
    }

    public function removeWarehouse($warehouse_id)
    {
        $this->db->query(
            'CREATE TEMPORARY TABLE _warehouse_affected_products'
            . ' (PRIMARY KEY product_id (product_id))'
            . ' ENGINE = MEMORY'
            . ' SELECT product_id FROM ?:warehouses_products_amount'
            . ' WHERE warehouse_id = ?i',
            $warehouse_id
        );

        $this->db->query('DELETE FROM ?:warehouses_products_amount WHERE ?w', [
            'warehouse_id' => $warehouse_id,
        ]);

        $this->db->query('DELETE FROM ?:store_location_destination_links WHERE ?w', [
            'store_location_id' => $warehouse_id,
        ]);

        $this->db->query('DELETE FROM ?:store_location_shipping_delays WHERE ?w', [
            'store_location_id' => $warehouse_id,
        ]);

        $this->recalculateDestinationProductsStocks([
            'by_temporary_table'     => '_warehouse_affected_products',
            'reset_stock_split_flag' => true,
        ]);

        $this->db->query('DROP TEMPORARY TABLE _warehouse_affected_products');

        /**
         * Executes after deleting warehouse data
         * Allows to delete related data
         *
         * @param int $warehouse_id Warehouse identifier
         */
        fn_set_hook('warehouses_manager_remove_warehouse', $warehouse_id);
    }

    public function recalculateDestinationProductsStocksByProductIds(array $product_ids)
    {
        $product_ids = array_filter($product_ids);

        if (!$product_ids) {
            return;
        }

        $this->recalculateDestinationProductsStocks([
            'product_ids'            => $product_ids,
            'reset_stock_split_flag' => true,
        ]);
    }

    public function recalculateDestinationProductsStocksByWarehouseIds(array $warehouse_ids)
    {
        $warehouse_ids = array_filter($warehouse_ids);

        if (!$warehouse_ids) {
            return;
        }

        $this->recalculateDestinationProductsStocks([
            'warehouse_ids' => $warehouse_ids,
        ]);
    }

    private function recalculateDestinationProductsStocks(array $params)
    {
        if (!empty($params['product_ids'])) {
            $product_condition = $this->db->quote('product_id IN (?n)', $params['product_ids']);
        } elseif (!empty($params['warehouse_ids'])) {
            $product_condition = $this->db->quote(
                'product_id IN (SELECT product_id FROM ?:warehouses_products_amount WHERE warehouse_id IN (?n) GROUP BY product_id)',
                $params['warehouse_ids']
            );
        } elseif (!empty($params['by_temporary_table'])) {
            $product_condition = $this->db->quote(
                'product_id IN (SELECT product_id FROM ?p)',
                $params['by_temporary_table']
            );
            $product_ids = $this->db->getColumn('SELECT product_id FROM ?p', $params['by_temporary_table']);
        }

        if (empty($product_condition)) {
            return false;
        }

        if (!empty($params['reset_stock_split_flag'])) {
            $this->db->query(
                'UPDATE ?:products SET is_stock_split_by_warehouses = ?s'
                . ' WHERE ?p AND is_stock_split_by_warehouses = ?s',
                YesNo::NO,
                $product_condition,
                YesNo::YES
            );
        }

        $this->db->query(
            'DELETE FROM ?:warehouses_destination_products_amount WHERE ?p',
            $product_condition
        );

        if ($this->is_mve) {
            $this->db->query(
                'INSERT INTO ?:warehouses_destination_products_amount (destination_id, storefront_id, product_id, amount)'
                . ' (SELECT destination_links.destination_id, 0 AS storefront_id, '
                .       ' warehouses_products_amount.product_id, SUM(warehouses_products_amount.amount) AS amount'
                . ' FROM ?:warehouses_products_amount AS warehouses_products_amount'
                . ' INNER JOIN ?:store_locations AS store_locations'
                .       ' ON store_locations.store_location_id = warehouses_products_amount.warehouse_id'
                . ' INNER JOIN ?:store_location_destination_links AS destination_links'
                .       ' ON destination_links.store_location_id = warehouses_products_amount.warehouse_id'
                . ' WHERE ?p AND store_locations.status = ?s'
                . ' GROUP BY destination_links.destination_id, warehouses_products_amount.product_id)',
                $product_condition,
                ObjectStatuses::ACTIVE
            );
        } else {
            $this->db->query(
                'INSERT INTO ?:warehouses_destination_products_amount (destination_id, storefront_id, product_id, amount)'
                . ' (SELECT destination_links.destination_id, storefronts_companies.storefront_id, '
                .       ' warehouses_products_amount.product_id, SUM(warehouses_products_amount.amount) AS amount'
                . ' FROM ?:warehouses_products_amount AS warehouses_products_amount'
                . ' INNER JOIN ?:store_locations AS store_locations'
                .       ' ON store_locations.store_location_id = warehouses_products_amount.warehouse_id'
                . ' INNER JOIN ?:store_location_destination_links AS destination_links'
                .       ' ON destination_links.store_location_id = warehouses_products_amount.warehouse_id'
                . ' INNER JOIN ?:ult_objects_sharing AS objects_sharing'
                .       ' ON objects_sharing.share_object_type = ?s'
                .           ' AND objects_sharing.share_object_id = warehouses_products_amount.warehouse_id'
                . ' INNER JOIN ?:storefronts_companies AS storefronts_companies'
                .       ' ON storefronts_companies.company_id = objects_sharing.share_company_id'
                . ' WHERE ?p AND store_locations.status = ?s'
                . ' GROUP BY destination_links.destination_id, storefronts_companies.storefront_id, '
                .   ' warehouses_products_amount.product_id)',
                'store_locations',
                $product_condition,
                ObjectStatuses::ACTIVE
            );
        }

        if (!empty($params['reset_stock_split_flag'])) {
            $this->db->query(
                'UPDATE ?:products SET is_stock_split_by_warehouses = ?s'
                . ' WHERE product_id IN '
                . ' (SELECT product_id FROM ?:warehouses_products_amount WHERE ?p GROUP BY product_id)',
                YesNo::YES,
                $product_condition
            );
        }
        if (isset($product_ids)) {
            foreach ($product_ids as $product_id) {
                $this->saveTotalAmount($product_id, $this->getProductWarehousesStock($product_id));
            }
        }

        return true;
    }

    /**
     * Removes product stocks info
     *
     * @param int $product_id Product ID
     */
    public function removeProductStocks($product_id)
    {
        $this->db->query(
            'UPDATE ?:products SET is_stock_split_by_warehouses = ?s'
            . ' WHERE product_id = ?i AND is_stock_split_by_warehouses = ?s',
            YesNo::NO,
            $product_id,
            YesNo::YES
        );

        $this->db->query('DELETE FROM ?:warehouses_products_amount WHERE product_id = ?i', $product_id);
        $this->db->query('DELETE FROM ?:warehouses_destination_products_amount WHERE product_id = ?i', $product_id);
    }

    /**
     * Saves total amount of specified product available in all warehouses combined.
     *
     * @param int                                  $product_id    Product identifier.
     * @param \Tygh\Addons\Warehouses\ProductStock $product_stock Product warehouse stock.
     *
     * @return bool
     *
     * @throws \Tygh\Exceptions\DatabaseException Exception at replace operation.
     */
    private function saveTotalAmount($product_id, ProductStock $product_stock)
    {
        if (fn_allowed_for('ULTIMATE')) {
            $data = [
                0 => [
                    'product_id'    => $product_id,
                    'amount'        => (int) $product_stock->getAmount(),
                    'storefront_id' => 0,
                ]
            ];
        } else {
            $data = [];
        }
        foreach ($product_stock->getWarehouses() as $warehouse) {
            if ($warehouse->isMarkedToRemove()) {
                continue;
            }
            $warehouse_id = $warehouse->getWarehouseId();
            $storefront_ids = $this->getStorefrontsByWarehouseId($warehouse_id);
            if (!$storefront_ids) {
                continue;
            }
            foreach ($storefront_ids as $storefront_id) {
                if (isset($data[$storefront_id])) {
                    $data[$storefront_id]['amount'] += $warehouse->getAmount();
                } else {
                    $data[$storefront_id] = [
                        'product_id'    => $product_id,
                        'amount'        => $warehouse->getAmount(),
                        'storefront_id' => $storefront_id
                    ];
                }
            }
        }
        if (!$data) {
            return (bool) $this->db->query(
                'DELETE FROM ?:warehouses_sum_products_amount WHERE product_id = ?i',
                $product_id
            );
        }
        foreach ($data as $chunk) {
            $this->db->replaceInto('warehouses_sum_products_amount', $chunk);
        }

        return true;
    }

    /**
     * Gets all storefronts on which specified warehouse shared.
     *
     * @param int $warehouse_id Warehouse identifier.
     *
     * @return int[]
     */
    private function getStorefrontsByWarehouseId($warehouse_id)
    {
        if ($this->is_mve) {
            return [0];
        }
        $company_ids = $this->db->getColumn(
            'SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = ?s AND share_object_id = ?i',
            'store_locations',
            $warehouse_id
        );
        if (empty($company_ids)) {
            return [];
        }
        $repository = StorefrontProvider::getRepository();
        $result = [];
        foreach ($company_ids as $company_id) {
            /** @var \Tygh\Storefront\Storefront $storefront */
            $storefront = $repository->findByCompanyId($company_id);
            if (!$storefront) {
                continue;
            }
            $result[] = $storefront->storefront_id;
        }
        return $result;
    }
}
