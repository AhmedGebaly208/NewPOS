<?php

return [
    'name' => 'POS',
    
    // Store Information
    'store_name' => env('STORE_NAME', config('app.name')),
    'store_address' => env('STORE_ADDRESS', ''),
    'store_phone' => env('STORE_PHONE', ''),
    'store_email' => env('STORE_EMAIL', ''),
    
    // Barcode Scanner Settings
    'barcode_enabled' => env('POS_BARCODE_ENABLED', true),
    'barcode_prefix' => env('POS_BARCODE_PREFIX', ''),
    'barcode_suffix' => env('POS_BARCODE_SUFFIX', 'Enter'),
    
    // Auto-clear cart after sale
    'auto_clear_cart' => true,
    
    // Auto-print receipt
    'auto_print_receipt' => env('POS_AUTO_PRINT', false),
    
    // Show out of stock products in search
    'show_out_of_stock' => false,
    
    // Maximum items in cart
    'max_cart_items' => 100,
    
    // Keyboard Shortcuts
    'shortcuts' => [
        'search' => 'F2',
        'customer' => 'F3',
        'discount' => 'F4',
        'hold' => 'F5',
        'clear' => 'F6',
        'pay' => 'F9',
    ],
];
