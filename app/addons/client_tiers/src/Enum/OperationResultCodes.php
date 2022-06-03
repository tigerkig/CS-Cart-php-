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

class OperationResultCodes
{
    const TIER_STAYS_THE_SAME = 0;
    const SUCCESSFULLY_SET_TIER = 1;
    const FAIL_SET_NEW_TIER = 2;
    const SUCCESSFULLY_UNSET_TIER = 3;
    const FAIL_UNSET_OLD_TIER = 4;
    const REQUIRED_OPERATION_REFUSED = 5;

}