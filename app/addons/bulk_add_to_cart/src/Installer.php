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

namespace Tygh\Addons\BulkAddToCart;

use Tygh\Addons\InstallerInterface;
use Tygh\Core\ApplicationInterface;
use Tygh\Registry;
use Tygh\Settings;

/**
 * This class describes the instractions for installing and uninstalling the bulk_add_to_cart add-on
 *
 * @package Tygh\Addons\BulkAddToCart
 */
class Installer implements InstallerInterface
{
    const LIST_PRODUCTS_VIEW_TEMPLATE = 'short_list';

    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self();
    }


    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {

    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {
        if ($this->setCompactListView()) {
            fn_set_notification('W', __('warning'), __('bulk_add_to_cart.install_text'));
        } else {
            fn_set_notification('W', __('warning'), __('bulk_add_to_cart.short_list_warning_install'));
        }
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {

    }

    /**
     * Enabling the Compact list view.
     * 
     * @return string
     */
    protected function setCompactListView()
    {
        if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
            $companies = fn_get_all_companies_ids();

            foreach ($companies as $company_id) {
                // FIXME Dirty hack to gets actual templates for storefront
                Registry::set('runtime.company_id', $company_id);
                $products_view_templates = Settings::instance()->getVariants('Appearance', 'default_products_view_templates', 0);
                Registry::set('runtime.company_id', 0);

                if (empty($products_view_templates[self::LIST_PRODUCTS_VIEW_TEMPLATE])) {
                    return false;
                }

                Settings::instance()->updateValue(
                    'default_products_view_templates',
                    [self::LIST_PRODUCTS_VIEW_TEMPLATE],
                    'Appearance',
                    false,
                    $company_id
                );
                Settings::instance()->updateValue(
                    'default_products_view',
                    self::LIST_PRODUCTS_VIEW_TEMPLATE,
                    'Appearance',
                    false,
                    $company_id
                );
            }
        } else {
            Settings::instance()->updateValue(
                'default_products_view_templates',
                [self::LIST_PRODUCTS_VIEW_TEMPLATE],
                'Appearance'
            );
            Settings::instance()->updateValue(
                'default_products_view',
                self::LIST_PRODUCTS_VIEW_TEMPLATE,
                'Appearance'
            );
        }

        return true;
    }
}
