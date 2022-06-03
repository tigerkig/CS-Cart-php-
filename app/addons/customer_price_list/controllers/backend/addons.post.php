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

use Tygh\Addons\CustomerPriceList\ServiceProvider;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @global string $mode
 * @global string $action
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'update' && $_REQUEST['addon'] === 'customer_price_list') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    /** @var \Tygh\Storefront\Storefront $storefront */
    $storefront = Tygh::$app['storefront'];

    $params = [
        'sort_by' => 'storefront_and_priority'
    ];

    if (fn_allowed_for('ULTIMATE') && fn_get_runtime_company_id()) {
        $params['storefront_id'] = $storefront->storefront_id;
    }

    $repository = ServiceProvider::getRepository();

    $view->assign([
        'list' => $repository->getQueue($params)
    ]);
}
