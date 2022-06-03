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

use Tygh\Settings;
use Tygh\Addons\Gdpr\CookiesPolicyManager;
use Tygh\Addons\Gdpr\Service;

defined('BOOTSTRAP') or die('Access denied');

/**
 * Updates add-on settings
 *
 * @param int   $setting_id Setting id
 * @param array $settings   Settings
 */
function fn_gdpr_update_settings($setting_id, array $settings)
{
    $setting_id = (int) $setting_id;

    if ($setting_id) {
        $settings = json_encode((array) $settings);
        Settings::instance()->updateValueById($setting_id, $settings);
    }
}

/**
 * Fetches settings data
 *
 * @param string $name     Setting name
 * @param string $section  Setting section
 * @param array  $settings Settings
 *
 * @return mixed
 */
function fn_gdpr_get_setting_data($name, $section, $settings)
{
    return current(
        array_filter(
            isset($settings[$section]) ? $settings[$section] : array(),
            function($setting) use ($name) {
                return isset($setting['name']) && $setting['name'] == $name;
            }
        )
    );
}

/**
 * Proxies saving user agreement request to a specialized service
 *
 * @param array  $params         Parameters
 * @param string $agreement_type Type of agreement
 *
 * @return mixed
 */
function fn_gdpr_save_user_agreement($params, $agreement_type)
{
    /** @var Tygh\Addons\Gdpr\Service $service Gdpr service */
    $service = Tygh::$app['addons.gdpr.service'];

    if ($service->isNeeded($agreement_type)) {
        return $service->saveAcceptedAgreement($params, $agreement_type);
    }

    return false;
}

/**
 * Hook handler for saving accepted placing order agreement to the log
 */
function fn_gdpr_checkout_place_orders_pre_route($cart, $auth, $params)
{
    if (AREA !== 'C') {
        return false;
    }

    $params = array(
        'user_id' => isset($auth['user_id']) ? (int) $auth['user_id'] : 0,
        'email' => isset($cart['user_data']['email']) ? $cart['user_data']['email'] : '',
    );

    fn_gdpr_save_user_agreement($params, 'checkout_place_order');
}

/**
 * Hook handler for saving accepted update user profile agreement to the log
 */
function fn_gdpr_update_profile($action, $user_data, $current_user_data)
{
    if (AREA !== 'C') {
        return false;
    }

    $params = array(
        'user_id' => isset($user_data['user_id']) ? (int) $user_data['user_id'] : 0,
        'email' => isset($user_data['email']) ? $user_data['email'] : '',
    );

    $agreement_type = !empty($user_data['agreement_type']) ? $user_data['agreement_type'] : 'profiles_update';

    fn_gdpr_save_user_agreement($params, $agreement_type);
}

/**
 * Hook handler for saving accepted apply for vendor agreement to the log
 */
function fn_gdpr_update_company($company_data, $company_id, $lang_code, $action)
{
    if (AREA !== 'C') {
        return false;
    }

    $params = array(
        'user_id' => Tygh::$app['session']['auth']['user_id'],
        'email' => isset($company_data['email']) ? $company_data['email'] : '',
    );

    fn_gdpr_save_user_agreement($params, 'apply_for_vendor');
}

/**
 * Hook handler for saving accepted product subscription agreement
 */
function fn_gdpr_update_product_notifications_post($data, $subscribed, $deleted)
{
    if (AREA !== 'C') {
        return false;
    }

    $params = array(
        'email' => isset($data['email']) ? $data['email'] : '',
        'user_id' => Tygh::$app['session']['user_id'],
    );

    fn_gdpr_save_user_agreement($params, 'product_subscription');
}

/**
 * Hook handler for fetching user anonymization status
 */
function fn_gdpr_get_users($params, &$fields, $sortings, $condition, &$join, $auth)
{
    if (AREA === 'C') {
        return;
    }

    $join .= db_quote(' LEFT JOIN ?:gdpr_user_data ON ?:gdpr_user_data.user_id = ?:users.user_id');
    $fields['anonymized'] = '?:gdpr_user_data.anonymized';
}

/**
 * Hook handler for saving user agreement (profile editing) on guest checkout
 */
function fn_gdpr_save_cart_content_post($cart, $user_id, $type, $user_type)
{
    if (AREA !== 'C' || empty($cart['guest_checkout'])) {
        return;
    }

    $is_edit_profile_step = !empty($cart['edit_step']) && $cart['edit_step'] === 'step_two';

    if ($is_edit_profile_step) {
        $params = array(
            'email' => isset($cart['user_data']['email']) ? $cart['user_data']['email'] : '',
            'user_id' => 0,
        );

        fn_gdpr_save_user_agreement($params, 'checkout_profiles_update');
    }
}

/**
 * Hook handler for clean up agreements saved into the session when user logs out
 */
function fn_gdpr_user_logout_after($auth)
{
    if (AREA !== 'C') {
        return;
    }

    unset(Tygh::$app['session']['gdpr']);
}

/**
 * Hook handler for cleaning up cart data after user anonymizing
 */
function fn_gdpr_user_init($auth, $user_info, $first_init)
{
    if (AREA !== 'C') {
        return;
    }

    $cart_user_mismatch = empty($auth['user_id'])
        && !empty(Tygh::$app['session']['cart']['user_data']['user_id']);

    if (!$cart_user_mismatch) {
        return;
    }

    $is_anonymized = db_get_field('SELECT user_id FROM ?:gdpr_user_data WHERE user_id = ?i AND anonymized = ?s',
        Tygh::$app['session']['cart']['user_data']['user_id'],
        'Y'
    );

    if ($is_anonymized) {
        fn_clear_cart(Tygh::$app['session']['cart'], true, true);
    }
}

/**
 * Hook handler for saving accepted cookie agreement after user has authorised
 */
function fn_gdpr_login_user_post($user_id, $cu_id, $udata, $auth, $condition, $result)
{
    if (AREA !== 'C') {
        return;
    }

    if ($user_id) {
        /** @var CookiesPolicyManager $cookies_policy_manager */
        $cookies_policy_manager = Tygh::$app['addons.gdpr.cookies_policy_manager'];

        if ($cookies_policy_manager->hasStoredUserAgreement()) {
            /** @var Service $service GDPR service */
            $service = Tygh::$app['addons.gdpr.service'];
            $has_saved_agreement = $service->hasUserAgreement(CookiesPolicyManager::AGREEMENT_TYPE_COOKIES, $auth, false);

            if (!$has_saved_agreement) {
                $cookies_policy_manager->saveAgreement($user_id);
            }
        }
    }
}

/**
 * Hook handler for saving user consent or showing blocking page (if strict cookie policy enabled)
 */
function fn_gdpr_before_dispatch($controller, $mode, $action, $dispatch_extra, $area)
{
    if ($area != 'C') {
        return;
    }

    /** @var CookiesPolicyManager $cookies_policy_manager */
    $cookies_policy_manager = Tygh::$app['addons.gdpr.cookies_policy_manager'];

    $auth = Tygh::$app['session']['auth'];
    $has_user_agreement = $cookies_policy_manager->hasUserAgreement($auth);

    $cookies_accepted = isset($_REQUEST[CookiesPolicyManager::REQUEST_ACCEPTANCE_FLAG])
        && $_REQUEST[CookiesPolicyManager::REQUEST_ACCEPTANCE_FLAG] === 'Y';

    if ($cookies_accepted) {
        // user accepted cookies policy
        if (!$has_user_agreement) {
            $cookies_policy_manager->saveAgreement($auth['user_id']);
        }

    } elseif (!$has_user_agreement) {
        $cookies_policy_manager->applyCookiePolicy($_REQUEST);
    }
}