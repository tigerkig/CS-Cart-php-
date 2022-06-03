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

namespace Tygh\Addons\ClientTiers\HookHandlers;



use Tygh\Addons\ClientTiers\Enum\Logging;
use Tygh\Application;

class LoggingHookHandler
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "save_log" hook handler.
     *
     * Actions performed:
     *     - Saves tiers recalculation results.
     *
     * @see \fn_log_event()
     */
    public function onSaveLog($type, $action, $data, $user_id, &$content, $event_type)
    {
        if ($type === Logging::LOG_TYPE_CLIENT_TIERS) {
            /** @var \Tygh\Common\OperationResult $result */
            $result = $data['result'];

            $content = [
                'user_id'  => $result->getData('user_id'),
                'username' => $result->getData('username'),
                'client_tiers.moved_to_new_group' => $result->getData('new_group'),
                'client_tiers.removed_from_old_group' => $result->getData('old_group'),
            ];

            switch ($action) {
                case Logging::ACTION_SUCCESS:
                    break;
                case Logging::ACTION_FAILURE:
                    $errors = array_map('strip_tags', $result->getErrors());
                    $content['error'] = implode("\n", $errors);
                    break;
            }
        }

        return true;
    }

}