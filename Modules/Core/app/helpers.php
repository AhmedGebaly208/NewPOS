<?php

if (!function_exists('module_path')) {
    /**
     * Get the module path.
     */
    function module_path(string $module, string $path = ''): string
    {
        $modulePath = base_path("Modules/{$module}");
        
        return $path ? $modulePath . '/' . ltrim($path, '/') : $modulePath;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount to currency.
     */
    function format_currency(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? config('core.currency', 'USD');
        $symbol = config('core.currency_symbols.' . $currency, '$');
        
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date to human readable format.
     */
    function format_date($date, ?string $format = null): string
    {
        if (!$date) {
            return '';
        }

        $format = $format ?? config('core.date_format', 'Y-m-d H:i:s');
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}

if (!function_exists('generate_reference_number')) {
    /**
     * Generate a unique reference number.
     */
    function generate_reference_number(string $prefix = 'REF'): string
    {
        return $prefix . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input.
     */
    function sanitize_input(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_percentage')) {
    /**
     * Calculate percentage of a value.
     */
    function get_percentage(float $value, float $percentage): float
    {
        return ($value * $percentage) / 100;
    }
}

if (!function_exists('apply_discount')) {
    /**
     * Apply discount to amount.
     */
    function apply_discount(float $amount, float $discount, string $type = 'percentage'): float
    {
        if ($type === 'percentage') {
            return $amount - get_percentage($amount, $discount);
        }
        
        return max(0, $amount - $discount);
    }
}

if (!function_exists('calculate_tax')) {
    /**
     * Calculate tax amount.
     */
    function calculate_tax(float $amount, float $taxRate): float
    {
        return get_percentage($amount, $taxRate);
    }
}

if (!function_exists('get_current_user_id')) {
    /**
     * Get current authenticated user ID.
     */
    function get_current_user_id(): ?int
    {
        return auth()->id();
    }
}

if (!function_exists('log_activity')) {
    /**
     * Log user activity.
     */
    function log_activity(string $action, string $description, array $properties = []): void
    {
        \Illuminate\Support\Facades\Log::info($action, [
            'description' => $description,
            'user_id' => get_current_user_id(),
            'ip' => request()->ip(),
            'properties' => $properties,
            'timestamp' => now(),
        ]);
    }
}
