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

class Logging
{
    const LOG_TYPE_CLIENT_TIERS = 'client_tiers';
    const ACTION_SUCCESS = 'ct_success';
    const ACTION_FAILURE = 'ct_failure';

    public static function getActions()
    {
        return [
            self::ACTION_SUCCESS,
            self::ACTION_FAILURE,
        ];
    }
}