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

use Installer\App;

include 'app/Installer/App.php';
App::instance()->init($_REQUEST);

if (defined('CONSOLE') && file_exists('config.php')) {
    if (!defined('DEVELOPMENT')) {
        define('DEVELOPMENT', true);
    }

    $params = include 'config.php';
    $result = App::instance()->dispatch('setup.console', $params, true);
    $status_code = array_pop($result);
    exit($status_code);
} else {
    App::instance()->run($_REQUEST);
}
