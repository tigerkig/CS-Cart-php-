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


use Tygh\Addons\InstallerInterface;
use Tygh\Core\ApplicationInterface;
use Tygh\Settings;
use Tygh\Tygh;

/**
 * This class describes the instractions for installing and uninstalling the organizations add-on
 *
 * @package Tygh\Addons\ProductVariations
 */
class Installer implements InstallerInterface
{
    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self();
    }

    public function onBeforeInstall()
    {

    }

    public function onInstall()
    {
        /** @var \Tygh\Storefront\Repository $repository */
        $repository = Tygh::$app['storefront.repository'];

        list($storefronts) = $repository->find();
        $storefront_ids = array_keys($storefronts);

        Settings::instance()->updateValue('b2b_storefront_ids', $storefront_ids, 'organizations');
    }

    public function onUninstall()
    {

    }
}