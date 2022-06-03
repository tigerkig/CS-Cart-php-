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

namespace Tygh\Addons\Organizations\HookHandlers;


use Tygh\Addons\Organizations\ServiceProvider;
use Tygh\Addons\Organizations\Notifications\EventIdProviders\OrderProvider;
use Tygh\Enum\UserTypes;
use Tygh\Tygh;

/**
 * Class OrderHookHandler responsible for "order" and "cart" hook handlers
 *
 * @package Tygh\Addons\Organizations\HookHandlers
 */
class OrderHookHandler
{
    /**
     * The "pre_get_orders" hook handler.
     *
     * Actions performed:
     *  - Extends fields for retrieve organization ID
     *  - For customer area, allows viewing all organization orders to all organization users
     *
     * @see \fn_get_orders()
     */
    public static function onBeforeGetOrders(&$params, &$fields, $sortings, $get_totals, $lang_code)
    {
        $fields[] = '?:orders.organization_id';

        // At the customer area any organization-related user must see orders that belong to the organization, instead of his own orders.
        if (AREA == 'C' && !empty($params['user_id'])) {
            $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId((int) $params['user_id']);

            if ($organization_user) {
                $params['organization_id'] = $organization_user->getOrganizationId();
                unset($params['user_id']);
            }
        }
    }

    /**
     * The "get_orders" hook handler.
     *
     * Actions performed:
     *  - Implements filtering by organization ID
     *
     * @see \fn_get_orders()
     */
    public static function onGetOrders($params, $fields, $sortings, &$condition, $join, $group)
    {
        $organization_ids = isset($params['organization_id']) ? (array) $params['organization_id'] : [];
        $organization_ids = array_filter($organization_ids);

        if ($organization_ids) {
            $condition .= Tygh::$app['db']->quote(
                ' AND ?:orders.organization_id IN (?n)',
                $params['organization_id']
            );
        }
    }

    /**
     * The "get_orders_post" hook handler.
     *
     * Actions performed:
     *  - Loads and injects organizations into the orders list.
     *
     * @see \fn_get_orders()
     */
    public static function onAfterGetOrders($params, &$order_list)
    {
        // Ensure no empty or duplicated values are present
        $organization_ids = array_filter(array_unique(array_column($order_list, 'organization_id')));

        if (!$organization_ids) {
            return;
        }

        $organizations = ServiceProvider::getOrganizationRepository()->findAllByIds($organization_ids);

        foreach ($order_list as &$order) {
            if (!isset($order['organization_id'], $organizations[$order['organization_id']])) {
                continue;
            }

            $order['organization'] = $organizations[$order['organization_id']];
        }
        unset($order);
    }

    /**
     * The "is_order_allowed_post" hook handler.
     *
     * Actions performed:
     *  - Provides access to orders for organization users
     *
     * @see \fn_is_order_allowed()
     */
    public static function onAfterIsOrderAllowed($order_id, $auth, &$allowed)
    {
        if (AREA === 'C' && !$allowed && !empty($auth['organization_id'])) {
            $allowed = (bool) db_get_field(
                'SELECT order_id FROM ?:orders WHERE organization_id = ?i AND order_id = ?i',
                $auth['organization_id'], $order_id
            );
        }
    }

    /**
     * The "create_order" hook handler.
     *
     * Actions perfomed:
     *  - Injects organization ID to the order data
     *
     * @see \fn_update_order()
     */
    public static function onBeforeCreateOrder(&$order_data)
    {
        $order_data = self::fillOrderDataWithOrganizationIdByUserId($order_data);
    }

    /**
     * The "update_order" hook handler.
     *
     * Actions perfomed:
     *  - Injects organization ID to the order data
     *
     * @see \fn_update_order()
     */
    public static function onBeforeUpdateOrder(&$order_data, $order_id)
    {
        $user_id = db_get_field('SELECT user_id FROM ?:orders WHERE order_id = ?i', $order_id);

        if ($user_id) {
            $order_data['user_id'] = (int) $user_id;
            $order_data = self::fillOrderDataWithOrganizationIdByUserId($order_data);
        }
    }

    /**
     * The "save_cart_content_pre" hook handler.
     *
     * Actions perfomed:
     *  - Replaces the current user with the owner of the organization.
     *
     * @see \fn_save_cart_content()
     */
    public static function onBeforeSaveCartContent($cart, &$user_id, $type, $user_type)
    {
        if ($type !== 'C') {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if (!$organization_user || $organization_user->isOwner()) {
            return;
        }

        $organization_owner = ServiceProvider::getOrganizationUserRepository()->findOwnerByOrganizationId($organization_user->getOrganizationId());

        if ($organization_owner) {
            $user_id = $organization_owner->getUserId();
        }
    }

    /**
     * The "save_cart_content_before_save" hook handler.
     *
     * Actions perfomed:
     *  - Injects organization ID to the cart data
     *
     * @see \fn_save_cart_content()
     */
    public static function onBeforeSaveCartContentProductData($cart, $user_id, $type, $user_type, &$product_data)
    {
        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if (!$organization_user) {
            return;
        }

        $product_data['organization_id'] = $organization_user->getOrganizationId();
    }

    /**
     * The "pre_extract_cart" hook handler.
     *
     * Actions perfomed:
     *  - Injects organization ID to the cart data
     *
     * @see \fn_extract_cart_content()
     */
    public static function onBeforeExtractCartContent($cart, &$condition, $item_types, $user_id, $type, $user_type)
    {
        if (!$user_id || $type !== 'C') {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if (!$organization_user || $organization_user->isOwner()) {
            return;
        }

        $organization_owner = ServiceProvider::getOrganizationUserRepository()->findOwnerByOrganizationId($organization_user->getOrganizationId());

        if (!$organization_owner) {
            return;
        }

        // FIXME Dirty hack
        $condition = str_replace(
            db_quote('user_id = ?i', $user_id),
            db_quote('user_id = ?i', $organization_owner->getUserId()),
            $condition
        );
    }

    /**
     * The "change_order_status_post" hook handler.
     *
     * Actions perfomed:
     *  - Sends order notification for all organization users
     *
     * @see \fn_change_order_status()
     */
    public static function onAfterChangeOrderStatus($order_id, $status_to, $status_from, $force_notification, $place_order, $order_info, $edp_data)
    {
        if (empty($order_info['organization_id'])) {
            return;
        }

        if (!is_array($force_notification)) {
            $force_notification = [];
        }

        $receivers_schema = Tygh::$app['event.receivers_schema'];

        foreach ($receivers_schema as $reciever_type) {
            $force_notification[$reciever_type] = UserTypes::CUSTOMER === $reciever_type;
        }

        /** @var \Tygh\Notifications\EventDispatcher $event_dispatcher */
        $event_dispatcher = Tygh::$app['event.dispatcher'];

        /** @var \Tygh\Notifications\Settings\Factory $notification_settings_factory */
        $notification_settings_factory = Tygh::$app['event.notification_settings.factory'];
        $notification_rules = $notification_settings_factory->create($force_notification);

        if ($edp_data) {
            $edp_notification_rules = fn_get_edp_notification_rules($force_notification, $edp_data);
        }

        $user_id = (int) $order_info['user_id'];
        $status_id = strtolower($status_to);

        $organization_users = ServiceProvider::getOrganizationUserRepository()->findUsersByORganizationId($order_info['organization_id']);

        foreach ($organization_users as $organization_user) {
            if ($organization_user->getUserId() === $user_id) {
                continue;
            }

            $user_info = fn_get_user_short_info($organization_user->getUserId());

            if (!$user_info) {
                continue;
            }

            $order_info['user_id'] = $organization_user->getUserId();
            $order_info['email'] = $user_info['email'];
            $order_info['firstname'] = $user_info['firstname'];
            $order_info['lastname'] = $user_info['lastname'];

            $event_dispatcher->dispatch(
                "order.status_changed.{$status_id}",
                ['order_info' => $order_info],
                $notification_rules,
                new OrderProvider($order_info)
            );

            if ($edp_data) {
                $event_dispatcher->dispatch(
                    'order.edp',
                    ['order_info' => $order_info, 'edp_data' => $edp_data],
                    $edp_notification_rules,
                    new OrderProvider($order_info, $edp_data)
                );
            }
        }
    }

    /**
     * The "user_session_products_condition" hook handler.
     *
     * Actions perfomed:
     *  - Removes condition by session_id
     *  - Adds condition by organization_id
     *
     * @see \fn_user_session_products_condition()
     */
    public static function onBuildUserSessionProductsCondition($params, &$conditions)
    {
        if (empty($params['user_id'])) {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($params['user_id']);

        if (!$organization_user) {
            return;
        }

        unset($conditions['session_id']);
        $conditions['organization_id'] = db_quote('organization_id = ?i', $organization_user->getOrganizationId());
    }

    protected static function fillOrderDataWithOrganizationIdByUserId(array $order_data)
    {
        $user_id = empty($order_data['user_id']) ? null : (int) $order_data['user_id'];

        if (!$user_id) {
            return $order_data;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if (!$organization_user) {
            return $order_data;
        }

        $order_data['organization_id'] = $organization_user->getOrganizationId();

        return $order_data;
    }
}