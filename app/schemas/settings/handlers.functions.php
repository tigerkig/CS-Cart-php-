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

use Tygh\Enum\ProductTracking;

defined('BOOTSTRAP') or die('Access denied');

/**
 * Returns additional input or label attributes for default_tracking setting
 *
 * @return array<string, array<string, int|string>> List of the attributes
 */
function fn_settings_handlers_general_global_tracking()
{
    return [
        'input_attributes' => [
            'checked_value' => ProductTracking::TRACK,
            'unchecked_value' => ProductTracking::DO_NOT_TRACK,
        ]
    ];
}

/**
 * Returns additional input or label attributes for default_tracking setting
 *
 * @return array<string, array<string, int|string>> List of the attributes
 */
function fn_settings_handlers_general_default_tracking()
{
    return fn_settings_handlers_general_global_tracking();
}
