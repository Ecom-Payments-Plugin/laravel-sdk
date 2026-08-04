<?php

return [
    'api_token' => env('ECOM_API_TOKEN'),
    'merchant_id' => env('ECOM_MERCHANT_ID'),
    'environment' => env('ECOM_ENVIRONMENT', 'sandbox'),
    'webhook_secret' => env('ECOM_WEBHOOK_SECRET'),
];
