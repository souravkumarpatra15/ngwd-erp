<?php
if (!function_exists('isActive')) {
    function isActive(string $path): string {
        return (strpos(current_url(), $path) !== false) ? 'active' : '';
    }
}
if (!function_exists('leadStatusColor')) {
    function leadStatusColor(string $status): string {
        return match($status) {
            'new' => 'primary','contacted' => 'info','follow_up' => 'warning',
            'proposal_sent' => 'secondary','negotiation' => 'purple',
            'converted' => 'success','lost' => 'danger', default => 'secondary',
        };
    }
}
if (!function_exists('projectStatusColor')) {
    function projectStatusColor(string $status): string {
        return match($status) {
            'pending' => 'warning','development' => 'primary','testing' => 'info',
            'revision' => 'secondary','completed' => 'success',
            'on_hold' => 'dark','cancelled' => 'danger', default => 'secondary',
        };
    }
}
if (!function_exists('invoiceStatusColor')) {
    function invoiceStatusColor(string $status): string {
        return match($status) {
            'draft' => 'secondary','sent' => 'info','paid' => 'success',
            'partial' => 'warning','overdue' => 'danger', default => 'secondary',
        };
    }
}
if (!function_exists('currencySymbol')) {
    // Display-only symbol lookup. Amounts are stored already in the
    // record's chosen currency — no FX conversion happens here.
    function currencySymbol(?string $code): string {
        $map = ['INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'AUD' => 'A$', 'CAD' => 'C$', 'AED' => 'د.إ', 'SGD' => 'S$'];
        return $map[strtoupper($code ?: 'INR')] ?? (strtoupper($code) . ' ');
    }
}
if (!function_exists('formatMoney')) {
    function formatMoney(float $amount, string $currency = 'INR'): string {
        // Back-compat: also accept a literal symbol (old call style) as well as a 3-letter code.
        $symbol = (strlen($currency) <= 3 && ctype_alpha($currency)) ? currencySymbol($currency) : $currency;
        return $symbol . number_format($amount, 2);
    }
}
if (!function_exists('renderCurrencyBreakdown')) {
    /**
     * Render a currency-keyed amount array without converting currencies.
     * Example: ['INR' => 1000, 'USD' => 25] => "₹1,000.00 · $25.00".
     */
    function renderCurrencyBreakdown(array $amounts): string {
        if ($amounts === []) {
            return formatMoney(0, 'INR');
        }

        $parts = [];
        foreach ($amounts as $currency => $amount) {
            $code = strtoupper((string) ($currency ?: 'INR'));
            $parts[] = esc(formatMoney((float) $amount, $code));
        }

        return implode(' · ', $parts);
    }
}
if (!function_exists('daysUntil')) {
    function daysUntil(string $date): int {
        return (int) ceil((strtotime($date) - time()) / 86400);
    }
}
if (!function_exists('priorityColor')) {
    function priorityColor(string $priority): string {
        return match($priority) {
            'low' => 'success','medium' => 'warning','high' => 'danger','urgent' => 'dark',
            default => 'secondary',
        };
    }
}
