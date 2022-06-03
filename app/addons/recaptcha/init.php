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

use Tygh\Application;
use Tygh\Registry;
use Tygh\Web\Antibot;

$addons_dir = Registry::get('config.dir.addons');
Tygh::$app['class_loader']->add('Gregwar\\', $addons_dir . '/recaptcha/lib');

Tygh::$app->extend('antibot', function(Antibot $antibot, Application $app) {
    $driver = fn_recaptcha_get_captcha_driver();
    if ($driver->isSetUp()) {
        $antibot->setDriver($driver);
    }

    return $antibot;
});