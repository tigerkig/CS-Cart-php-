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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// max ammount of products in order to pass separate entries to paypal
fn_define('MAX_PAYPAL_PRODUCTS', 100);
// max product description length to pass to paypal
fn_define('MAX_PAYPAL_DESCR_LENGTH', 126);
// paypal's IPN identifier for refunded transactions
fn_define('PAYPAL_ORDER_STATUS_REFUNDED', 'Refunded');
// paypal's IPN identifier for completed transactions
fn_define('PAYPAL_ORDER_STATUS_COMPLETED', 'Completed');
// ingore partial refund policy identifier (see Order status on partial refund addon setting)
fn_define('PAYPAL_PARTIAL_REFUND_IGNORE', 'ignore');
// session key to temporary store currently configured payment when performing Integrated Sign Up
fn_define('PAYPAL_STORED_PAYMENT_ID_KEY', 'paypal_stored_payment_id');