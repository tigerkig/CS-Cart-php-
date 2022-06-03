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

use Tygh\Enum\ProfileFieldSections;
use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

/** @var array $cart */
$cart = &Tygh::$app['session']['cart'];

/** @var array $auth */
$auth = &Tygh::$app['session']['auth'];

/** @var \Tygh\SmartyEngine\Core $view */
$view = Tygh::$app['view'];

if ($mode === 'checkout') {

    /** @var array $profile_fields */
    $profile_fields = $view->getTemplateVars('profile_fields');

    $display_steps = fn_checkout_get_display_steps($profile_fields);

    // Array notifying that one or another step is completed.
    $completed_steps = [
        'step_one'   => false,
        'step_two'   => false,
        'step_three' => false,
        'step_four'  => false,
    ];

    // Set edit step
    $recheck_edit_step = false;
    if (!empty($_REQUEST['edit_step']) && !empty($display_steps[$_REQUEST['edit_step']])) {
        $cart['edit_step'] = $_REQUEST['edit_step'];
    } elseif (empty($cart['edit_step'])) {
        $recheck_edit_step = true;
        $cart['edit_step'] = 'step_one';
        if (!$display_steps['step_one']) {
            $cart['edit_step'] = 'step_two';
        }
    } else {
        if ($cart['edit_step'] == 'step_one' && !$display_steps['step_one']) {
            $cart['edit_step'] = 'step_two';
        } elseif ($cart['edit_step'] == 'step_three' && !$display_steps['step_three']) {
            $cart['edit_step'] = 'step_four';
            if (!$display_steps['step_four']) {
                $cart['edit_step'] = 'step_two';
            }
        } elseif ($cart['edit_step'] == 'step_four' && !$display_steps['step_four']) {
            $cart['edit_step'] = 'step_three';
            if (!$display_steps['step_three']) {
                $cart['edit_step'] = 'step_two';
            }
        }
    }

    // Final step
    $final_step = 'step_four';
    if (!$display_steps['step_four']) {
        $final_step = 'step_three';
        if (!$display_steps['step_three']) {
            $final_step = 'step_two';
        }
    }

    unset(Tygh::$app['session']['failed_registration']);

    if (!empty($auth['user_id'])) {
        if (!empty($_REQUEST['profile_id'])) {
            fn_checkout_set_cart_profile_id($cart, $auth, $_REQUEST['profile_id']);
            fn_checkout_update_steps($cart, $auth, $_REQUEST);
        } elseif (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
            $cart['profile_id'] = 0;
        } else {
            fn_checkout_update_steps($cart, $auth, $_REQUEST);
        }
    } else {
        $user_data = fn_restore_post_data('user_data');
        if ($user_data) {
            Tygh::$app['session']['failed_registration'] = true;
        }
    }

    $billing_population = fn_check_profile_fields_population($cart['user_data'], ProfileFieldSections::BILLING_ADDRESS, $profile_fields);
    $shipping_population = fn_check_profile_fields_population($cart['user_data'], ProfileFieldSections::SHIPPING_ADDRESS, $profile_fields);
    $contact_info_population = fn_check_profile_fields_population($cart['user_data'], ProfileFieldSections::ESSENTIALS, $profile_fields);
    $contact_fields_filled = fn_check_profile_fields_population($cart['user_data'], ProfileFieldSections::CONTACT_INFORMATION, $profile_fields);

    if ($cart['edit_step'] != 'step_one' && (!$billing_population || !$shipping_population)) {
        $cart['edit_step'] = 'step_two';
    }

    $guest_checkout = !empty($cart['guest_checkout']) || !$display_steps['step_one'];

    // Check fields population on first and second steps
    if (($contact_info_population || $guest_checkout) && empty(Tygh::$app['session']['failed_registration'])) {
        if (!$contact_fields_filled) {
            $recheck_edit_step = false;
            if ($cart['edit_step'] != 'step_one') {
                fn_set_notification('W', __('notice'), __('text_fill_the_mandatory_fields'));

                return [CONTROLLER_STATUS_REDIRECT, "checkout.checkout?edit_step=step_one"];
            }
        }

        $completed_steps['step_one'] = true;

        if (($billing_population || empty($profile_fields[ProfileFieldSections::BILLING_ADDRESS])) && ($shipping_population || empty($profile_fields[ProfileFieldSections::SHIPPING_ADDRESS]))) {
            $completed_steps['step_two'] = true;
        }
    } elseif ($guest_checkout && !empty(Tygh::$app['session']['failed_registration'])) {
        $completed_steps['step_one'] = true;
    }

    $payment_methods = fn_prepare_checkout_payment_methods($cart, $auth, CART_LANGUAGE, true);

    // Edit step postprocessing
    if ($recheck_edit_step) {
        if ($cart['edit_step'] == 'step_one' && $completed_steps['step_one']) {
            $cart['edit_step'] = 'step_two';
        }
    }
    if ($cart['edit_step'] == 'step_two' && $completed_steps['step_two'] && empty($_REQUEST['from_step'])) {
        if ($display_steps['step_three']) {
            $cart['edit_step'] = 'step_three';
        } elseif ($display_steps['step_four']) {
            $cart['edit_step'] = 'step_four';
        }
    }

    // Backward compatibility
    Tygh::$app['session']['edit_step'] = $cart['edit_step'];
    // \Backward compatibility

    // Next step
    $next_step = !empty($_REQUEST['next_step']) ? $_REQUEST['next_step'] : '';
    if (empty($next_step)) {
        if (!empty($_REQUEST['from_step']) && in_array($cart['edit_step'], ['step_one', 'step_two'])) {
            $next_step = $_REQUEST['from_step'];
        } elseif ($cart['edit_step'] == 'step_one') {
            $next_step = 'step_two';
        } elseif ($cart['edit_step'] == 'step_two') {
            $next_step = 'step_three';
            if (fn_allowed_for('ULTIMATE') && !$display_steps['step_three']) {
                $next_step = 'step_four';
            }
        } elseif ($cart['edit_step'] == 'step_three') {
            $next_step = 'step_four';
        }
    }

    // if address step is completed, check if shipping step is completed
    if ($completed_steps['step_two']) {
        $completed_steps['step_three'] = true;
    }

    // If shipping step is completed, assume that payment step is completed too
    if ($completed_steps['step_three']) {
        $completed_steps['step_four'] = true;
    }

    if ((!empty($cart['shipping_failed']) || !empty($cart['company_shipping_failed'])) && $completed_steps['step_three']) {
        $completed_steps['step_four'] = false;

        if (defined('AJAX_REQUEST')) {
            fn_set_notification('W', __('warning'), __('text_no_shipping_methods'));
        }
    }

    if (fn_notification_exists('extra', 'shipping_rates_changed') && $cart['edit_step'] == 'step_four') {
        return [CONTROLLER_STATUS_REDIRECT, 'checkout.checkout?edit_step=step_three'];
    }

    fn_set_hook('checkout_select_default_payment_method', $cart, $payment_methods, $completed_steps, $auth);

    // Recalculate cart contents when the Shipping Options step is reached, but shipping wasn't calculated
    $needs_calculation = $cart['edit_step'] === 'step_three'
        || $cart['edit_step'] === 'step_four'
        || $completed_steps['step_two'];

    if ($needs_calculation && empty($cart['chosen_shipping'])) {
        fn_delete_notification('shipping_rates_changed');
        fn_calculate_cart_content($cart, $auth, 'A');
        Tygh::$app['session']['shipping_hash'] = fn_get_shipping_hash($cart['product_groups']);
    }

    $view->assign([
        'display_steps'           => $display_steps,
        'final_step'              => $final_step,
        'billing_population'      => $billing_population,
        'shipping_population'     => $shipping_population,
        'contact_info_population' => $contact_info_population,
        'contact_fields_filled'   => $contact_fields_filled,
        'payment_methods'         => $payment_methods,
        'completed_steps'         => $completed_steps,
        'next_step'               => $next_step,
        'cart'                    => $cart,
        'product_groups'          => $cart['product_groups'],
        'user_data'               => $cart['user_data'],
    ]);

    // payments form tab
    if (!empty($_REQUEST['active_tab'])) {
        $view->assign('active_tab', $_REQUEST['active_tab']);
    }

    // checkout customer handling action: register or log in
    if (!empty($_REQUEST['action'])) {
        $view->assign('checkout_type', $_REQUEST['action']);
    }
}

return [CONTROLLER_STATUS_OK];
