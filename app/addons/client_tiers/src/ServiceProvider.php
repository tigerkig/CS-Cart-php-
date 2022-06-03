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

use Tygh\Addons\ClientTiers\Classification\TierManagementLogger;
use Tygh\Addons\ClientTiers\Classification\TierManager;
use Tygh\Addons\ClientTiers\Classification\TierClassificationService;
use Tygh\Addons\ClientTiers\HookHandlers\LoggingHookHandler;
use Tygh\Addons\ClientTiers\HookHandlers\OrdersHookHandler;
use Tygh\Addons\ClientTiers\HookHandlers\UsergroupsHookHandler;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Tygh\Application;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tygh;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['addons.client_tiers.hook_handlers.usergroups'] = function (Application $app) {
            return new UsergroupsHookHandler($app);
        };

        $app['addons.client_tiers.hook_handlers.orders'] = function (Application $app) {
            $settings = Registry::get('addons.client_tiers');
            return new OrdersHookHandler($settings['upgrade_tier_option']);
        };

        $app['addons.client_tiers.tier.manager'] = function (Application $app) {
            $settings = Registry::get('addons.client_tiers');
            return new TierManager(
                $settings['tiers_reporting_period'],
                $settings['upgrade_tier_option'],
                $settings['automatic_downgrade'],
                ServiceProvider::getTierService());
        };

        $app['addons.client_tiers.tier.service'] = function (Application $app) {
            return new TierClassificationService($app['db']);
        };

        $app['addons.client_tiers.tier.logger'] = function (Application $app) {
            return new TierManagementLogger(ServiceProvider::getTierService());
        };

        $app['addons.client_tiers.hook_handlers.log'] = function (Application $app) {
            return new LoggingHookHandler($app);
        };
    }

    /**
     * @return \Tygh\Addons\ClientTiers\Classification\TierManager
     */
    public static function getTierManager()
    {
        return Tygh::$app['addons.client_tiers.tier.manager'];
    }

    /**
     * @return \Tygh\Addons\ClientTiers\Classification\TierClassificationService
     */
    public static function getTierService()
    {
        return Tygh::$app['addons.client_tiers.tier.service'];
    }

    /**
     * @return \Tygh\Addons\ClientTiers\Classification\TierManagementLogger
     */
    public static function getTierLogger()
    {
        return Tygh::$app['addons.client_tiers.tier.logger'];
    }
}