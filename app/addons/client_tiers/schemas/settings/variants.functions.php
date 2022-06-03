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

use \Tygh\Addons\ClientTiers\Enum\UpdatePeriods;
use \Tygh\Addons\ClientTiers\Enum\UpgradeOptions;

function fn_settings_variants_addons_client_tiers_tiers_reporting_period()
{
    $variants = UpdatePeriods::getAll();

    foreach ($variants as $variant_code => $variant_text) {
        $variants[$variant_code] = __($variant_text);
    }
    return $variants;
}

function fn_settings_variants_addons_client_tiers_upgrade_tier_option()
{
    $variants = UpgradeOptions::getOptions();

    foreach ($variants as $variant_code => $variant_text) {
        $variants[$variant_code] = __($variant_text);
    }
    return $variants;
}