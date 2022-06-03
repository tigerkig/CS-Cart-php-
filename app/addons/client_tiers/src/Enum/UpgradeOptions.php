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

namespace Tygh\Addons\ClientTiers\Enum;

class UpgradeOptions
{
    const AFTER_PURCHASE = 0;
    const AFTER_UPDATE_PERIOD = 1;

    public static function getOptions()
    {
        return [
            self::AFTER_PURCHASE        => 'client_tiers.after_completed_purchase',
            self::AFTER_UPDATE_PERIOD   => 'client_tiers.after_tier_check',

        ];
    }
}