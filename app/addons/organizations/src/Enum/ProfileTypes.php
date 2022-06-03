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

namespace Tygh\Addons\Organizations\Enum;

use Tygh\Enum\ProfileTypes as BaseProfileTypes;

/**
 * Class ProfileTypes extends base  ProfileTypes class and declares profile type for organization.
 *
 * @package Tygh\Addons\Organizations\Enum
 */
class ProfileTypes extends BaseProfileTypes
{
    const ORGANIZATION = 'organization';
    const CODE_ORGANIZATION = 'C';
}