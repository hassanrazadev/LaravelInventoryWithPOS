<?php

return [
    'store_name' => env('POS_STORE_NAME', env('APP_NAME', 'IMS-POS')),
    'store_address' => env('POS_STORE_ADDRESS', 'In-Store Point of Sale'),
    'store_phone' => env('POS_STORE_PHONE', ''),
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Thank you for your business!'),

    'printer' => [
        'driver' => env('POS_PRINTER_DRIVER', 'tcp'),
        'host' => env('POS_PRINTER_HOST', '127.0.0.1'),
        'port' => (int) env('POS_PRINTER_PORT', 9100),
        'http_path' => env('POS_PRINTER_HTTP_PATH', '/'),
        'http_format' => env('POS_PRINTER_HTTP_FORMAT', 'escpos'),
        'paper_width' => (int) env('POS_PRINTER_WIDTH', 80),
        'open_cash_drawer' => filter_var(env('POS_OPEN_CASH_DRAWER', true), FILTER_VALIDATE_BOOLEAN),
        'timeout' => (int) env('POS_PRINTER_TIMEOUT', 3),
    ],
];
