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

namespace Tygh\Addons\Organizations;


use Tygh\Addons\Organizations\HookHandlers\OrderHookHandler;
use Tygh\Addons\Organizations\HookHandlers\TiersHookHandler;
use Tygh\Addons\Organizations\HookHandlers\UserHookHandler;
use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /**
     * @inheritDoc
     */
    public function getHookHandlerMap()
    {
        return [
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterUserDelete() */
            'post_delete_user' => [UserHookHandler::class, 'onAfterUserDelete'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onGetUsers() */
            'get_users'        => [UserHookHandler::class, 'onGetUsers'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterGetUsers() */
            'get_users_post'   => [UserHookHandler::class, 'onAfterGetUsers'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterGetUserInfo() */
            'get_user_info'    => [UserHookHandler::class, 'onAfterGetUserInfo'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterUpdateProfile() */
            'update_profile'   => [UserHookHandler::class, 'onAfterUpdateProfile'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onBeforeLoginUser() */
            'login_user_pre'   => [UserHookHandler::class, 'onBeforeLoginUser'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterInitUser() */
            'user_init'        => [UserHookHandler::class, 'onAfterInitUser'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\UserHookHandler::onAfterFillAuth() */
            'fill_auth'        => [UserHookHandler::class, 'onAfterFillAuth'],

            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeGetOrders() */
            'pre_get_orders'                    => [OrderHookHandler::class, 'onBeforeGetOrders'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onGetOrders() */
            'get_orders'                        => [OrderHookHandler::class, 'onGetOrders'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onAfterGetOrders() */
            'get_orders_post'                   => [OrderHookHandler::class, 'onAfterGetOrders'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onAfterIsOrderAllowed() */
            'is_order_allowed_post'             => [OrderHookHandler::class, 'onAfterIsOrderAllowed'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeCreateOrder() */
            'create_order'                      => [OrderHookHandler::class, 'onBeforeCreateOrder'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeUpdateOrder() */
            'update_order'                      => [OrderHookHandler::class, 'onBeforeUpdateOrder'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeSaveCartContent() */
            'save_cart_content_pre'             => [OrderHookHandler::class, 'onBeforeSaveCartContent'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeSaveCartContentProductData() */
            'save_cart_content_before_save'     => [OrderHookHandler::class, 'onBeforeSaveCartContentProductData'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBeforeExtractCartContent() */
            'pre_extract_cart'                  => [OrderHookHandler::class, 'onBeforeExtractCartContent'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onAfterChangeOrderStatus() */
            'change_order_status_post'          => [OrderHookHandler::class, 'onAfterChangeOrderStatus'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\OrderHookHandler::onBuildUserSessionProductsCondition() */
            'user_session_products_condition'   => [OrderHookHandler::class, 'onBuildUserSessionProductsCondition'],
            /** @see \Tygh\Addons\Organizations\HookHandlers\TiersHookHandler::onUpdateUserTier() */
            'tier_manager_update_tier_pre'      => [TiersHookHandler::class, 'onUpdateUserTier'],
        ];
    }
}