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

namespace Installer\Requirements;

/**
 * Class MultibyteStringSupportValidator provides multi-byte extensions requirement validator.
 *
 * @package Installer\Requirements
 */
final class MultibyteStringSupportValidator extends AbstractValidator
{
    /**
     * @var array<string> Required extensions
     */
    protected $extensions = ['mbstring', 'iconv'];

    /**
     * @var int Extensions requirement conditions
     */
    protected $extensions_mode = self::REQUIRE_ALL;
}
