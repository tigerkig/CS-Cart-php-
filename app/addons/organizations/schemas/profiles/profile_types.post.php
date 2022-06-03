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
 ***************************************************************************/

use Tygh\Enum\ProfileFieldSections;
use Tygh\Addons\Organizations\Enum\ProfileTypes;
use Tygh\Enum\ProfileFieldAreas;

$schema[ProfileTypes::CODE_ORGANIZATION] = [
    'name'             => ProfileTypes::ORGANIZATION,
    'allowed_sections' => [
        ProfileFieldSections::CONTACT_INFORMATION,
        ProfileFieldSections::SHIPPING_ADDRESS,
        ProfileFieldSections::BILLING_ADDRESS,
        ProfileFieldSections::BILLING_AND_SHIPPING_ADDRESS,
    ],
    'allowed_areas'    => [
        ProfileFieldAreas::PROFILE,
    ],
];

return $schema;
