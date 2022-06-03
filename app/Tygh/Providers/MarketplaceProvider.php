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

namespace Tygh\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Marketplace\Client;
use Tygh\Registry;
use Tygh\Tygh;

class MarketplaceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     *
     * @return void
     */
    public function register(Container $app)
    {
        $app['marketplace.client'] = static function (Container $application) {
            $license_number = Registry::get('settings.Upgrade_center.license_number');
            return new Client(Registry::get('config.resources.marketplace_url'), $license_number);
        };
    }

    /**
     * Gets CS-Cart Marketplace API Client.
     *
     * @return Client
     */
    public static function getClient()
    {
        return isset(Tygh::$app['marketplace.client'])
            ? Tygh::$app['marketplace.client']
            : new Client(
                Registry::get('config.resources.marketplace_url'),
                Registry::get('settings.Upgrade_center.license_number')
            );
    }
}
