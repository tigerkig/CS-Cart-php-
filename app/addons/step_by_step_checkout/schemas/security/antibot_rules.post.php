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


$schema['checkout']['customer_info'] = [
    'request_method'            => 'POST',
    'verification_scenario'     => 'checkout',
    'condition'                 => function ($request_data) {
        return \Tygh\Registry::get('settings.Checkout.disable_anonymous_checkout') != 'Y'
            && empty(Tygh::$app['session']['cart']['user_data']['email']);
    },
    'save_post_data'            => [
        'user_data',
    ],
    'rewrite_controller_status' => [
        CONTROLLER_STATUS_REDIRECT,
        'checkout.checkout?login_type=guest',
    ],
];

return $schema;