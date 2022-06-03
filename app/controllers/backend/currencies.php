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

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'currency_data'
    );

    //
    // Update currency
    //
    if ($mode == 'update') {
        $currency_id = fn_update_currency($_REQUEST['currency_data'], $_REQUEST['currency_id'], DESCR_SL);
        if (empty($currency_id)) {
            fn_delete_notification('changes_saved');
        }
    }

    if ($mode == 'delete') {

        if (!empty($_REQUEST['currency_id'])) {
            $currency_code = db_get_field("SELECT currency_code FROM ?:currencies WHERE currency_id = ?i", $_REQUEST['currency_id']);

            if ($currency_code != CART_PRIMARY_CURRENCY) {
                fn_delete_currency($_REQUEST['currency_id']);
                fn_set_notification('N', __('notice'), __('currency_deleted'));
            } else {
                fn_set_notification('W', __('warning'), __('base_currency_not_deleted'));
            }
        }
    }

    if ($mode == 'update_status') {
        if (fn_allowed_for('ULTIMATE:FREE')) {
            $currency = fn_get_currencies_list(array('currency_id' => $_REQUEST['id']), AREA, DESCR_SL);
            $currency = reset($currency);

            if ($currency['is_primary'] == 'Y' && $_REQUEST['status'] != 'A') {
                fn_set_notification('E', __('error'), __('default_currency_status'));

                return array(CONTROLLER_STATUS_REDIRECT, fn_url('currencies.manage'));
            } elseif ($_REQUEST['status'] != 'A') {
                fn_set_notification('E', __('error'), __('currency_hidden_status_free'));

                return array(CONTROLLER_STATUS_REDIRECT, fn_url('currencies.manage'));

            } else {
                $currency['is_primary'] = 'Y';

                fn_update_currency($currency, $_REQUEST['id'], DESCR_SL);
            }
        }

        fn_tools_update_status($_REQUEST);
    }

    return array(CONTROLLER_STATUS_OK, 'currencies.manage');
}

if ($mode == 'manage') {

    $currencies = fn_get_currencies_list(array(), AREA, DESCR_SL);

    Tygh::$app['view']->assign([
        'currencies_data'            => $currencies,
        'is_allow_update_currencies' => fn_check_permissions('currencies', 'update', 'admin', Http::POST),
    ]);

} elseif ($mode == 'update') {

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    if (!empty($_REQUEST['currency_id'])) {

        $currency = fn_get_currency_data($_REQUEST['currency_id']);

        if (fn_allowed_for('ULTIMATE')) {
            /** @var \Tygh\Storefront\Repository $repository */
            $repository = Tygh::$app['storefront.repository'];
            list($is_sharing_enabled, $is_shared) = $repository->getSharingDetails(['currency_ids' => $currency['currency_id']]);

            $view->assign([
                'is_sharing_enabled' => $is_sharing_enabled,
                'is_shared'          => $is_shared,
            ]);
        }


        $view->assign('currency', $currency);
    }
}
