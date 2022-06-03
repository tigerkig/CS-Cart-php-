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

namespace Tygh\Addons\CustomerPriceList;

use Tygh\Database\Connection;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\StorefrontStatuses;
use Tygh\Enum\UsergroupTypes;
use Tygh\Enum\YesNo;

/**
 * Class Repository
 *
 * @package Tygh\Addons\CustomerPriceList
 */
class Repository
{
    /**
     * @var \Tygh\Database\Connection
     */
    protected $db_connection;

    /**
     * @var int
     */
    protected $usergroup_all_id = 0;

    /**
     * @var string
     */
    protected $usergroup_all_name = '';

    /**
     * Repository constructor.
     *
     * @param \Tygh\Database\Connection $db_connection
     * @param int                       $usergroup_all_id
     * @param string                    $usergroup_all_name
     */
    public function __construct(Connection $db_connection, $usergroup_all_id = 0, $usergroup_all_name = '')
    {
        $this->db_connection = $db_connection;
        $this->usergroup_all_id = (int) $usergroup_all_id;
        $this->usergroup_all_name = (string) $usergroup_all_name;
    }

    /**
     * Saves price list
     *
     * @param array<string, mixed> $price_list
     *
     * @return void
     */
    public function save(array $price_list)
    {
        $this->db_connection->replaceInto('customer_price_list', $price_list);
    }

    /**
     * Remove price list by usergroup ID
     *
     * @param int $usergroup_id
     */
    public function removeByUsergroupId($usergroup_id)
    {
        $this->db_connection->query(
            'DELETE FROM ?:customer_price_list WHERE usergroup_id = ?i',
            $usergroup_id
        );
    }

    /**
     * Remove price list by storefront ID
     *
     * @param int $storefront_id
     */
    public function removeByStorefrontId($storefront_id)
    {
        $this->db_connection->query(
            'DELETE FROM ?:customer_price_list WHERE storefront_id = ?i',
            $storefront_id
        );
    }

    /**
     * Gets files of price list
     *
     * @param array<string, mixed> $params
     *
     * @return array<int, array>
     */
    public function getList(array $params = [])
    {
        $conditions = [];

        if (!empty($params['storefront_id'])) {
            $conditions['storefront_id'] = $this->db_connection->quote(
                'customer_price_list.storefront_id = ?i',
                $params['storefront_id']
            );
        }

        if (!empty($params['usergroup_id'])) {
            $conditions['usergroup_id'] = $this->db_connection->quote(
                'customer_price_list.usergroup_id = ?i',
                $params['usergroup_id']
            );
        }

        if (!empty($params['usergroup_ids'])) {
            $conditions['usergroup_ids'] = $this->db_connection->quote(
                'customer_price_list.usergroup_id IN (?n)',
                $params['usergroup_ids']
            );
        }

        return $this->prepareRows($this->db_connection->getArray(
            'SELECT storefronts.storefront_id, storefronts.name AS storefront, usergroups.usergroup_id,'
            . '     usergroup_descriptions.usergroup, customer_price_list.file, customer_price_list.updated_at'
            . ' FROM ?:customer_price_list AS customer_price_list'
            . ' LEFT JOIN ?:storefronts AS storefronts ON storefronts.storefront_id = customer_price_list.storefront_id'
            . ' LEFT JOIN ?:usergroups AS usergroups ON usergroups.type = ?s'
            .  '    AND customer_price_list.usergroup_id = usergroups.usergroup_id'
            . ' LEFT JOIN ?:usergroup_descriptions AS usergroup_descriptions'
            . '     ON usergroup_descriptions.usergroup_id = usergroups.usergroup_id'
            . '     AND usergroup_descriptions.lang_code = ?s'
            . ' WHERE ?p ORDER BY ?p'
            . ' ?p',
            UsergroupTypes::TYPE_CUSTOMER,
            CART_LANGUAGE,
            implode(' AND ', $conditions),
            $this->buildOrderBy($params, 'storefront_and_priority'),
            isset($params['limit']) ? sprintf('LIMIT %d', $params['limit']) : ''
        ));
    }

    /**
     * Gets queue for generating price list
     *
     * @param array<string, mixed> $params
     *
     * @return array<int, array>
     */
    public function getQueue(array $params = [])
    {
        $conditions = $this->buildQueueConditions($params);

        $this->db_connection->query(
            'CREATE TEMPORARY TABLE _customer_price_list_usergroups'
            . ' SELECT usergroup_id, type, status, is_price_list_enabled, price_list_priority FROM ?:usergroups'
            . ' WHERE status = ?s AND type = ?s',
            ObjectStatuses::ACTIVE,
            UsergroupTypes::TYPE_CUSTOMER
        );
        $this->db_connection->query('INSERT INTO _customer_price_list_usergroups ?e', [
            'usergroup_id'          => $this->usergroup_all_id,
            'status'                => ObjectStatuses::ACTIVE,
            'type'                  => UsergroupTypes::TYPE_CUSTOMER,
            'is_price_list_enabled' => YesNo::YES,
            'price_list_priority'   => -100
        ]);

        $result = $this->prepareRows($this->db_connection->getArray(
            'SELECT storefronts.storefront_id, storefronts.name AS storefront, usergroups.usergroup_id,'
            . '     usergroup_descriptions.usergroup, customer_price_list.file, customer_price_list.updated_at'
            . ' FROM ?:storefronts AS storefronts'
            . ' LEFT JOIN _customer_price_list_usergroups AS usergroups ON usergroups.type = ?s'
            . ' LEFT JOIN ?:usergroup_descriptions AS usergroup_descriptions'
            .  '    ON usergroup_descriptions.usergroup_id = usergroups.usergroup_id'
            . '     AND usergroup_descriptions.lang_code = ?s'
            . ' LEFT JOIN ?:customer_price_list AS customer_price_list'
            . '     ON customer_price_list.storefront_id = storefronts.storefront_id'
            . '     AND customer_price_list.usergroup_id = usergroups.usergroup_id'
            . ' WHERE ?p ORDER BY ?p',
            UsergroupTypes::TYPE_CUSTOMER,
            CART_LANGUAGE,
            implode(' AND ', $conditions),
            $this->buildOrderBy($params, 'updated_at')
        ));

        $this->db_connection->query('DROP TEMPORARY TABLE _customer_price_list_usergroups');

        return $result;
    }

    /**
     * Find price list file path for given usergroups
     *
     * @param int   $storefront_id
     * @param int[] $usergroup_ids
     * @param bool  $include_usergroup_all
     *
     * @return array|null
     */
    public function findPriceList($storefront_id, array $usergroup_ids, $include_usergroup_all = true)
    {
        array_walk($usergroup_ids, function ($value) {
            return (int) $value;
        });

        if ($include_usergroup_all && !in_array($this->usergroup_all_id, $usergroup_ids, true)) {
            $usergroup_ids[] = $this->usergroup_all_id;
        }

        $list = $this->getList([
            'storefront_id' => $storefront_id,
            'usergroup_ids' => $usergroup_ids,
            'limit'         => 1
        ]);

        if ($list) {
            return reset($list);
        }

        return null;
    }

    /**
     * Prepares price list info
     *
     * @param array<int, array> $rows
     *
     * @return array<int, array>
     */
    protected function prepareRows(array $rows)
    {
        foreach ($rows as &$row) {
            $row['usergroup_id'] = (int) $row['usergroup_id'];
            $row['storefront_id'] = (int) $row['storefront_id'];

            if ($row['usergroup_id'] === $this->usergroup_all_id) {
                $row['usergroup'] = $this->usergroup_all_name;
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, string>
     */
    protected function buildQueueConditions(array $params)
    {
        $conditions = [
            'usergroup_is_price_list_enabled' => $this->db_connection->quote(
                'usergroups.is_price_list_enabled = ?s',
                YesNo::YES
            ),
            'usergroup_type'                  => $this->db_connection->quote(
                'usergroups.type = ?s',
                UsergroupTypes::TYPE_CUSTOMER
            ),
            'usergroup_status'                => $this->db_connection->quote(
                'usergroups.status = ?s',
                ObjectStatuses::ACTIVE
            ),
            'storefront_status'               => $this->db_connection->quote(
                'storefronts.status = ?s',
                StorefrontStatuses::OPEN
            ),
        ];

        if (!empty($params['storefront_id'])) {
            $conditions['storefront_id'] = $this->db_connection->quote(
                'storefronts.storefront_id = ?i',
                $params['storefront_id']
            );
        }

        if (!empty($params['usergroup_id'])) {
            $conditions['usergroup_id'] = $this->db_connection->quote(
                'usergroups.usergroup_id = ?i',
                $params['usergroup_id']
            );
        }

        if (!empty($params['usergroup_ids'])) {
            $conditions['usergroup_ids'] = $this->db_connection->quote(
                'usergroups.usergroup_id IN (?n)',
                $params['usergroup_ids']
            );
        }

        if (!empty($params['exists'])) {
            $conditions['file'] = 'customer_price_list.file IS NOT NULL';
        }

        return $conditions;
    }

    /**
     * @param array  $params
     * @param string $default
     *
     * @return string
     */
    protected function buildOrderBy(array $params, $default)
    {
        $sort_by = isset($params['sort_by']) ? (string) $params['sort_by'] : $default;

        switch ($sort_by) {
            case 'updated_at';
                return 'customer_price_list.updated_at IS NULL DESC, customer_price_list.updated_at ASC';
                break;
            case 'storefront_and_priority';
            default:
                return 'storefronts.storefront_id ASC, usergroups.price_list_priority DESC, usergroups.usergroup_id ASC';
                break;
        }
    }
}