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

use Tygh\Enum\YesNo;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

fn_register_hooks(
    'checkout_update_steps_pre',
    'checkout_update_steps_user_exists',
    'checkout_update_steps_before_update_user_data',
    'checkout_update_steps_shipping_changed',
    'checkout_get_user_profiles',
    /** @see \fn_step_by_step_checkout_checkout_is_terms_and_conditions_agreement_required_pre */
    'checkout_is_terms_and_conditions_agreement_required_pre'
);

// A set of flags that are used on checkout when determining shipping and taxes calculation
Registry::set('checkout.prefill_address', YesNo::NO, true);
Registry::set('checkout.estimate_shipping_when_none_selected', YesNo::NO, true);
