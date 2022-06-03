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

namespace Tygh\Addons\Organizations\HookHandlers;

use Tygh\Addons\Organizations\Organization\Organization;
use Tygh\Addons\Organizations\Organization\OrganizationUser;
use Tygh\Addons\Organizations\Organization\OrganizationUserRepository;
use Tygh\Addons\Organizations\ServiceProvider;
use Tygh\Tygh;

/**
 * Class UserHookHandler responsible for "user" hook handlers
 *
 * @package Tygh\Addons\Organizations\HookHandlers
 */
class UserHookHandler
{
    /**
     * The "post_delete_user" hook handler.
     *
     * Actions performed:
     *  - Removes related row from organization_user table
     *
     * @see \fn_delete_user()
     */
    public static function onAfterUserDelete($user_id)
    {
        $organization_user_repository = ServiceProvider::getOrganizationUserRepository();
        $organization_repository = ServiceProvider::getOrganizationRepository();

        $organization_user = $organization_user_repository->findByUserId($user_id);

        if ($organization_user) {
            $organization_repository->deleteUser($organization_user);
        }
    }

    /**
     * The "get_users" hook handler.
     *
     * Actions performed:
     *  - Adds sorting/filtering by organization to the users list at administration area.
     *
     * @see \fn_get_users()
     */
    public static function onGetUsers($params, $fields, &$sortings, &$condition, &$join, $auth)
    {
        $sortings['organization'] = 'organization_users.organization_id';

        $has_filter_by_organization = !empty($params['organization_id']);
        $has_sorting_by_organization = isset($params['sort_by']) && $params['sort_by'] === 'organization';

        if ($has_filter_by_organization || $has_sorting_by_organization) {
            $join .= db_quote(sprintf(
                ' LEFT JOIN ?:%s AS organization_users ON organization_users.user_id = ?:users.user_id',
                OrganizationUserRepository::TABLE_NAME_ORGANIZATION_USER
            ));

            if ($has_filter_by_organization) {
                if (is_array($params['organization_id'])) {
                    $condition['organization_id'] = db_quote(
                        ' AND organization_users.organization_id IN (?n)',
                        $params['organization_id']
                    );
                } else {
                    $condition['organization_id'] = db_quote(
                        ' AND organization_users.organization_id = ?i',
                        $params['organization_id']
                    );
                }
            }
        }
    }

    /**
     * The "get_users_post" hook handler.
     *
     * Actions performed:
     *  - Injects organization information to user list.
     *
     * @see \fn_get_users()
     */
    public static function onAfterGetUsers(&$users, $params, $auth)
    {
        $user_ids = array_column($users, 'user_id');
        $organization_ids = [];

        $organization_users = ServiceProvider::getOrganizationUserRepository()->findByUserIds($user_ids);

        foreach ($organization_users as $organization_user) {
            $organization_ids[$organization_user->getOrganizationId()] = $organization_user->getOrganizationId();
        }

        $organizations = ServiceProvider::getOrganizationRepository()->findAllByIds($organization_ids);

        foreach ($users as &$user) {
            if (!isset($organization_users[$user['user_id']])) {
                continue;
            }
            $organization_user = $organization_users[$user['user_id']];
            $organization_user->setOrganization(isset($organizations[$organization_user->getOrganizationId()]) ? $organizations[$organization_user->getOrganizationId()] : null);

            $user['organization_user'] = $organization_users[$user['user_id']];
        }
        unset($user);
    }

    /**
     * The "get_user_info" hook handler.
     *
     * Actions performed:
     *  - Injects organization information to user data.
     *
     * @see \fn_get_user_info()
     */
    public static function onAfterGetUserInfo($user_id, $get_profile, $profile_id, &$user_data)
    {
        if (AREA !== 'A') {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if ($organization_user) {
            $organization = ServiceProvider::getOrganizationRepository()->findById($organization_user->getOrganizationId());
            $organization_user->setOrganization($organization);

            $user_data['organization_id'] = $organization_user->getOrganizationId();
            $user_data['organization_user'] = $organization_user;
        }
    }

    /**
     * The "update_profile" hook handler.
     *
     * Actions performed:
     *  - Createa new organization if organization data is represented in user data
     *  - Links user to organization
     *
     * @see \fn_update_user()
     */
    public static function onAfterUpdateProfile($action, $user_data, $current_user_data)
    {
        if ($action === 'add' && !empty($user_data['organization'])) {
            self::registerOrganization($user_data);
            return;
        }

        if (empty($user_data['new_organization_id'])) {
            return;
        }

        $organization_repository = ServiceProvider::getOrganizationRepository();
        $organization_user_repository = ServiceProvider::getOrganizationUserRepository();

        $organization_id = (int) $user_data['new_organization_id'];
        $organization_user = $organization_user_repository->findByUserId($user_data['user_id']);

        if ($organization_user && $organization_user->getOrganizationId() === $organization_id) {
            return;
        }

        $organization = $organization_repository->findById($organization_id);

        if (!$organization) {
            return;
        }

        if ($organization_user) {
            $organization_repository->deleteUser($organization_user);
        }

        $organization_repository->addUser(new OrganizationUser($organization_id, $user_data['user_id'], OrganizationUser::ROLE_REPRESENTATIVE));
    }

    /**
     * The "login_user_pre" hook handler.
     *
     * Actions performed:
     *  - Rejects user login if organization disabled.
     *
     * @see \fn_login_user()
     */
    public static function onBeforeLoginUser($user_id, $udata, $auth, &$condition)
    {
        $user_id = (int) $user_id;

        if (!$user_id) {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);

        if (!$organization_user) {
            return;
        }

        $organization = ServiceProvider::getOrganizationRepository()->findById($organization_user->getOrganizationId());

        if (!$organization) {
            return;
        }

        if (!$organization->isActive()) {
            // Disable login user
            $condition .= ' AND 1 = 2';
        }
    }

    /**
     * The "user_init" hook handler.
     *
     * Actions performed:
     *  - Saves organization information into user info.
     *
     * @see \fn_init_user()
     */
    public static function onAfterInitUser(&$auth, &$user_info)
    {
        if (empty($user_info['user_id'])) {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_info['user_id']);
        $organization = null;

        if ($organization_user) {
            $organization = ServiceProvider::getOrganizationRepository()->findById($organization_user->getOrganizationId());
        }

        if (!$organization) {
            $auth['organization_id'] = null;
        } else {
            $auth['organization_id'] = $organization_user->getOrganizationId();
            $auth['organization_user_role'] = $organization_user->getRole();

            $user_info['organization_user_role'] = $organization_user->getRole();
            $user_info['organization_name'] = $organization->getName();
            $user_info['is_organization_owner'] = $user_info['organization_user_role'] === OrganizationUser::ROLE_OWNER;

            $cart = &Tygh::$app['session']['cart'];
            $cart = ServiceProvider::actualizeCart($cart, $organization_user->getUserId(), $organization_user->getOrganizationId());
        }
    }

    /**
     * The "fill_auth" hook handler.
     *
     * Actions performed:
     *  - Saves organization information into user info.
     *
     * @see \fn_fill_auth()
     */
    public static function onAfterFillAuth(&$auth, $user_data, $area, $original_auth)
    {
        if (empty($user_data['user_id'])) {
            return;
        }

        $organization_user = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_data['user_id']);

        if (!$organization_user) {
            return;
        }

        $organization = ServiceProvider::getOrganizationRepository()->findById($organization_user->getOrganizationId());

        if ($organization) {
            $auth['organization_id'] = $organization_user->getOrganizationId();
        } else {
            $auth['organization_id'] = null;
        }
    }

    protected static function registerOrganization(array $user_data)
    {
        $organization_repository = ServiceProvider::getOrganizationRepository();

        $organization = Organization::createFromArray(array_merge($user_data['organization'], [
            'organization_id' => null,
            'status'        => Organization::STATUS_ACTIVE
        ]));

        $organization_repository->save($organization);
        $organization_repository->changeOwner(
            $organization,
            new OrganizationUser($organization->getOrganizationId(), $user_data['user_id'], OrganizationUser::ROLE_OWNER)
        );
    }
}