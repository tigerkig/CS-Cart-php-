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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'post_delete_user',
    'delete_company',
    'url_post'
);

Registry::get('class_loader')->add('Hybrid', Registry::get('config.dir.addons') . 'hybrid_auth/lib');
Registry::get('class_loader')->add('Facebook', Registry::get('config.dir.addons') . 'hybrid_auth/lib');
Registry::get('class_loader')->add('PayPal', Registry::get('config.dir.addons') . 'hybrid_auth/lib');
Registry::get('class_loader')->add('Psr', Registry::get('config.dir.addons') . 'hybrid_auth/lib');

require_once Registry::get('config.dir.addons') . 'hybrid_auth/lib/Facebook/polyfills.php';