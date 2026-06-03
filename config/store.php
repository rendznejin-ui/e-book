<?php

return [
    // Display currency.
    'currency' => env('STORE_CURRENCY', 'USD'),
    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '$'),

    // Tax applied at checkout, as a percentage of the subtotal (0 = none).
    'tax_percent' => (float) env('STORE_TAX_PERCENT', 0),

    // Store / merchant identity shown on receipts.
    'name' => env('STORE_NAME', 'E-Book Store'),
    'support_email' => env('STORE_SUPPORT_EMAIL', 'support@ebook.test'),
];
