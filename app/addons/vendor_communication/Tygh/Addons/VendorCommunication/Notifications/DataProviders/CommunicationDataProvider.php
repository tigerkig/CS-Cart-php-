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


namespace Tygh\Addons\VendorCommunication\Notifications\DataProviders;

use Tygh\Enum\UserTypes;
use Tygh\Enum\YesNo;
use Tygh\Enum\Addons\VendorCommunication\CommunicationTypes;
use Tygh\Notifications\DataProviders\BaseDataProvider;

/**
 * Class CommunicationDataProvider provides a data for message transports that required for sending messages
 * about events added in Vendor Communication addon.
 *
 * @package Tygh\Addons\VendorCommunication\Notifications\DataProviders
 */
class CommunicationDataProvider extends BaseDataProvider
{
    protected $lang_code = null;
    protected $company_id = 0;

    public function __construct(array $data)
    {
        $this->company_id = isset($data['company_id']) ? $data['company_id'] : 0;

        $data['lang_code'] = $this->getLangCode($data);
        $data['to'] = $this->getTo($data);
        $data = $this->getActionUrls($data);
        $data['message_author'] = $this->getMessageAuthor($data);
        $data['company_name'] = fn_get_company_name($this->company_id);
        if (fn_allowed_for('MULTIVENDOR')) {
            $data['is_vendor_to_admin'] = $data['communication_type'] === CommunicationTypes::VENDOR_TO_ADMIN;
            $data['admin_user_id'] = fn_get_company_admin_user_id($this->company_id);
        }
        parent::__construct($data);
    }

    protected function getActionUrls(array $data)
    {
        $data['action_url'] = 'vendor_communication.view?thread_id=' . $data['thread_id'] . '&communication_type=' . $data['communication_type'];

        return $data;
    }

    /**
     * Finds proper language code for notification receiver.
     *
     * @param array<string, string> $data Information about notification.
     *
     * @return string
     */
    protected function getLangCode(array $data)
    {
        if (isset($this->lang_code)) {
            return $this->lang_code;
        }
        if ($data['last_message_user_type'] !== UserTypes::VENDOR) {
            $user_id = fn_get_company_root_admin_user_id($this->company_id) ?: 1;
        } else {
            $user_id = $data['user_id'];
        }
        $user_info = fn_get_user_info((int) $user_id);
        return $this->lang_code = isset($user_info['lang_code']) ? $user_info['lang_code'] : CART_LANGUAGE;
    }

    protected function getTo(array $data)
    {
        return [
            'vendor' => $this->getVendorReceiver($data),
            'customer' => $this->getCustomerReceiver($data),
        ];
    }

    protected function getMessageAuthor(array $data)
    {
        if (!empty($data['last_message_user_id'])) {
            $message_from = fn_vendor_communication_get_user_name($data['last_message_user_id']);
        }
        return !empty($message_from) ? $message_from : __('customer');
    }

    protected function getVendorReceiver(array $data)
    {
        $to = db_get_field('SELECT email FROM ?:companies WHERE company_id = ?i', $data['company_id']);
        return $to;
    }

    protected function getCustomerReceiver(array $data)
    {
        $to = fn_get_user_short_info($data['user_id']);
        return $to['email'];
    }
}
