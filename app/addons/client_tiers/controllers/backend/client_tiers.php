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

use Tygh\Addons\ClientTiers\Enum\Calculation;
use Tygh\Addons\ClientTiers\ServiceProvider;
use Tygh\Enum\UserTypes;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'recalculate') {

        if (isset($_REQUEST['type'])) {
            $type = $_REQUEST['type'];
        } else {
            $type = Calculation::AUTO;
        }

        $manager = ServiceProvider::getTierManager();
        $logger = ServiceProvider::getTierLogger();

        $page = 1;
        $items_per_page = 1000;

        do {
            list($all_customers, $search) = fn_get_users(['user_type' => UserTypes::CUSTOMER, 'page' => $page], $auth, $items_per_page);
            if ($page != $search['page']) {
                break;
            }
            $page++;
            $operation_statuses = $manager->updateTier(array_column($all_customers, 'user_id'), $type);
            if (defined('CONSOLE')) {
                $logger->showNotifications($operation_statuses);
            } else {
                $logger->showLogs($operation_statuses);
            }
        } while (true);

        if (!defined('CONSOLE')) {
            fn_set_notification('N', __('notice'), __('client_tiers.all_been_recalculated'));
        }
    }
    return [CONTROLLER_STATUS_OK];
}