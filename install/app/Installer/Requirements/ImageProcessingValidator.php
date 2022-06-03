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
 * Class ImageProcessingValidator provides ImageMagick and GD extensions requirement validator.
 *
 * @package Installer\Requirements
 */
final class ImageProcessingValidator extends AbstractValidator
{
    protected $extensions = ['imagick', 'gd'];

    protected $extensions_mode = self::REQUIRE_ANY;

    public function validate()
    {
        $result = parent::validate();

        if (in_array('gd', self::$installed_extensions) && !in_array('imagick', self::$installed_extensions)) {
            $this->warnings[] = 'bad_lib';
        }

        return $result;
    }
}
