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

class UpdatePeriods
{
    const PREVIOUS_30_DAYS = 0;
    const PREVIOUS_MONTH = 1;
    const PREVIOUS_12_MONTHS = 2;
    const PREVIOUS_YEAR = 3;

    public static function getAll()
    {
        return [
            self::PREVIOUS_30_DAYS   => 'client_tiers.previous_30_days',
            self::PREVIOUS_MONTH     => 'client_tiers.previous_month',
            self::PREVIOUS_12_MONTHS => 'client_tiers.previous_12_months',
            self::PREVIOUS_YEAR      => 'client_tiers.previous_year',
        ];
    }
}