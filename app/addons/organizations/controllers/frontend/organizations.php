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

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array $auth
 * @var array $mode
 */
$organization_id = isset($auth['organization_id']) ? (int) $auth['organization_id'] : 0;

if (!ServiceProvider::isStorefrontB2B() || !ServiceProvider::isOrganizationOwner($auth['user_id'], $organization_id)) {
    return [CONTROLLER_STATUS_NO_PAGE];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'update') {
        $organization_data = isset($_REQUEST['organization_data']) ? (array) $_REQUEST['organization_data'] : null;
        $organization_repository = ServiceProvider::getOrganizationRepository();

        if (!$organization_data) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $organization = $organization_repository->findById($organization_id);

        if (!$organization) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $organization->merge($organization_data);
        $organization_repository->save($organization);

        fn_set_notification('N', __('notice'), __('text_changes_saved'));

        return [CONTROLLER_STATUS_REDIRECT, 'organizations.update'];
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'update') {
    $organization_repository = ServiceProvider::getOrganizationRepository();

    $organization = $organization_repository->findById($organization_id, [
        'load_fields_values' => true
    ]);

    if (!$organization) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign([
        'organization'     => $organization,
        'profile_fields' => fn_get_profile_fields('A', $auth, CART_LANGUAGE, [
            'profile_type'     => ProfileTypes::CODE_ORGANIZATION,
            'skip_email_field' => true
        ])
    ]);

    fn_add_breadcrumb(__('organizations.organization_details'));
}
