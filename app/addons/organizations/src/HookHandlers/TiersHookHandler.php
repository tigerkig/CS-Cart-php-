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

use Tygh\Addons\Organizations\ServiceProvider;

class TiersHookHandler
{
    /**
     * The "tier_manager_update_tier_pre" hook handler.
     *
     * Actions performed:
     *  - Finds organization to which specified user belongs
     *  - If there is one - replaces id of specified user with ids of all users from his organizations
     *
     * @see \Tygh\Addons\ClientTiers\Classification\TierManager::updateTier()
     */
    public static function onUpdateUserTier(&$user_ids, $type, $allow_downgrade)
    {
        foreach ($user_ids as $user_id) {
            $organization = ServiceProvider::getOrganizationUserRepository()->findByUserId($user_id);
            if (!$organization) {
                continue;
            }
            $user_ids_expanded = array_merge(array_keys(ServiceProvider::getOrganizationUserRepository()->findUsersByORganizationId($organization->getOrganizationId())));
        }
        $user_ids = $user_ids_expanded;
    }
}