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
 * Interface ValidatorInteface provides interface of a PHP extensions requirement validator.
 *
 * @package Installer\Requirements
 */
interface ValidatorInteface
{
    /** @var int Any of the extensions is required */
    const REQUIRE_ANY = 0;

    /** @var int All of the extensiosn are required */
    const REQUIRE_ALL = 1;

    /** @var string Extensions missing error code */
    const EXTENSION_MISSING = 'missing';

    /**
     * ValidatorInteface constructor.
     */
    public function __construct();

    /**
     * Provides list of extensions the validator requires.
     *
     * @return string[]
     */
    public function getRequirements();

    /**
     * Provides the extensions check mode.
     *
     * @return int
     */
    public function getRequirementsMode();

    /**
     * Checks if the extensions are installed on the server.
     *
     * @return bool
     */
    public function validate();

    /**
     * Provides validation errors.
     *
     * @return string[]
     */
    public function getErrors();

    /**
     * Sets validation errors.
     *
     * @param string[] $errors
     */
    public function setErrors(array $errors);

    /**
     * Provides validation warnings.
     *
     * @return string[]
     */
    public function getWarnings();

    /**
     * Sets validation warnings.
     *
     * @param string[] $warnings
     */
    public function setWarnings(array $warnings);
}
