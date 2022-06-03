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

use Tygh\Addons\Organizations\Enum\ProfileTypes;
use Tygh\Addons\Organizations\ServiceProvider;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Tools\SecurityHelper;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array $auth
 * @var array $mode
 */
$organization_id = isset($auth['organization_id']) ? (int) $auth['organization_id'] : 0;

if (!ServiceProvider::isStorefrontB2B() || !ServiceProvider::isOrganizationOwner($auth['user_id'], $organization_id)) {
    return [CONTROLLER_STATUS_NO_PAGE];
}

$organization_repository = ServiceProvider::getOrganizationRepository();
$organization = $organization_repository->findById($organization_id);

if (!$organization) {
    return [CONTROLLER_STATUS_NO_PAGE];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'update') {
        $user_data = isset($_REQUEST['user_data']) ? (array) $_REQUEST['user_data'] : [];
        $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
        $profile_id = empty($_REQUEST['profile_id']) ? 0 : (int) $_REQUEST['profile_id'];

        $organization_user_repository = ServiceProvider::getOrganizationUserRepository();

        if ($user_id) {
            $organization_user = $organization_user_repository->findByUserId($user_id);

            if (!$organization_user || $organization_user->getOrganizationId() !== $organization_id) {
                return [CONTROLLER_STATUS_NO_PAGE];
            }
        }

        $is_new_user = $user_id === 0;
        $is_valid_user_data = true;

        if (empty($user_data['email'])) {
            fn_set_notification('W', __('warning'), __('error_validator_required', array('[field]' => __('email'))));
            $is_valid_user_data = false;
        } elseif (!fn_validate_email($user_data['email'])) {
            fn_set_notification('W', __('error'), __('text_not_valid_email', array('[email]' => $_REQUEST['user_data']['email'])));
            $is_valid_user_data = false;
        }

        if (!$is_valid_user_data) {
            return [
                CONTROLLER_STATUS_REDIRECT,
                $is_new_user ? 'organization_users.add' : 'organization_users.update?user_id=' . $user_id
            ];
        }

        if ($is_new_user) {
            $user_data['new_organization_id'] = $organization_id;
            $user_data['password1'] = $user_data['password2'] = substr(SecurityHelper::generateRandomString(), 0, USER_PASSWORD_LENGTH);
        }

        $result = fn_update_user($user_id, $user_data, $auth, !empty($_REQUEST['ship_to_another']), true);

        if ($result) {
            list($user_id, $profile_id) = $result;

            if (isset($user_data['status'])) {
                db_query('UPDATE ?:users SET status = ?s WHERE user_id = ?i', $user_data['status'], $user_id);
            }

            if ($user_id && $is_new_user) {
                fn_recover_password_generate_key($user_data['email']);
            }
        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        return [
            CONTROLLER_STATUS_REDIRECT,
            $user_id ? 'organization_users.update?user_id=' . $user_id : 'organization_users.add'
        ];
    } elseif ($mode === 'delete') {
        $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

        if (!$user_id) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $organization_user_repository = ServiceProvider::getOrganizationUserRepository();
        $organization_user = $organization_user_repository->findByUserId($user_id);

        if (!$organization_user || $organization_user->getOrganizationId() !== $organization_id) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        fn_delete_user($user_id);

        return [CONTROLLER_STATUS_REDIRECT, 'organization_users.manage'];
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'update') {
    $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $profile_id = empty($_REQUEST['profile_id']) ? 0 : (int) $_REQUEST['profile_id'];

    if (!$user_id) {
        return [CONTROLLER_STATUS_REDIRECT, 'organization_users.manage'];
    }

    $organization_repository = ServiceProvider::getOrganizationRepository();
    $organization_user_repository = ServiceProvider::getOrganizationUserRepository();

    $organization_user = $organization_user_repository->findByUserId($user_id);

    if (!$organization_user || $organization_user->getOrganizationId() !== $organization_id) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] === 'new') {
        $user_data = fn_get_user_info($user_id, false);
    } else {
        $user_data = fn_get_user_info($user_id, true, $profile_id);
    }

    if (empty($user_data)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $restored_user_data = fn_restore_post_data('user_data');

    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    $profile_fields = fn_get_profile_fields();

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'profile_fields' => $profile_fields,
        'user_data'      => $user_data,
    ]);

    if (Registry::get('settings.General.user_multiple_profiles') == YesNo::YES) {
        $view->assign('user_profiles', fn_get_user_profiles($user_id));
    }

    fn_add_breadcrumb($organization->getName(), 'organizations.update');
    fn_add_breadcrumb(__('organizations.manage_users'), 'organization_users.manage');
    fn_add_breadcrumb(__('editing_profile'));
} elseif ($mode === 'add') {
    $user_data = [];
    $profile_fields = fn_get_profile_fields();
    $restored_user_data = fn_restore_post_data('user_data');

    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'profile_fields' => $profile_fields,
        'user_data'      => $user_data,
    ]);

    fn_add_breadcrumb($organization->getName(), 'organizations.update');
    fn_add_breadcrumb(__('organizations.manage_users'), 'organization_users.manage');
    fn_add_breadcrumb(__('new_user_profile'));
} elseif ($mode === 'manage') {
    $params = array_merge($_REQUEST, [
        'organization_id'    => $organization_id
    ]);

    list($users, $search) = fn_get_users($params, $auth, Registry::get('settings.Appearance.elements_per_page'));

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'users'  => $users,
        'search' => $search,
    ]);

    fn_add_breadcrumb($organization->getName(), 'organizations.update');
    fn_add_breadcrumb(__('organizations.manage_users'));
}
