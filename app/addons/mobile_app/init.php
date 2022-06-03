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

use Tygh\Addons\MobileApp\ServiceProvider;

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    /** @see \fn_mobile_app_change_order_status() */
    'change_order_status',
    /** @see \fn_mobile_app_delete_image_pre() */
    'delete_image_pre',
    /** @see \Tygh\Storefront\Repository::save() */
    'storefront_repository_save_post'
);
