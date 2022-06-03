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

use Tygh\Addons\Organizations\Organization\Organization;
use Tygh\Addons\Organizations\Organization\OrganizationUser;
use Tygh\Addons\Organizations\Enum\ProfileTypes;
use Tygh\Enum\NotificationSeverity;
use Tygh\Registry;
use Tygh\Addons\Organizations\ServiceProvider;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'update') {
        $organization_data = isset($_REQUEST['organization_data']) ? (array) $_REQUEST['organization_data'] : null;
        $organization_id = isset($_REQUEST['organization_id']) ? (int) $_REQUEST['organization_id'] : 0;
        $organization_repository = ServiceProvider::getOrganizationRepository();

        if (!$organization_data) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        if ($organization_id) {
            $organization = $organization_repository->findById($organization_id, [
                'load_owner_user' => true
            ]);

            if (!$organization) {
                return [CONTROLLER_STATUS_NO_PAGE];
            }
        } else {
            $organization = new Organization();
        }

        $organization->merge($organization_data);
        $organization_repository->save($organization);

        if (isset($organization_data['owner']) && $organization->getOwnerUser()->getUserId() != $organization_data['owner']) {
            $organization_repository->changeOwner($organization, new OrganizationUser(
                $organization->getOrganizationId(),
                $organization_data['owner'],
                OrganizationUser::ROLE_OWNER
            ));
        }

        return [
            CONTROLLER_STATUS_REDIRECT,
            'organizations.update?organization_id=' . $organization->getOrganizationId(),
        ];
    } elseif ($mode === 'update_status') {
        $organization_ids = isset($_REQUEST['id']) ? (array) $_REQUEST['id'] : [];
        $organization_ids = isset($_REQUEST['organization_ids']) ? (array) $_REQUEST['organization_ids'] : $organization_ids;
        $status = isset($_REQUEST['status']) ? (string) $_REQUEST['status'] : null;

        $organization_repository = ServiceProvider::getOrganizationRepository();

        if (!$organization_ids || !$status) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $organizations = $organization_repository->findAllByIds($organization_ids);

        if (!$organizations) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        foreach ($organizations as $organization) {
            $organization->setStatus($status);
            $organization_repository->save($organization);
        }

        fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('status_changed'));

        return [CONTROLLER_STATUS_OK, 'organizations.manage'];
    } elseif ($mode === 'delete') {
        $organization_ids = isset($_REQUEST['organization_id']) ? (array) $_REQUEST['organization_id'] : [];
        $organization_ids = isset($_REQUEST['organization_ids']) ? (array) $_REQUEST['organization_ids'] : $organization_ids;

        $organization_repository = ServiceProvider::getOrganizationRepository();

        foreach ($organization_ids as $organization_id) {
            if (!$organization_id) {
                continue;
            }

            $organization_repository->deleteById($organization_id);
        }

        return [CONTROLLER_STATUS_OK, 'organizations.manage'];
    }

    return [CONTROLLER_STATUS_OK];
}
if ($mode === 'manage') {
    $conditions = isset($_REQUEST['conditions']) ? (array) $_REQUEST['conditions'] : [];
    $items_per_page = (int) (isset($_REQUEST['items_per_page']) ? $_REQUEST['items_per_page'] : Registry::get('settings.Appearance.admin_elements_per_page'));
    $current_page = (int) (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1);

    if (isset($_REQUEST['ids'])) {
        $conditions['organization_id'] = (array) $_REQUEST['ids'];
    }

    $conditions = array_filter($conditions);
    $current_page = max($current_page, 1);
    $offset = $items_per_page * ($current_page - 1);

    $organization_repository = ServiceProvider::getOrganizationRepository();

    $count = $organization_repository->count($conditions);
    $organizations = $organization_repository->findAll($conditions, [
        'limit'                => $items_per_page,
        'offset'               => $offset,
        'load_organization_user' => true
    ]);

    $search = [
        'page'           => $current_page,
        'total_items'    => $count,
        'items_per_page' => $items_per_page,
        'conditions'     => $conditions
    ];

    if ($action === 'picker' && fn_constant('AJAX_REQUEST', false)) {
        $ajax = Tygh::$app['ajax'];

        $objects = [];

        foreach ($organizations as $organization) {
            $objects[] = [
                'id'   => $organization->getOrganizationId(),
                'text' => $organization->getName()
            ];
        }

        $ajax->assign('objects', $objects);
        $ajax->assign('total_objects', $count);

        Registry::set('runtime.organizations', $organizations);

        return [CONTROLLER_STATUS_NO_CONTENT];
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'statuses'    => Organization::getStatuses(),
        'organizations' => $organizations,
        'search'      => $search
    ]);
} elseif ($mode === 'update' || $mode === 'add') {
    if ($mode === 'update') {
        $organization_id = isset($_REQUEST['organization_id']) ? (int) $_REQUEST['organization_id'] : 0;

        if (!$organization_id) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $organization_repository = ServiceProvider::getOrganizationRepository();

        $organization = $organization_repository->findById($organization_id, [
            'load_fields_values' => true,
            'load_owner_user'    => true
        ]);

        if (!$organization) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }
    } else {
        $organization = new Organization();
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'statuses'    => Organization::getStatuses(),
        'organization'  => $organization,
        'profile_fields' => fn_get_profile_fields('A', $auth, CART_LANGUAGE, [
            'profile_type'     => ProfileTypes::CODE_ORGANIZATION,
            'skip_email_field' => true
        ])
    ]);
}