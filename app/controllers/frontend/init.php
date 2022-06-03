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

use Tygh\BlockManager\Location;
use Tygh\BlockManager\SchemesManager;
use Tygh\Development;
use Tygh\Enum\StorefrontStatuses;
use Tygh\Registry;
use Tygh\Enum\SiteArea;
use Tygh\Enum\NotificationSeverity;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $controller */
/** @var string $mode */
/** @var array $auth */

// phpcs:disable SlevomatCodingStandard.Arrays.DisallowImplicitArrayCreation.ImplicitArrayCreationUsed

/**
 * Act on behalf functionality
 */
if (!empty($_REQUEST['skey'])) {
    $session_data = fn_get_storage_data('session_' . $_REQUEST['skey'] . '_data');
    fn_set_storage_data('session_' . $_REQUEST['skey'] . '_data', '');

    if (!empty($session_data)) {
        /** @var \Tygh\Web\Session $session */
        $session = Tygh::$app['session'];
        $session->start();

        $session->fromArray(unserialize($session_data));
        $session->markSettingsAsRequiredToSave();

        if (!fn_cart_is_empty(Tygh::$app['session']['cart'])) {
            fn_calculate_cart_content(Tygh::$app['session']['cart'], Tygh::$app['session']['auth'], 'S', true, 'F', true);
            fn_save_cart_content(Tygh::$app['session']['cart'], Tygh::$app['session']['auth']['user_id']);
        }
    }

    return [CONTROLLER_STATUS_REDIRECT, fn_query_remove(REAL_URL, 'skey')];
}

if (Registry::get('runtime.customization_mode.live_editor')) {
    Tygh::$app['view']->assign('live_editor_objects', fn_get_schema('customization', 'live_editor_objects'));
}

$antibot_validation = fn_validate_controller_with_antibot($controller, $mode, $_SERVER['REQUEST_METHOD'], $_REQUEST);
if ($antibot_validation) {
    return $antibot_validation;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return;
}

// Check if store is closed

/** @var \Tygh\Storefront\Storefront $storefront */
$storefront = Tygh::$app['storefront'];
if ($storefront->status === StorefrontStatuses::CLOSED) {
    if (!empty($_REQUEST['store_access_key'])) {
        Tygh::$app['session']['store_access_key'] = $_GET['store_access_key'];
    }

    if (
        !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_controllers')
        && (empty(Tygh::$app['session']['store_access_key']) || Tygh::$app['session']['store_access_key'] !== $storefront->access_key)
    ) {
        if (defined('AJAX_REQUEST')) {
            fn_set_notification('E', __('notice'), __('text_store_closed'));
            exit;
        }

        Development::showStub();
    }
} elseif (
    empty($auth['user_id'])
    && $storefront->is_accessible_for_authorized_customers_only
    && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_controllers')
    && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_customer_controllers')
) {
    fn_set_notification('E', __('access_denied'), __('error_not_logged'));

    return [CONTROLLER_STATUS_REDIRECT, 'auth.login_form?return_url=' . urlencode(Registry::get('config.current_url'))];
} elseif (
    !empty($auth['user_id'])
    && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_controllers')
    && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_customer_controllers')
) {
    $auth['password_change_timestamp'] = !empty($auth['password_change_timestamp']) ? $auth['password_change_timestamp'] : 0;
    $time_diff = TIME - $auth['password_change_timestamp'];
    $expire = Registry::get('settings.Security.account_password_expiration_period') * SECONDS_IN_DAY;

    if ($expire && $time_diff >= $expire) {
        if (!fn_notification_exists('extra', 'password_expire')) {
            fn_set_notification(
                NotificationSeverity::ERROR,
                __('warning'),
                __('error_password_expired_change', ['[link]' => fn_url('profiles.update')]),
                'S',
                'password_expire'
            );
        }
    }
}

//gets some information for rendering admin panel links
if (fn_is_bottom_panel_available(Tygh::$app['session']['auth'])) {
    Tygh::$app['view']->assign('is_bottom_panel_available', true);
    Tygh::$app['view']->assign(fn_prepare_bottom_panel_data());
}

if (empty($_REQUEST['product_id']) && empty($_REQUEST['category_id'])) {
    unset(Tygh::$app['session']['current_category_id']);
}

$dispatch = $_REQUEST['dispatch'];
$dynamic_object = [];
if (!empty($_REQUEST['dynamic_object'])) {
    $dynamic_object = $_REQUEST['dynamic_object'];
}

$dynamic_object_scheme = SchemesManager::getDynamicObject($dispatch, AREA, $_REQUEST);
if (!empty($dynamic_object_scheme) && !empty($_REQUEST[$dynamic_object_scheme['key']])) {
    $dynamic_object['object_type'] = $dynamic_object_scheme['object_type'];
    $dynamic_object['object_id'] = $_REQUEST[$dynamic_object_scheme['key']];
    $dispatch = $dynamic_object_scheme['customer_dispatch'];
}

Tygh::$app['view']->assign('location_data', Location::instance()->get($dispatch, $dynamic_object, CART_LANGUAGE));
Tygh::$app['view']->assign('layout_data', Registry::get('runtime.layout'));
Tygh::$app['view']->assign('current_mode', fn_get_current_mode($_REQUEST));
Tygh::$app['view']->assign('hash_of_available_countries', fn_get_hash_of_available_countries());

// Init cart if not set
if (empty(Tygh::$app['session']['cart'])) {
    fn_clear_cart(Tygh::$app['session']['cart']);
}

if (!empty(Tygh::$app['session']['continue_url'])) {
    Tygh::$app['session']['continue_url'] = fn_url_remove_service_params(Tygh::$app['session']['continue_url']);
}

if (!empty(Tygh::$app['session']['auth']['user_id'])) {
    fn_extract_cart_content(Tygh::$app['session']['cart'], Tygh::$app['session']['auth']['user_id'], SiteArea::STOREFRONT);
}
