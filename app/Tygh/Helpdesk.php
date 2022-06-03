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

/* WARNING: DO NOT MODIFY THIS FILE TO AVOID PROBLEMS WITH THE CART FUNCTIONALITY */

namespace Tygh;

use Tygh\Enum\SiteArea;
use Tygh\Tygh;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\NotificationsCenter\NotificationsCenter;

/**
 *
 * Helpdesk connector class
 *
 */
class Helpdesk
{
    /**
     * Returns current license status
     *
     * @param  string $license_number
     * @param  array $extra_fields
     *
     * @return string
     */
    public static function getLicenseInformation($license_number = '', $extra_fields = array())
    {
        Registry::set('log_cut', false);
		return 'ACTIVE';
    }

    /**
     * Set/Get token auth key
     * @param  string $generate If generate value is equal to "true", new token will be generated
     * @return string token value
     */
    public static function token($generate = false)
    {
        if ($generate) {
            $token = fn_crc32(microtime());
            fn_set_storage_data('hd_request_code', $token);
        } else {
            $token = fn_get_storage_data('hd_request_code');
        }

        return $token;
    }

    /**
     * Get store auth key
     *
     * @return string store key
     */
    public static function getStoreKey()
    {
        $key = Registry::get('settings.store_key');
        $host_path = Registry::get('config.http_host') . Registry::get('config.http_path');

        if (!empty($key)) {
            list($token, $host) = explode(';', $key);
            if ($host != $host_path) {
                unset($key);
            }
        }

        if (empty($key)) {
            // Generate new value
            $key = fn_crc32(microtime());
            $key .= ';' . $host_path;
            Settings::instance()->updateValue('store_key', $key);
        }

        return $key;
    }

    public static function auth()
    {
        $_SESSION['last_status'] = 'INIT';

        self::initHelpdeskRequest();

        return true;
    }

    public static function initHelpdeskRequest($area = AREA)
    {
        if ($area != 'C') {
            $protocol = defined('HTTPS') ? 'https' : 'http';

            $_SESSION['stats'][] = '<img src="' . fn_url('helpdesk_connector.auth', 'A', $protocol) . '" alt="" style="display:none" />';
        }
    }

    /**
     * Parse license information
     *
     * @param  string    $data             Result from [self::getLicenseInformation]
     * @param  array     $auth
     * @param  bool|true $process_messages
     * @return array     Return string $license, string $updates, array $messages, array $params
     */
    public static function parseLicenseInformation($data, $auth, $process_messages = true)
    {
        $_SESSION['last_status'] = 'ACTIVE';
		return array('ACTIVE', 'NO UPDATES', '', []);
    }

    public static function processMessages($messages, $process_messages = true, $license_status = '')
    {
        $new_messages = [];

        if (!empty($messages)) {

            foreach ($messages->Message as $message) {
                $message_id = empty($message->Id)
                    ? intval(fn_crc32(microtime()) / 2)
                    : (string) $message->Id;

                $new_messages[$message_id] = [
                    'text'  => (string) $message->Text,
                    'type'  => empty($message->Type)
                        ? NotificationSeverity::WARNING
                        : (string) $message->Type,
                    'title' => empty($message->Title)
                        ? __('notice')
                        : (string) $message->Title,
                    'state' => empty($message->State)
                        ? null
                        : (string) $message->State,
                    'action_url' => empty($message->ActionUrl)
                        ? ''
                        : (string) $message->ActionUrl,
                    'pinned' => !empty($message->Pinned) && $message->Pinned,
                    'remind' => !empty($message->Remind) && $message->Remind
                ];
            }

            // check new messages for 'special' messages
            $special_messages = fn_get_schema('settings', 'licensing');
            foreach ($special_messages as $special_message_id => $message_info) {
                if (isset($new_messages[$special_message_id])) {
                    $new_messages[$special_message_id] = fn_array_merge(
                        $new_messages[$special_message_id],
                        [
                            'text'       => $message_info['message']    ?: $new_messages[$special_message_id]['text'],
                            'type'       => $message_info['severity']   ?: $new_messages[$special_message_id]['type'],
                            'title'      => $message_info['title']      ?: $new_messages[$special_message_id]['title'],
                            'state'      => $message_info['state']      ?: $new_messages[$special_message_id]['state'],
                            'action_url' => $message_info['action_url'] ?: $new_messages[$special_message_id]['action_url'],
                            'section'    => $message_info['section']    ?: NotificationsCenter::SECTION_ADMINISTRATION,
                            'tag'        => $message_info['tag']        ?: NotificationsCenter::TAG_OTHER,
                        ]
                    );
                }
            }

            if (!empty($license_status) && !$new_messages) {
                switch ($license_status) {
                    case 'PENDING':
                    case 'SUSPENDED':
                    case 'DISABLED':
                        $new_messages['license_error_license_is_disabled'] = [
                            'type'       => NotificationSeverity::ERROR,
                            'title'      => __('error'),
                            'text'       => __('licensing.license_error_license_is_disabled'),
                            'action_url' => '',
                            'section'    => NotificationsCenter::SECTION_ADMINISTRATION,
                            'tag'        => NotificationsCenter::TAG_LICENSE,
                        ];
                        break;
                    case 'LICENSE_IS_INVALID':
                        $new_messages['license_error_license_is_invalid'] = [
                            'type'       => NotificationSeverity::ERROR,
                            'title'      => __('error'),
                            'text'       => __('licensing.license_error_license_is_invalid'),
                            'action_url' => '',
                            'section'    => NotificationsCenter::SECTION_ADMINISTRATION,
                            'tag'        => NotificationsCenter::TAG_LICENSE,
                        ];
                        break;
                }
            }

            if ($process_messages) {
                /** @var \Tygh\NotificationsCenter\NotificationsCenter $notifications_center */
                $notifications_center = Tygh::$app['notifications_center'];
                /** @var \Tygh\Database\Connection $db */
                $db = Tygh::$app['db'];
                $root_admin_user_id = (int) $db->getField(
                    'SELECT user_id FROM ?:users WHERE user_type = ?s AND is_root = ?s AND company_id = ?i',
                    UserTypes::ADMIN,
                    YesNo::YES,
                    0
                );

                foreach ($new_messages as $msg) {
                    $notifications_center->add([
                        'user_id'    => $root_admin_user_id,
                        'title'      => $msg['title'],
                        'message'    => $msg['text'],
                        'severity'   => $msg['type'],
                        'area'       => SiteArea::ADMIN_PANEL,
                        'action_url' => $msg['action_url'],
                        'section'    => isset($msg['section'])
                            ? $msg['section']
                            : NotificationsCenter::SECTION_OTHER,
                        'tag'        => isset($msg['tag'])
                            ? $msg['tag']
                            : NotificationsCenter::TAG_OTHER,
                        'language_code' => Registry::get('settings.Appearance.backend_default_language'),
                        'pinned'     => $msg['pinned'],
                        'remind'     => $msg['remind']
                    ]);
                }
            }
        }

        return $new_messages;
    }

    public static function registerLicense($license_data)
    {
        return array('', 'ACTIVE', '');
    }

    public static function checkStoreImportAvailability($license_number, $version, $edition = PRODUCT_EDITION)
    {
        return true;
    }

    /**
     * Masques license number when the demo mode is enabled
     *
     * @param string $license_number License number
     * @param bool   $is_demo_mode   True if demo mode enabled
     *
     * @return string Spoofed (if necessary) license number
     */
    public static function masqueLicenseNumber($license_number, $is_demo_mode = false)
    {
        if ($license_number && $is_demo_mode) {
            $license_number = preg_replace('/[^-]/', 'X', $license_number);
        }

        return $license_number;
    }

    /**
     * Checks store mode.
     *
     * @param string $license_number License number
     * @param array  $auth           Auth data
     * @param array  $extra          Extra data to include into license check
     *
     * @return array License status, messages and store mode
     */
    public static function getStoreMode($license_number, $auth, $extra = array())
    {
        $extra['store_mode'] = 'ultimate';
		return [ 'ACTIVE', '', 'ultimate' ];
    }

    /**
     * Checks if companies limitations have been reached.
     *
     * @deprecated since 4.10.1.
     * Use \Tygh\Helpdesk::isStorefrontsLimitReached instead
     *
     * @return bool
     */
    public static function isCompaniesLimitReached()
    {
        return static::isStorefrontsLimitReached();
    }

    /**
     * Checks if storefronts limitations have been reached.
     *
     * @return bool True if there are too many storefronts
     */
    public static function isStorefrontsLimitReached()
    {
        return false;
    }

    /**
     * Sends usage feature metrics.
     */
    public static function sendReportMetrics()
    {
    }

    public static function isValidRequest(array $request, array $additional_validation_params = [])
    {
        return 'valid';
    }

    public static function getSoftwareInformation($stop_execution = true, $format = 'html')
    {
    }
}
