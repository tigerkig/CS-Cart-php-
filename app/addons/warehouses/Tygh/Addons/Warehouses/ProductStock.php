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

class ProductStock
{
    /** @var int Product identifier */
    protected $product_id;

    /** @var ProductWarehouse[] */
    protected $product_warehouses = [];

    /** @var bool */
    protected $is_stock_split_by_warehouses = false;

    public function __construct($product_id, array $warehouses_amounts, $is_stock_split_by_warehouses = null)
    {
        $this->product_id = (int) $product_id;
        $this->initializeAmounts($warehouses_amounts);
        $this->is_stock_split_by_warehouses = !empty($this->product_warehouses) || $is_stock_split_by_warehouses;
    }

    /**
     * Determines if product has its stock split between any warehouses (at least one)
     *
     * @return bool
     */
    public function hasStockSplitByWarehouses()
    {
        return $this->is_stock_split_by_warehouses;
    }

    /**
     * Fetches product identifier
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Fetches product overall amount
     *
     * @return bool|int
     */
    public function getAmount()
    {
        return $this->getProductAmount($this->product_warehouses);
    }

    /**
     * Fetches product amount that is available for provided destination
     *
     * @param int $destination_id Destination identifier
     *
     * @return bool|int
     */
    public function getAmountForDestination($destination_id)
    {
        $warehouses = $this->getWarehousesForShippingInDestination($destination_id);

        return $this->getProductAmount($warehouses);
    }

    /**
     * Fetches product amount that is available for provided warehouse
     *
     * @param int $warehouse_id Warehouse identifier
     *
     * @return bool|int
     */
    public function getAmountForWarehouse($warehouse_id)
    {
        $warehouse = $this->getWarehousesById($warehouse_id);

        return $this->getProductAmount($warehouse);
    }

    /**
     * Converts stock data to an array
     *
     * @return array
     */
    public function getStockAsArray()
    {
        $product_warehouses = [];

        /** @var \Tygh\Addons\Warehouses\ProductWarehouse $product_warehouse */
        foreach ($this->product_warehouses as $warehouse) {
            $product_warehouses[$warehouse->getWarehouseId()] = $warehouse->getStockAsArray();
        }

        return $product_warehouses;
    }

    /**
     * Sets amount of product for specified warehouse
     *
     * @param int $warehouse_id Warehouse identifier
     * @param int $amount       Amount of product
     * @return $this
     */
    public function setAmountForWarehouse($warehouse_id, $amount)
    {
        $warehouse = $this->getWarehousesById($warehouse_id);
        $warehouse = reset($warehouse);
        $warehouse->setAmount($amount);

        return $this;
    }

    /**
     * Increases product stock by provider amount
     *
     * @param int $amount Product amount
     *
     * @return $this
     */
    public function increaseStockByAmount($amount)
    {
        /** @var \Tygh\Addons\Warehouses\ProductWarehouse $first_warehouse */
        $first_warehouse = $this->product_warehouses[0];
        $new_amount = $first_warehouse->getAmount() + $amount;
        $first_warehouse->setAmount($new_amount);

        return $this;
    }

    /**
     * Reduces stock by provided amount
     *
     * @param int $amount Product amount
     *
     * @return $this
     */
    public function reduceStockByAmount($amount)
    {
        $this->reduceStock($amount, $this->product_warehouses);
        return $this;
    }

    /**
     * Reduces stock by provided amount from warehouses that available for provided destination
     *
     * @param int $amount         Product amount
     * @param int $destination_id Destination identifier
     *
     * @return $this
     */
    public function reduceStockByAmountForDestination($amount, $destination_id)
    {
        $warehouses = $this->getWarehousesForDestination($destination_id);
        $this->reduceStock($amount, $warehouses);

        return $this;
    }

    /**
     * Reduces stock by provided amount in warehouses that are ship to the selected store, starting with the one
     * that was selected as the pickup point.
     *
     * @param int $amount   Product amount
     * @param int $store_id Pickup store identifier
     *
     * @return $this
     */
    public function reduceStockByAmountForStore($amount, $store_id)
    {
        $pickup_store = $this->getWarehousesById($store_id);
        $pickup_store = reset($pickup_store);

        $alternative_warehouses = $this->getWarehousesThatShipToStore($pickup_store);
        $warehouses = array_merge([$pickup_store], $alternative_warehouses);

        $this->reduceStock($amount, $warehouses);

        return $this;
    }

    /**
     * Reduces product stock by provided amount
     *
     * @param int                                        $amount     Product amount
     * @param \Tygh\Addons\Warehouses\ProductWarehouse[] $warehouses Product warehouses data
     *
     * @return $this
     */
    protected function reduceStock($amount, $warehouses)
    {
        $amount_delta = $amount;
        foreach ($warehouses as $warehouse) {
            $warehouse_amount = (int) $warehouse->getAmount();

            if ($warehouse_amount <= 0) {
                $new_warehouse_amount = $warehouse_amount;
            } elseif ($warehouse_amount >= $amount_delta) {
                $new_warehouse_amount = $warehouse_amount - $amount_delta;
                $amount_delta = 0;
            } else {
                $new_warehouse_amount = 0;
                $amount_delta -= $warehouse_amount;
            }

            $warehouse->setAmount($new_warehouse_amount);
        }

        if ($amount_delta > 0) {
            $in_stock_warehouses = array_filter($warehouses, function($warehouse) {
                /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
                return $warehouse->getAmount() > 0;
            });

            if ($in_stock_warehouses) {
                return $this->reduceStock($amount_delta, $in_stock_warehouses);
            }

            $first_warehouse = reset($warehouses);
            $new_amount = $first_warehouse->getAmount() - $amount_delta;
            $first_warehouse->setAmount($new_amount);
        }

        return $this;
    }

    /**
     * Fetches product amount from warehouses
     *
     * @param array $warehouses Product warehouses data
     *
     * @return bool|int
     */
    protected function getProductAmount($warehouses)
    {
        if (!$this->hasStockSplitByWarehouses()) {
            return false;
        }

        $amount = 0;
        /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
        foreach ($warehouses as $warehouse) {
            $amount += $warehouse->getAmount();
        }

        return $amount;
    }

    /**
     * Filters out warehouses and stores that are not available for provided destination
     *
     * @param int $destination_id Destination identifier
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehousesForDestination($destination_id)
    {
        $warehouses = array_filter($this->getActiveWarehouses(), function ($warehouse) use ($destination_id) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
            return $warehouse->isAvailForPickupInDestination($destination_id)
                || $warehouse->isAvailForShippingInDestination($destination_id);
        });

        $warehouses = $this->sortByDestinationPosition($warehouses, $destination_id);

        return array_values($warehouses);
    }

    /**
     * Gets warehouses that are shown for the specifed destination.
     *
     * @param int $destination_id Destination identifier
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehousesForPickupInDestination($destination_id)
    {
        $warehouses = array_filter($this->getActiveWarehouses(), function ($warehouse) use ($destination_id) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
            return $warehouse->isAvailForPickupInDestination($destination_id);
        });

        $warehouses = $this->sortByDestinationPosition($warehouses, $destination_id);

        return array_values($warehouses);
    }

    /**
     * Gets warehouses that ship to the specifed destination.
     *
     * @param int $destination_id Destination identifier
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehousesForShippingInDestination($destination_id)
    {
        $warehouses = array_filter($this->getActiveWarehouses(), function ($warehouse) use ($destination_id) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
            return $warehouse->isAvailForShippingInDestination($destination_id);
        });

        $warehouses = $this->sortByDestinationPosition($warehouses, $destination_id);

        return array_values($warehouses);
    }

    /**
     * Gets warehouses that can ship a product to the specified store.
     *
     * @param \Tygh\Addons\Warehouses\ProductWarehouse $store
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehousesThatShipToStore(ProductWarehouse $store)
    {
        $warehouses = $this->getWarehousesForShippingInDestination($store->getMainDestinationId());
        $warehouses = array_filter($warehouses, function($warehouse) use ($store) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
            return $store->getWarehouseId() != $warehouse->getWarehouseId()
                && $warehouse->getAmount() > 0;
        });

        return $warehouses;
    }

    /**
     * Filters out warehouses that are not the selected pickup point.
     *
     * @param int[]|int $warehouse_ids Warehouse identifier
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehousesById($warehouse_ids)
    {
        $warehouse_ids = (array) $warehouse_ids;
        $warehouse = array_filter($this->product_warehouses, function ($warehouse) use ($warehouse_ids) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse */
            return in_array($warehouse->getWarehouseId(), $warehouse_ids);
        });

        return array_values($warehouse);
    }

    /**
     * Gets warehouses
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    public function getWarehouses()
    {
        return $this->product_warehouses;
    }

    /**
     * Initializes product warehouses amounts
     *
     * @param array $warehouses_amounts Product warehouses amount data
     *
     * @return $this
     */
    private function initializeAmounts(array $warehouses_amounts)
    {
        foreach ($warehouses_amounts as $warehouse) {
            $warehouse_data = [
                'amount'                   => $warehouse['amount'],
                'position'                 => $warehouse['position'],
                'product_id'               => $this->getProductId(),
                'store_type'               => $warehouse['store_type'],
                'warehouse_id'             => $warehouse['warehouse_id'],
                'main_destination_id'      => $warehouse['main_destination_id'],
                'pickup_destination_ids'   => $warehouse['pickup_destinations_ids'],
                'shipping_destination_ids' => $warehouse['shipping_destinations_ids'],
                'destinations'             => $this->initializeDestinations($warehouse['destinations']),
                'status'                   => $warehouse['status'],
            ];
            $this->product_warehouses[] = new ProductWarehouse($warehouse_data);
        }

        return $this;
    }

    /**
     * Reorders warehouses by their priority within a rate area.
     *
     * @param \Tygh\Addons\Warehouses\ProductWarehouse[] $warehouses     Rate area warehouses
     * @param int                                        $destination_id Rate area ID
     *
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    protected function sortByDestinationPosition(array $warehouses, $destination_id)
    {
        usort($warehouses, function($warehouse_1, $warehouse_2) use ($destination_id) {
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse_1 */
            /** @var \Tygh\Addons\Warehouses\ProductWarehouse $warehouse_2 */
            if ($warehouse_1->getPosition($destination_id) < $warehouse_2->getPosition($destination_id)) {
                return -1;
            }
            if ($warehouse_1->getPosition($destination_id) > $warehouse_2->getPosition($destination_id)) {
                return 1;
            }

            return 0;
        });

        return $warehouses;
    }

    /**
     * Creates a list of destination-specific settings for a warehouse.
     *
     * @param array $destinations
     *
     * @return array
     */
    protected function initializeDestinations(array $destinations)
    {
        $initialized_destinations = [];

        foreach ($destinations as $destination) {
            if (!$destination instanceof Destination) {
                $destination = new Destination($destination);
            }

            $initialized_destinations[$destination->getId()] = $destination;
        }

        return $initialized_destinations;
    }

    /**
     * @return \Tygh\Addons\Warehouses\ProductWarehouse[]
     */
    protected function getActiveWarehouses()
    {
        return array_filter($this->product_warehouses, function(ProductWarehouse $warehouse) {
            return $warehouse->isActive();
        });
    }
}
