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

use Tygh\Enum\SiteArea;
use Tygh\Registry;
use Tygh\Tools\Url;
use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 */

if ($mode === 'redirect') {

    $from_area = isset($_REQUEST['area']) ? (string) $_REQUEST['area'] : null;
    $from_url = isset($_REQUEST['url']) ? (string) $_REQUEST['url'] : null;
    $current_area = isset($_REQUEST['to_area']) ? (string) $_REQUEST['to_area'] : 'A';
    $current_account_type = AREA === 'A' ? ACCOUNT_TYPE : 'customer';
    $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : Tygh::$app['session']['auth']['user_id'];

    if (!$from_area || !$from_url) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $storefront_id = isset($_REQUEST['storefront_id']) ? $_REQUEST['storefront_id'] : null;

    $url = fn_bottom_panel_get_redirect_url($from_area, $from_url, $current_area, $current_account_type);

    if (empty($url)) {
        /** @psalm-suppress TypeDoesNotContainType */
        if (
            $current_account_type === 'vendor'
            && SiteArea::isAdmin($from_area)
            && SiteArea::isStorefront($current_area)
        ) {
            // vendor microstore url
            $url = $storefront_id
                ? 'companies.products?company_id=' . Registry::get(
                    'runtime.company_id'
                ) . '&storefront_id=' . $storefront_id
                : 'companies.products?company_id=' . Registry::get('runtime.company_id');
        } else {
            $url = $storefront_id
                ? 'index.index' . '?storefront_id=' . $storefront_id
                : 'index.index';
        }
    }

    if (SiteArea::isStorefront($current_area) && in_array($current_account_type, ['admin', 'vendor']) && !empty($user_id)) {
        $url = Url::buildUrn('profiles.act_as_user', [
            'user_id' => $user_id,
            'area' => SiteArea::STOREFRONT,
            'redirect_url' => $url,
        ]);
        $current_area = SiteArea::ADMIN_PANEL;
    } elseif (
        $from_area === SiteArea::STOREFRONT
        && $current_area === SiteArea::ADMIN_PANEL
        && $current_account_type === 'vendor'
        && !empty($user_id)
        && !empty($_REQUEST['ekey'])
    ) {
        $ekey = $_REQUEST['ekey'];

        $url = Url::buildUrn('auth.ekey_login', [
            'user_id' => $user_id,
            'area' => SiteArea::STOREFRONT,
            'redirect_url' => fn_url($url, $current_area),
            'ekey'  => $ekey
        ]);
    }

    return [CONTROLLER_STATUS_REDIRECT, fn_url($url, $current_area)];
}

/**
 * Get schema for chosen are in appropriate format
 *
 * @param string $from_area One-letter area type
 *
 * @return array
 */
function fn_bottom_panel_get_schema($from_area)
{
    $result = [];
    $schema = fn_get_schema('bottom_panel', $from_area === 'A' ? 'admin' : 'customer');

    foreach ($schema as $key => $item) {
        if (!isset($item['from'])) {
            continue;
        }

        if (!is_array($item['from'])) {
            $item['from'] = [
                'dispatch' => $item['from']
            ];
        }

        if (isset($item['to_admin']) && !is_array($item['to_admin']) && !is_callable($item['to_admin'])) {
            $item['to_admin'] = [
                'dispatch' => $item['to_admin']
            ];
        }

        if (isset($item['to_vendor']) && !is_array($item['to_vendor']) && !is_callable($item['to_vendor'])) {
            $item['to_vendor'] = [
                'dispatch' => $item['to_vendor']
            ];
        }

        $item_key = sprintf('%d_%s', count($item['from']), $key);
        $result[$item['from']['dispatch']][$item_key] = $item;
    }

    foreach ($result as $dispatch => &$rules) {
        krsort($rules, SORT_NATURAL);
    }
    unset($rules);

    return $result;
}

/**
 * Get redirect URL from one area to another
 *
 * @param string $from_area            One-letter area type which user come from
 * @param string $from_url             URL which user come from
 * @param string $current_area         One-letter area type which user is going to go
 * @param string $current_account_type Account type of current area
 *
 * @return bool|string URL for redirect, false otherwise
 */
function fn_bottom_panel_get_redirect_url($from_area, $from_url, $current_area, $current_account_type)
{
    $url = new Url($from_url);
    $dispatch = $url->getQueryParam('dispatch');

    if ($dispatch === null) {
        return false;
    }

    $schema = fn_bottom_panel_get_schema($from_area);

    if (!isset($schema[$dispatch])) {
        return false;
    }

    $to_keys = [];
    $to = null;

    if ($current_area === 'C') {
        $to_keys[] = 'to_customer';
    } else {
        $to_keys[] = sprintf('to_%s', strtolower($current_account_type));
        $to_keys[] = 'to_admin';
    }

    foreach ($schema[$dispatch] as $rule) {
        $to = null;

        foreach ($rule['from'] as $key => $value) {
            if (is_int($key) && $url->getQueryParam($value) === null
                || !is_int($key) && $url->getQueryParam($key) != $value
            ) {
                continue 2;
            }
        }

        foreach ($to_keys as $key) {
            if (isset($rule[$key])) {
                $to = $rule[$key];
                break;
            }
        }

        if ($to && is_callable($to)) {
            $to = call_user_func($to, $url);
        }

        if (!$to) {
            continue;
        }

        foreach ($to as $key => &$value) {
            if (is_callable($value)) {
                $value = call_user_func($value, $url);
            } elseif (strpos($value, '%') === 0) {
                $value = $url->getQueryParam(trim($value, '%'));
            }
        }
        unset($value);
        break;
    }

    if (!$to && $current_area === $from_area) {
        $to = $url->getQueryParams();
    }

    if (!$to) {
        return false;
    }

    list($dispatch, $controller, $mode) = fn_get_dispatch_routing($to);

    if ($current_area === 'A') {
        if (!fn_check_permissions($controller, $mode, 'admin', 'GET', $to)) {
            return false;
        }
    } elseif ($current_area === 'C') {
        $to['action'] = 'preview';
    }

    unset($to['dispatch']);

    return Url::buildUrn($dispatch, $to);
}