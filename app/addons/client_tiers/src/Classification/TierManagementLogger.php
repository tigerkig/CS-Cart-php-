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

namespace Tygh\Addons\ClientTiers\Classification;

use Tygh\Addons\ClientTiers\Enum\Logging;
use Tygh\Addons\ClientTiers\Enum\OperationResultCodes;
use Tygh\Addons\ClientTiers\ServiceProvider;
use Tygh\Common\OperationResult;

/**
 * Class TierManagementLogger allows to use any logging operations with tier management operations
 *
 * @package Tygh\Addons\ClientTiers\Classification
 */
class TierManagementLogger
{
    /** @var \Tygh\Addons\ClientTiers\Classification\TierClassificationService */
    protected $classification_service;

    public function __construct(TierClassificationService $classification_service)
    {
        $this->classification_service = $classification_service;
    }

    /**
     * Contains all logging options for operations with tiers
     *
     * @param array $operation_result Object contains 2 fields:
     *                                code - Code of state of executed operation
     *                                data - Data from executed operation
     *
     * @return \Tygh\Common\OperationResult
     */
    protected function logTierUpdateResult($operation_result)
    {
        $code = $operation_result['code'];
        $data = $operation_result['data'];
        $username = fn_get_user_name($data['user_id']);

        $result = new OperationResult(false);
        $result->setData($data['user_id'], 'user_id');
        $result->setData($username, 'username');
        $result->setData(fn_format_price($data['user_total']), 'current_total');

        switch ($code) {
            case OperationResultCodes::SUCCESSFULLY_SET_TIER:
                $result->setSuccess(true);
                $new_group = fn_get_usergroup_name($this->classification_service->getUsergroupByTier($data['new_tier']));
                $result->setData($new_group, 'new_group');
                $result->addMessage($code, __('client_tiers.client_success_set_tier',
                    ['[username]' => $username, '[total]' => $data['user_total'], '[new_group]' => $new_group]));
                fn_log_event(Logging::LOG_TYPE_CLIENT_TIERS, Logging::ACTION_SUCCESS, ['result' => $result]);
                break;
            case OperationResultCodes::FAIL_SET_NEW_TIER:
                $new_group = fn_get_usergroup_name($this->classification_service->getUsergroupByTier($data['new_tier']));
                $result->setData($new_group, 'new_group');
                $result->addError($code, __('client_tiers.client_fail_set_new_tier', [
                    '[user_id]'   => $data['user_id'],
                    '[total]'     => $data['user_total'],
                    '[new_group]' => $new_group
                ]));
                fn_log_event(Logging::LOG_TYPE_CLIENT_TIERS, Logging::ACTION_FAILURE, ['result' => $result]);
                break;
            case OperationResultCodes::SUCCESSFULLY_UNSET_TIER:
                $result->setSuccess(true);
                $old_group = fn_get_usergroup_name($this->classification_service->getUsergroupByTier($data['old_tier']));
                $result->setData($old_group, 'old_group');
                $result->addMessage($code, __('client_tiers.client_success_unset_tier',
                    ['[username]' => $username, '[total]' => $data['user_total'], '[old_group]' => $old_group]));
                fn_log_event(Logging::LOG_TYPE_CLIENT_TIERS, Logging::ACTION_SUCCESS, ['result' => $result]);
                break;
            case OperationResultCodes::FAIL_UNSET_OLD_TIER:
                $old_group = fn_get_usergroup_name($this->classification_service->getUsergroupByTier($data['old_tier']));
                $result->setData($old_group, 'old_group');
                $result->addError($code, __('client_tiers.client_fail_unset_old_tier', [
                    '[user_id]'   => $data['user_id'],
                    '[total]'     => $data['user_total'],
                    '[old_group]' => $old_group
                ]));
                fn_log_event(Logging::LOG_TYPE_CLIENT_TIERS, Logging::ACTION_FAILURE, ['result' => $result]);
                break;
            case OperationResultCodes::REQUIRED_OPERATION_REFUSED:
            case OperationResultCodes::TIER_STAYS_THE_SAME:
                $result->setSuccess(true);
                break;
            default:
                break;
        }

        return $result;
    }

    public function showLogs(array $operation_results)
    {
        foreach ($operation_results as $operation_result) {
            $this->logTierUpdateResult($operation_result);
        }
    }

    public function showNotifications(array $operation_results)
    {
        foreach ($operation_results as $operation_result) {
            $result = $this->logTierUpdateResult($operation_result);
            $result->showNotifications();
        }
    }
}