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
if (!function_exists('formatMoney')) {
    function formatMoney(float $amount, string $currency = '₹'): string {
        return $currency . number_format($amount, 2);
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
