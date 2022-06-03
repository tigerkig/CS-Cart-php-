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

namespace Tygh\Addons\Stripe\HookHandlers;

use Tygh\Application;

class CheckoutHookHandler
{
    /**
     * @var \Tygh\Application
     */
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "checkout_place_orders_pre_route" hook handler.
     *
     * Actions performed:
     *  - Adds information about placed order into ajax response.
     *
     * @see \fn_checkout_place_order()
     */
    public function onPlaceOrderPreRoute($cart, $auth, $params)
    {
        if (defined('AJAX_REQUEST') && !empty($cart['processed_order_id'])) {
            $order_id = min($cart['processed_order_id']);
            /** @var \Tygh\Ajax $ajax */
            $ajax = $this->application['ajax'];
            $ajax->assign('order_id', $order_id);
        }
    }
}
