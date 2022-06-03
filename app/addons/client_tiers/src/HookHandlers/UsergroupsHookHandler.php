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

use Tygh\Application;

class UsergroupsHookHandler
{
    /** @var \Tygh\Database\Connection */
    protected $db;

    /**
     * UsergroupsHookHandler constructor.
     *
     * @param \Tygh\Application $application
     */
    public function __construct(Application $application)
    {
        $this->db = $application['db'];
    }

    /**
     * The "get_usergroups" hook handler.
     *
     * Actions performed:
     *     - Adds minimum spend value as a parameter for user group.
     *
     * @see \fn_get_usergroups()
     */
    public function onGetUsergroups($params, $lang_code, &$field_list, &$join, &$condition, $group_by, $order_by, $limit)
    {
        $field_list .= ', tiers.min_spend_value';
        $join .= ' LEFT JOIN ?:usergroups_tiers as tiers ON tiers.usergroup_id = a.usergroup_id';

        if (isset($params['min_spend_value'])) {
            $condition .= $this->db->query(' AND tiers.min_spend_value = ?i', $params['min_spend_value']);
        }
    }

    /**
     * The "update_usergroup" hook handler.
     *
     * Actions performed:
     *     - Updates minimum spend value for specified user group.
     *
     * @see \fn_update_usergroup()
     */
    public function onUpdateUsergroup($usergroup_data, $usergroup_id, $create)
    {
        if ($create) {
            $this->db->query('INSERT INTO ?:usergroups_tiers (min_spend_value, usergroup_id) VALUES (?i, ?i)', 0, $usergroup_id);
        } elseif (isset($usergroup_data['min_spend_value'])) {
            $this->db->query('UPDATE ?:usergroups_tiers SET min_spend_value = ?i WHERE usergroup_id = ?i', $usergroup_data['min_spend_value'], $usergroup_id);
        }
    }

    /**
     * The "delete_usergroups" hook handler.
     *
     * Actions performed:
     *     - Deletes minimum spend value for deleting user group.
     *
     * @see \fn_delete_usergroups()
     */
    public function onDeleteUsergroup($usergroup_ids)
    {
        $this->db->query('DELETE FROM ?:usergroups_tiers WHERE usergroup_id IN (?n)', $usergroup_ids);
    }

}