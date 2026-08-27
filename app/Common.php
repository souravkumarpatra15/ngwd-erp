<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on and may also contain additional functions
 * that you'd like to use throughout your entire application.
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('renderCurrencyBreakdown')) {
    /**
     * Render a currency-keyed amount array without converting currencies.
     * Example: ['INR' => 1000, 'USD' => 25] => "₹1,000.00 · $25.00".
     */
    function renderCurrencyBreakdown(array $amounts): string
    {
        if ($amounts === []) {
            return '₹' . number_format(0, 2);
        }

        $symbols = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'AED' => 'د.إ',
            'SGD' => 'S$',
        ];

        $parts = [];
        foreach ($amounts as $currency => $amount) {
            $code = strtoupper((string) ($currency ?: 'INR'));
            $symbol = $symbols[$code] ?? ($code . ' ');
            $parts[] = esc($symbol . number_format((float) $amount, 2));
        }

        return implode(' · ', $parts);
    }
}
