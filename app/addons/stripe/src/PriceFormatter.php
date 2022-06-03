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

namespace Tygh\Addons\Stripe;

use Tygh\Tools\Formatter;

/**
 * Class PriceFormatter formats prices for Stripe payments.
 *
 * @package Tygh\Addons\Stripe
 */
class PriceFormatter
{
    /**
     * @var \Tygh\Tools\Formatter
     */
    protected $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Formats payment amount by currency.
     *
     * @param float  $amount   Payment amount
     * @param string $currency Current code
     *
     * @return int
     */
    public function asCents($amount, $currency)
    {
        $amount = $this->formatter->asPrice($amount, $currency, false, false);

        $amount_in_cents = $this->convertToCents($amount);

        return $amount_in_cents;
    }

    /**
     * Converts amount to smallest currency unit.
     *
     * @param float $amount Monetary amount.
     *
     * @return int Amount in cents
     */
    protected function convertToCents($amount)
    {
        $amount = preg_replace('/\D/', '', $amount);

        $amount_in_cents = (int) ltrim($amount, '0');

        return $amount_in_cents;
    }
}
