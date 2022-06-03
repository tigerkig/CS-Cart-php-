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

namespace Tygh\Enum\Addons\Searchanise;

/**
 * Class QueueActions contains searchanise queue actions
 *
 * @package Tygh\Enum\Addons\Searchanise
 */
class QueueActions
{
    const UPDATE_PRODUCTS     = 'update';
    const UPDATE_CATEGORIES   = 'categories_update';
    const UPDATE_PAGES        = 'pages_update';
    const UPDATE_FACETS       = 'facet_update';

    const DELETE_PRODUCTS     = 'delete';
    const DELETE_CATEGORIES   = 'categories_delete';
    const DELETE_PAGES        = 'pages_delete';
    const DELETE_FACETS       = 'facet_delete';

    const DELETE_PRODUCTS_ALL = 'delete_all';
    const DELETE_FACETS_ALL   = 'facet_delete_all';

    const PREPARE_FULL_IMPORT = 'prepare_full_import';
    const START_FULL_IMPORT   = 'start_full_import';
    const END_FULL_IMPORT     = 'end_full_import';
}
