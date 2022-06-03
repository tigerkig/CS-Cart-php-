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

namespace Tygh\Addons\ClientTiers;

use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;
use Tygh\Core\ApplicationInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    public function getHookHandlerMap()
    {
        return [
            'get_usergroups'           => [
                'addons.client_tiers.hook_handlers.usergroups',
                'onGetUsergroups',
            ],
            'update_usergroup'         => [
                'addons.client_tiers.hook_handlers.usergroups',
                'onUpdateUsergroup',
            ],
            'change_order_status_post' => [
                'addons.client_tiers.hook_handlers.orders',
                'onCompletePurchase',
            ],
            'save_log'                 => [
                'addons.client_tiers.hook_handlers.log',
                'onSaveLog',
            ],
            'delete_usergroups'         => [
                'addons.client_tiers.hook_handlers.usergroups',
                'onDeleteUsergroup',
            ]
        ];
    }

}
