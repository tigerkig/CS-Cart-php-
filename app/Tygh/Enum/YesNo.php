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

namespace Tygh\Enum;

/**
 * Class YesNo contains possible values of boolean type used in the database.
 *
 * @package Tygh\Enum
 */
class YesNo
{
    const YES = 'Y';
    const NO = 'N';

    /**
     * Converts value to the string representation.
     *
     * @param bool|string $val
     *
     * @return string
     */
    public static function toId($val)
    {
        return $val === true || $val === self::YES
            ? YesNo::YES
            : YesNo::NO;
    }

    /**
     * Converts value to the boolean representation.
     *
     * @param bool|string|int|null $val Value
     *
     * @return bool
     */
    public static function toBool($val)
    {
        return $val === true || $val === self::YES;
    }
}
