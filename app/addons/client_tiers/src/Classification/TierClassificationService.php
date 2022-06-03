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

use Tygh\Database\Connection;
use Tygh\Enum\UsergroupLinkStatuses;
use Tygh\Tygh;

/**
 * Class TierClassificationService provides methods to work with usergroups tiers classification
 *
 * @package Tygh\Addons\ClientTiers\Classification
 */
class TierClassificationService
{
    /** @var array */
    protected $classification;

    /** @var null|\Tygh\Database\Connection  */
    protected $db;

    /**
     * TierClassificationService constructor.
     *
     * @param \Tygh\Database\Connection|null $db
     */
    public function __construct(Connection $db = null)
    {
        $this->db = ($db) ? $db : Tygh::$app['db'];
        $this->initClassification();
    }

    /**
     * Gets array of user groups with theirs minimum spend values.
     *
     * @param bool $new Flag to get new user groups classification from database.
     *
     * @return array
     */
    public function getClassification($new = false)
    {
        if (empty($this->classification) || $new) {
            $this->initClassification();
        }

        return $this->classification;
    }

    /**
     * Returns ordered array of user group id with minimum spend value assigned to this user group
     *
     */
    public function initClassification()
    {
        $this->classification = $this->db->getArray('SELECT usergroup_id, min_spend_value FROM ?:usergroups_tiers WHERE min_spend_value != 0 ORDER BY min_spend_value ASC');
    }

    /**
     * Calculates tier that should be assigned with specified amount of spent money.
     *
     * @param double $total Amount of spent money
     *
     * @return int|null Number of tier that appropriate for this total | null if there is not appropriate tier
     */
    public function findProperTierByTotalSpend($total)
    {
        $selected_tier = null;

        if (empty($total)) {
            return null;
        }
        foreach ($this->classification as $tier => $usergroup) {
            if ($usergroup['min_spend_value'] > $total) {
                break;
            }
            $selected_tier = $tier;
        }

        return $selected_tier;
    }

    /**
     * Gets tier in which specified user belongs.
     *
     * @param int $user_id
     *
     * @return int|null Number of current tier for user | null, if current user does not belong to any tier
     */
    public function getCurrentTierByUserId($user_id)
    {
        $usergroup_ids = fn_get_user_usergroups($user_id);
        foreach ($usergroup_ids as $usergroup_id => $usergroup_data) {
            if ($usergroup_data['status'] !== UsergroupLinkStatuses::ACTIVE) {
                continue;
            }
            foreach ($this->classification as $tier => $usergroup) {
                if ($usergroup_id == $usergroup['usergroup_id']) {
                    return $tier;
                }
            }
        }

        return null;
    }

    /**
     * Gets usergroup id for specific tier
     *
     * @param int $tier_id Number of tier
     *
     * @return int|bool Usergroup_id from specified tier | false  if there is not usergroup for this tier_id
     */
    public function getUsergroupByTier($tier_id)
    {
        return isset($this->classification[$tier_id]['usergroup_id']) ? $this->classification[$tier_id]['usergroup_id'] : false;
    }
}