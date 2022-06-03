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

use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

/** @var array $cart */
$cart = &Tygh::$app['session']['cart'];

/** @var array $auth */
/** @var string $mode */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($mode === 'customer_info') {
        $_REQUEST['next_step'] = isset($_REQUEST['next_step'])
            ? $_REQUEST['next_step']
            : 'step_two';

        return [CONTROLLER_STATUS_OK];
    }

    if ($mode === 'place_order' && !empty($_REQUEST['update_steps'])) {
        $_REQUEST['update_step'] = 'step_four';

        list($errors, $redirect_params) = fn_checkout_update_steps($cart, $auth, $_REQUEST);

        if (!$errors) {
            if (!empty($redirect_params['edit_step'])) {
                $display_steps = fn_checkout_get_display_steps();
                if ($redirect_params['edit_step'] == 'step_four' && !$display_steps['step_four']) {
                    $redirect_params['edit_step'] = 'step_three';
                }
                if ($redirect_params['edit_step'] == 'step_three' && !$display_steps['step_three']) {
                    $redirect_params['edit_step'] = 'step_two';
                }
            }

            return [CONTROLLER_STATUS_REDIRECT, 'checkout.checkout?' . http_build_query($redirect_params)];
        }
    }
}

$cart['keep_chosen_shipping'] = false;

return [CONTROLLER_STATUS_OK];
