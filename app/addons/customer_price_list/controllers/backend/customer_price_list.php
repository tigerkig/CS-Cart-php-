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

use Tygh\Addons\CustomerPriceList\ServiceProvider;
use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @global string $mode
 * @global string $action
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!defined('CONSOLE')) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    if ($mode === 'runner') {
        $timeout = (int) Registry::ifGet('config.customer_price_list.generate_timeout', 360);

        $storefront_id = 0;
        if (!empty($_REQUEST['company_id'])) {
            $storefront_repository = StorefrontProvider::getRepository();
            /** @var \Tygh\Storefront\Storefront|null $storefront */
            $storefront = $storefront_repository->findByCompanyId($_REQUEST['company_id'], true);
            if ($storefront) {
                $storefront_id = (int) $storefront->storefront_id;
            }
        }

        $repository = ServiceProvider::getRepository();

        $queue = $repository->getQueue();

        foreach ($queue as $item) {
            if ($storefront_id && (int) $item['storefront_id'] !== $storefront_id) {
                continue;
            }
            fn_echo(__('customer_price_list.starting_generate_price_list', [
                '[storefront]' => $item['storefront'],
                '[usergroup]'  => $item['usergroup'],
            ]) . PHP_EOL);

            try {
                $process = ServiceProvider::createCustomerProcess(
                    [
                        '-p',
                        '--dispatch=customer_price_list.generate',
                        '--switch_storefront_id=' . $item['storefront_id'],
                        '--storefront_id=' . $item['storefront_id'],
                        '--usergroup_id=' . $item['usergroup_id'],
                    ]
                );

                $process->setTimeout($timeout);
                $process->run();

                if ($process->getExitCode() !== 0) {
                    fn_echo(__('customer_price_list.generate_price_list_error', [
                        '[storefront]' => $item['storefront'],
                        '[usergroup]'  => $item['usergroup'],
                        '[error]'      => $process->getErrorOutput(),
                    ]));
                }
            } catch (Exception $exception) {
                fn_echo(__('customer_price_list.generate_price_list_error', [
                    '[storefront]' => $item['storefront'],
                    '[usergroup]'  => $item['usergroup'],
                    '[error]'      => $exception->getMessage(),
                ]) . PHP_EOL);
            } catch (Throwable $exception) {
                fn_echo(__('customer_price_list.generate_price_list_error', [
                    '[storefront]' => $item['storefront'],
                    '[usergroup]'  => $item['usergroup'],
                    '[error]'      => $exception->getMessage(),
                ]));
            }

            fn_echo(__('customer_price_list.ending_generate_price_list', [
                '[storefront]' => $item['storefront'],
                '[usergroup]'  => $item['usergroup'],
            ]) . PHP_EOL);
        }

        return [CONTROLLER_STATUS_NO_CONTENT];
    }

    if ($mode === 'check') {
        $dir = fn_get_files_dir_path();
        $name = tempnam($dir, 'customer_price_list');

        if ($name === false) {
            throw new RuntimeException(sprintf('Directory %s not writable', $dir));
        }

        unlink($name);

        return [CONTROLLER_STATUS_NO_CONTENT];
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'check') {
    $process = ServiceProvider::createAdminProcess(['-p', '--dispatch=customer_price_list.check']);
    $process->setTimeout(5);
    $process->run();

    if ($process->getExitCode() !== 0) {
        fn_set_notification('E', __('error'), $process->getErrorOutput());
    } else {
        fn_set_notification('N', __('notice'), __('successful'));
    }

    return [CONTROLLER_STATUS_OK];
} elseif ($mode === 'get') {
    $usergroup_id = isset($_REQUEST['usergroup_id']) ? (int) $_REQUEST['usergroup_id'] : null;
    $storefront_id = isset($_REQUEST['storefront_id']) ? (int) $_REQUEST['storefront_id'] : null;

    if ($usergroup_id === null || $storefront_id === null) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $repository = ServiceProvider::getRepository();
    $service = ServiceProvider::getService();

    $price_list = $repository->findPriceList($storefront_id, [$usergroup_id], false);

    if (!$price_list) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    list($file_path, $file_name) = $service->getFile($price_list);

    fn_get_file($file_path, $file_name);
}
