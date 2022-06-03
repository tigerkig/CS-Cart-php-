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

use Tygh\Addons\Organizations\ServiceProvider;
use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

if (!ServiceProvider::isStorefrontB2B() || empty($auth['organization_id'])) {
    return [CONTROLLER_STATUS_OK];
}

// Cart is empty, create it
if (empty(Tygh::$app['session']['cart'])) {
    fn_clear_cart(Tygh::$app['session']['cart']);
}

/** @var array $cart */
$cart = &Tygh::$app['session']['cart'];
$cart = ServiceProvider::actualizeCart($cart, $auth['user_id'], $auth['organization_id']);