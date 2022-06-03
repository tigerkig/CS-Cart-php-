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


namespace Tygh\Addons\Organizations\Notifications\EventIdProviders;

use Tygh\Notifications\EventIdProviders\OrderProvider as BaseOrderProvider;

/**
 * Class OrderProvider extends base OrderProvider class and used to send order notification for all users of organization.
 *
 * @package Tygh\Addons\Organizations\Notifications\EventIdProviders
 */
class OrderProvider extends BaseOrderProvider
{
    public function __construct(array $order, $edp_data = null)
    {
        parent::__construct($order, $edp_data);

        $this->id .= $order['user_id'];
    }
}