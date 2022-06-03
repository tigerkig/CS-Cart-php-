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

namespace Tygh\Api\Entities;

class Vendors extends Stores
{
    /**
     * Returns privileges for Vendors entity
     *
     * @return array<string, string>
     */
    public function privileges()
    {
        return [
            'create' => 'manage_vendors',
            'update' => 'manage_vendors',
            'delete' => 'manage_vendors',
            'index'  => 'view_vendors'
        ];
    }
}
