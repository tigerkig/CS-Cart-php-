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

use Tygh\BlockManager\Block;
use Tygh\BlockManager\Exim;
use Tygh\BlockManager\Layout;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\ProfileFieldSections;
use Tygh\Enum\ProfileTypes;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Settings;

defined('BOOTSTRAP') or die('Access denied');

function fn_step_by_step_checkout_checkout_update_steps_user_exists($cart, $auth, $params, &$redirect_params)
{
    $redirect_params['edit_step'] = $params['update_step'];
}

function fn_step_by_step_checkout_checkout_update_steps_pre($cart, $auth, &$params, &$redirect_params)
{
    $params['next_step'] = isset($params['next_step'])
        ? $params['next_step']
        : '';

    $params['update_step'] = isset($params['update_step'])
        ? $params['update_step']
        : '';

    if ($params['next_step']) {
        $redirect_params['edit_step'] = $params['next_step'];
    }
}

function fn_step_by_step_checkout_checkout_update_steps_before_update_user_data(&$cart, $auth, $params, $user_id, &$user_data)
{
    if (!$user_id) {
        return;
    }

    if (isset($user_data['profile_id'])) {
        $profile_id = $user_data['profile_id'];
    } elseif (!empty($cart['user_data']['profile_id'])) {
        $profile_id = $cart['user_data']['profile_id'];
    } elseif (!empty($cart['profile_id'])) {
        $profile_id = $cart['profile_id'];
    }

    $current_user_data = fn_get_user_info($user_id, true, $profile_id);

    $cart['user_data'] = fn_array_merge(
        $cart['user_data'],
        $current_user_data
    );

    if ($profile_id) {
        $cart['profile_id'] = $profile_id;
    }

    if ($user_data) {
        $user_data['user_id'] = $user_id;

        $user_data = fn_array_merge(
            $current_user_data,
            $user_data
        );

        $send_notification = isset($user_data['email']) && $user_data['email'] !== $current_user_data['email'];

        // Update user and profile fields
        $user_update_result = fn_update_user(
            $user_id,
            $user_data,
            $auth,
            $params['ship_to_another'],
            $send_notification
        );

        if ($user_update_result) {
            list(, $profile_id) = $user_update_result;

            $user_data['profile_id'] = $profile_id;
            $cart['user_data']['profile_id'] = $profile_id;
            $cart['profile_id'] = $profile_id;
        }
    }
}

function fn_step_by_step_checkout_checkout_update_steps_shipping_changed($cart, $auth, $params, &$redirect_params)
{
    if ($params['next_step'] === 'step_four') {
        $redirect_params['edit_step'] = 'step_three';
    }
}

/**
 * Proxies some Checkout section settings to add-on settings
 *
 * @return null|string
 */
function fn_step_by_step_checkout_checkout_settings_proxy()
{
    // For example, during the installation
    if (!isset(Tygh::$app['view'])) {
        return null;
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    $settings = Settings::instance();
    $proxied_section = $settings->getSectionByName('Checkout');
    $proxied_setting_objects = $settings->getList($proxied_section['section_id'], 0);

    $output = '';
    $settings_with_types = [
        'address_position'       => 'S',
        'agree_terms_conditions' => 'C',
        'configure_sign_in_step' => 'S',
        'sign_in_default_action' => 'S',
        'display_shipping_step'  => 'C',
        'display_payment_step'   => 'C',
    ];
    foreach ($proxied_setting_objects as $subsection_name => $setting_objects) {
        foreach ($setting_objects as $setting_object) {
            if (!array_key_exists($setting_object['name'], $settings_with_types)) {
                continue;
            }

            $setting_object['type'] = $settings_with_types[$setting_object['name']];

            $view->assign('item', $setting_object);
            $view->assign('section', $proxied_section['section_id']);
            $view->assign('html_name', "addon_data[options][{$setting_object['object_id']}]");
            $view->assign('class', 'setting-wide');
            $view->assign('html_id', "addon_option_step_by_step_checkout{$setting_object['name']}");

            $output .= $view->fetch('common/settings_fields.tpl');
        }
    }

    return $output;
}

/**
 * @return string Notification text displayed at the add-on settings.
 */
function fn_step_by_step_checkout_get_information()
{
    $is_backed_up = false;

    $files = [];
    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_available_company_ids();
        foreach ($company_ids as $company_id) {
            $files = array_merge(fn_get_dir_contents(Registry::get('config.dir.layouts') . $company_id, false, true), $files);
        }
    } else {
        $files = fn_get_dir_contents(Registry::get('config.dir.layouts'), false, true);
    }
    foreach ($files as $file) {
        if (strpos($file, 'layouts_checkout_') === 0) {
            $is_backed_up = true;
            break;
        }
    }

    Tygh::$app['view']->assign('is_backed_up', $is_backed_up);
    Tygh::$app['view']->assign('file_path', fn_get_rel_dir(Registry::get('config.dir.layouts')));
}

/**
 * Import location Checkout appropriate for Lite checkout during Disabling/Uninstalling add-on
 */
function fn_step_by_step_checkout_import_lite_checkout_layout()
{
    if (Registry::get('addons.step_by_step_checkout.status') == 'D') {
        return;
    }

    $export_filename = sprintf('layouts_checkout_%s.xml', date('mdY_Hms', TIME));
    $import_filename = sprintf('layouts_lite_checkout_%s.xml', fn_allowed_for('ULTIMATE') ? 'ultimate' : 'multivendor');
    $layout_path = sprintf('%s/step_by_step_checkout/layouts/%s', Registry::get('config.dir.addons'), $import_filename);

    $exported_file_paths = [];
    $locations = [];

    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_available_company_ids();

        foreach ($company_ids as $company_id) {
            $layout = Layout::instance($company_id)->getDefault();

            if (empty($layout)) {
                continue;
            }

            $checkout_location_id = fn_step_by_step_checkout_find_checkout_location_id($layout['layout_id']);

            if (empty($checkout_location_id)) {
                continue;
            }

            $locations[] = [
                'layout_id'   => $layout['layout_id'],
                'location_id' => $checkout_location_id,
                'company_id'  => $company_id
            ];
        }
    } else {
        $layout = Layout::instance()->getDefault();
        $checkout_location_id = fn_step_by_step_checkout_find_checkout_location_id($layout['layout_id']);


        if ($layout && $checkout_location_id) {
            $locations[] = [
                'layout_id'   => $layout['layout_id'],
                'location_id' => $checkout_location_id,
                'company_id'  => 0
            ];
        }
    }

    if ($locations) {
        foreach ($locations as $location) {
            $filename = Exim::instance($location['company_id'])->exportToFile(
                $location['layout_id'],
                [$location['location_id']],
                $export_filename
            );

            if (!$filename) {
                continue;
            }

            Exim::instance($location['company_id'], $location['layout_id'])->importFromFile($layout_path);

            $new_location_id = fn_step_by_step_checkout_find_checkout_location_id($location['layout_id']);

            if (empty($new_location_id)) {
                continue;
            }

            if ($location['company_id']) {
                $exported_file_paths[] = fn_get_rel_dir(sprintf('%s/%s/%s',
                    rtrim(Registry::get('config.dir.layouts'), '/'),
                    $location['company_id'],
                    $filename
                ));
            } else {
                $exported_file_paths[] = fn_get_rel_dir(sprintf('%s/%s',
                    rtrim(Registry::get('config.dir.layouts'), '/'),
                    $filename
                ));
            }
            fn_step_by_step_checkout_update_checkout_blocks($location['company_id'], $new_location_id);
        }
    }

    if (!empty($exported_file_paths)) {
        fn_set_notification('W', __('warning'), __('step_by_step_checkout.layout_page_was_backed_up', [
            '[file_path]' => implode(', ', $exported_file_paths)
        ]));
    }
}

/**
 * Restores address_position setting to default value
 */
function fn_step_by_step_checkout_restore_checkout_address_position()
{
    $company_ids = [null];
    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = array_merge($company_ids, fn_get_all_companies_ids());
    }

    foreach ($company_ids as $company_id) {
        Settings::instance($company_id)->updateValue('address_position', 'shipping_first', 'Checkout');
    }
}

/**
 * After status changing action show notification/import location depends on add-on status
 *
 * @param $status Add-on status
 */
function fn_settings_actions_addons_post_step_by_step_checkout($status)
{
    if ($status === ObjectStatuses::ACTIVE) {
        fn_set_notification(
            NotificationSeverity::WARNING,
            __('warning'),
            __(
                'step_by_step_checkout.layout_can_be_setup',
                [
                    '[href]' => fn_url('addons.update?addon=step_by_step_checkout'),
                ]
            )
        );

        $active_email_section = Registry::get('settings.Checkout.address_position') == 'billing_first'
            ? ProfileFieldSections::BILLING_ADDRESS
            : ProfileFieldSections::SHIPPING_ADDRESS;

        $disable_email_section = $active_email_section === ProfileFieldSections::BILLING_ADDRESS
            ? ProfileFieldSections::SHIPPING_ADDRESS
            : ProfileFieldSections::BILLING_ADDRESS;

        if (fn_step_by_step_checkout_has_billing_and_shipping_email_profile_field()) {
            db_query(
                'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
                . ' WHERE field_name = ?s AND section = ?s AND profile_type = ?s',
                'Y', 'Y', 'Y', 'Y', 'email', $active_email_section, ProfileTypes::CODE_USER
            );

            db_query(
                'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
                . ' WHERE field_name = ?s AND section = ?s AND profile_type = ?s',
                'N', 'Y', 'N', 'Y', 'email', $disable_email_section, ProfileTypes::CODE_USER
            );

            db_query(
                'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
                . ' WHERE field_name = ?s AND section = ?s AND profile_type = ?s',
                'N', 'N', 'N', 'N', 'email', ProfileFieldSections::CONTACT_INFORMATION,
                ProfileTypes::CODE_USER
            );
        } else {
            db_query(
                'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
                . ' WHERE field_name = ?s AND section = ?s AND profile_type = ?s',
                'Y', 'Y', 'Y', 'Y', 'email', ProfileFieldSections::CONTACT_INFORMATION,
                ProfileTypes::CODE_USER
            );
        }
    } else {
        fn_step_by_step_checkout_import_lite_checkout_layout();

        db_query(
            'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
            . ' WHERE field_name = ?s AND section = ?s AND profile_type = ?s',
            'Y', 'Y', 'Y', 'Y', 'email', ProfileFieldSections::CONTACT_INFORMATION,
            ProfileTypes::CODE_USER
        );

        db_query(
            'UPDATE ?:profile_fields SET checkout_show = ?s, checkout_required = ?s, profile_show = ?s, profile_required = ?s'
            . ' WHERE field_name = ?s AND section IN (?a) AND profile_type = ?s',
            'N', 'N', 'N', 'N', 'email', [ProfileFieldSections::SHIPPING_ADDRESS, ProfileFieldSections::BILLING_ADDRESS],
            ProfileTypes::CODE_USER
        );

        fn_step_by_step_checkout_restore_checkout_address_position();
    }
}

/**
 * Change checkout blocks content according to profile fields ids in store
 *
 * @param int $company_id           Company identifier
 * @param int $checkout_location_id Location identifier in company
 */
function fn_step_by_step_checkout_update_checkout_blocks($company_id, $checkout_location_id)
{
    static $block_types_profile_fields_map;

    if ($block_types_profile_fields_map === null) {
        $profile_fields = db_get_hash_multi_array(
            'SELECT field_id, field_name, section FROM ?:profile_fields WHERE profile_type = ?s',
            ['section', 'field_name', 'field_id'], 'U'
        );

        $block_types_profile_fields_map = [
            'lite_checkout_customer_information' => [
                isset($profile_fields['C']['firstname']) ? $profile_fields['C']['firstname'] : null,
                isset($profile_fields['C']['lastname']) ? $profile_fields['C']['lastname'] : null,
                isset($profile_fields['C']['phone']) ? $profile_fields['C']['phone'] : null,
                isset($profile_fields['C']['email']) ? $profile_fields['C']['email'] : null,
            ],
            'lite_checkout_customer_address' => [
                isset($profile_fields['S']['s_address']) ? $profile_fields['S']['s_address'] : null,
                isset($profile_fields['S']['s_zipcode']) ? $profile_fields['S']['s_zipcode'] : null,
            ],
            'lite_checkout_location' => [
                isset($profile_fields['S']['s_city']) ? $profile_fields['S']['s_city'] : null,
                isset($profile_fields['S']['s_state']) ? $profile_fields['S']['s_state'] : null,
                isset($profile_fields['S']['s_country']) ? $profile_fields['S']['s_country'] : null,
            ],
        ];
    }

    foreach ($block_types_profile_fields_map as $block_type => $profile_field_ids) {
        $profile_field_ids = array_filter($profile_field_ids);

        if (empty($profile_field_ids)) {
            continue;
        }

        $snappings = Block::instance($company_id)->getBlocksByTypeForLocation($block_type, $checkout_location_id);

        if (empty($snappings)) {
            continue;
        }

        $snapping_ids = array_keys($snappings);
        $snapping_id = reset($snapping_ids);

        $block = Block::instance($company_id)->getSnappingData(['?:bm_snapping.block_id'], $snapping_id);

        if (empty($block)) {
            continue;
        }

        $block_contents = db_get_array(
            'SELECT block_id, snapping_id, lang_code, object_id, object_type, content FROM ?:bm_blocks_content'
            . ' WHERE block_id = ?i',
            $block['block_id']
        );

        foreach ($block_contents as $block_content) {
            $content = @unserialize($block_content['content']);

            if (!is_array($content)) {
                continue;
            }

            $content['items']['item_ids'] = implode(',', $profile_field_ids);

            db_query(
                'UPDATE ?:bm_blocks_content SET content = ?s'
                . ' WHERE block_id = ?i AND snapping_id = ?i AND lang_code = ?s AND object_id = ?i AND object_type = ?s',
                serialize($content), $block_content['block_id'], $block_content['snapping_id'], $block_content['lang_code'],
                $block_content['object_id'], $block_content['object_type']
            );
        }
    }
}

/**
 * Change checkout blocks content according to profile fields ids in store
 *
 * @param int $layout_id Layout identifier
 *
 * @return int|bool Checkout location identifier on success false otherwise
 */
function fn_step_by_step_checkout_find_checkout_location_id($layout_id)
{
    $locations = db_get_hash_single_array(
        'SELECT location_id, dispatch FROM ?:bm_locations WHERE layout_id = ?s AND dispatch IN (?a)',
        ['dispatch', 'location_id'],
        $layout_id, ['checkout.checkout', 'checkout']
    );

    if (isset($locations['checkout.checkout'])) {
        return (int) $locations['checkout.checkout'];
    }

    if (isset($locations['checkout'])) {
        return (int) $locations['checkout'];
    }

    return false;
}

function fn_step_by_step_checkout_has_billing_and_shipping_email_profile_field()
{
    return (bool) db_get_row(
        'SELECT field_id FROM ?:profile_fields WHERE field_name = ?s AND section IN (?a) AND profile_type = ?s',
        'email', [ProfileFieldSections::SHIPPING_ADDRESS, ProfileFieldSections::BILLING_ADDRESS], ProfileTypes::CODE_USER
    );
}

/**
 * Hook handler: on gets user profiles for checkout. Makes all profiles as selectable
 */
function fn_step_by_step_checkout_checkout_get_user_profiles($auth, &$user_profiles, $profile_fields)
{
    foreach ($user_profiles as &$profile) {
        $profile['is_selectable'] = true;
    }
    unset($profile);
}

/**
 * The "checkout_is_terms_and_conditions_agreement_required_pre" hook handler.
 *
 * Actions performed:
 * - Sets Terms and conditions agreement requirement from the corresponding setting.
 *
 * @see \fn_checkout_is_terms_and_conditions_agreement_required()
 */
function fn_step_by_step_checkout_checkout_is_terms_and_conditions_agreement_required_pre(&$is_agreement_required)
{
    $is_agreement_required = YesNo::toBool(Registry::get('settings.Checkout.agree_terms_conditions'));
}
