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

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if (Registry::isExist('config.help_center.server_url')) {
    Tygh::$app['view']->assign('help_center_server_url', Registry::get('config.help_center.server_url'));
} elseif (defined('HELP_CENTER_SERVER_URL')) {
    Tygh::$app['view']->assign('help_center_server_url', HELP_CENTER_SERVER_URL);
}

return [CONTROLLER_STATUS_OK];
