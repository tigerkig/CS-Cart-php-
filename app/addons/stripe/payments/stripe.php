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

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\Stripe\Payments\Stripe;

/** @var array $order_info */
/** @var array $processor_data */

if (!empty($order_info['payment_info']['stripe.payment_intent_id'])) {
    $processor = new Stripe(
        $order_info['payment_id'],
        Tygh::$app['db'],
        Tygh::$app['addons.stripe.price_formatter'],
        $processor_data['processor_params']
    );

    $pp_response = $processor->charge($order_info);
}
