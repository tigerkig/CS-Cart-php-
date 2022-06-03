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

use Tygh\Addons\CustomerPriceList\Provider\CartCatalogProvider;
use Tygh\Addons\CustomerPriceList\ServiceProvider;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @global string $mode
 * @global string $action
 * @global array  $auth
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!defined('CONSOLE')) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    if ($mode === 'generate') {
        /**
         * @var \Tygh\Storefront\Storefront $storefront
         */
        $storefront = Tygh::$app['storefront'];
        $storefront_id = isset($_REQUEST['storefront_id']) ? (int) $_REQUEST['storefront_id'] : null;
        $usergroup_id = isset($_REQUEST['usergroup_id']) ? (int) $_REQUEST['usergroup_id'] : null;

        if ($usergroup_id === null || $storefront_id === null) {
            throw new RuntimeException('Storefront and usergroup must be defined');
        }

        if ($storefront_id !== (int) $storefront->storefront_id) {
            throw new RuntimeException('Storefront init broken');
        }

        $service = ServiceProvider::getService();

        if (!$service->generatePriceList($storefront_id, $usergroup_id)) {
            throw new RuntimeException('Price list was not generated');
        }

        return [CONTROLLER_STATUS_OK];
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'get') {
    $user_id = isset($auth['user_id']) ? (int) $auth['user_id'] : null;

    if (!$user_id) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    /**
     * @var \Tygh\Storefront\Storefront $storefront
     */
    $storefront = Tygh::$app['storefront'];
    $usergroup_ids = (array) $auth['usergroup_ids'];

    $repository = ServiceProvider::getRepository();
    $service = ServiceProvider::getService();

    $price_list = $repository->findPriceList($storefront->storefront_id, $usergroup_ids);

    if (!$price_list) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    list($file_path, $file_name) = $service->getFile($price_list);

    fn_get_file($file_path, $file_name);
} elseif ($mode === 'cart') {
    /** @var \Tygh\Web\Session $session*/
    $session = Tygh::$app['session'];
    /** @var \Tygh\Storefront\Storefront $storefront */
    $storefront = Tygh::$app['storefront'];
    $user_id = isset($auth['user_id']) ? (int) $auth['user_id'] : null;

    if (!$user_id) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $file_path = fn_create_temp_file();

    $generator = ServiceProvider::getGenerator();
    $provider = new CartCatalogProvider($session['cart']);

    try {
        unlink($file_path);
        $generator->generate($provider, $file_path);
    } catch (Exception $exception) {
        error_log($exception);
    } catch (Throwable $exception) {
        error_log($exception);
    }

    if (file_exists($file_path)) {
        $file_name = strtolower(sprintf('price_list_%s_cart.xlsx', $storefront->name));
        fn_get_file($file_path, $file_name, true);
    } else {
        fn_set_notification('E', __('error'), __('customer_price_list.price_list_generation_error'));
    }
}
