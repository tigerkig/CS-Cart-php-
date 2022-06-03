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

use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;
use Tygh\Enum\YesNo;
use Tygh\Storefront\Storefront;
use Tygh\Tygh;

/**
 * Class Bootstrap
 *
 * @package Tygh\Addons\CustomerPriceList
 */
class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheritDoc
     */
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
            'get_usergroups' => function ($params, $lang_code, &$field_list) {
                $field_list .= ', a.is_price_list_enabled, a.price_list_priority';
            },
            'delete_usergroups' => function ($usergroup_ids) {
                $service = ServiceProvider::getService();

                foreach ($usergroup_ids as $usergroup_id) {
                    $service->removePriceListByUsergroupId($usergroup_id);
                }
            },
            'update_usergroup' => function ($usergroup_data, $usergroup_id) {
                if (
                    empty($usergroup_data['is_price_list_enabled'])
                    || $usergroup_data['is_price_list_enabled'] === YesNo::YES
                ) {
                    return;
                }

                $service = ServiceProvider::getService();
                $service->removePriceListByUsergroupId($usergroup_id);
            },
            'storefront_repository_delete_post' => function (Storefront $storefront) {
                $service = ServiceProvider::getService();
                $service->removePriceListByStorefrontId($storefront->storefront_id);
            },

            'fill_auth' => function (&$auth, $user_data, $area) {
                if ($area !== 'C' || empty($auth['user_id'])) {
                    return;
                }

                /**
                 * @var \Tygh\Storefront\Storefront $storefront
                 */
                $storefront = Tygh::$app['storefront'];
                $repository = ServiceProvider::getRepository();

                $auth['is_price_list_exists'] = (bool) $repository->findPriceList(
                    $storefront->storefront_id,
                    $auth['usergroup_ids']
                );
            }
        ];
    }
}
