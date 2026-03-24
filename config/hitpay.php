<?php

$defaultApiUrl = env('HITPAY_MODE', 'sandbox') === 'live'
    ? 'https://api.hit-pay.com/v1'
    : 'https://api.sandbox.hit-pay.com/v1';

return [
    'mode' => env('HITPAY_MODE', 'sandbox'),
    'api_url' => rtrim(env('HITPAY_API_URL', $defaultApiUrl), '/'),
    'api_key' => env('HITPAY_API_KEY'),
    'currency' => strtoupper(env('HITPAY_CURRENCY', 'PHP')),
    'redirect_url' => env('HITPAY_REDIRECT_URL', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/payments/redirect'),
    'webhook_url' => env('HITPAY_WEBHOOK_URL', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/payments/hitpay/webhook'),
    'webhook_salt' => env('HITPAY_WEBHOOK_SALT', env('HITPAY_SALT')),
    'business_slug' => env('HITPAY_BUSINESS_SLUG'),
    'payment_method_id' => (int) env('HITPAY_PAYMENT_METHOD_ID', 5),
    'payment_methods' => array_values(array_filter(array_map('trim', explode(',', (string) env('HITPAY_PAYMENT_METHODS', ''))))),
    'send_email' => env('HITPAY_SEND_EMAIL', false),
    'send_sms' => env('HITPAY_SEND_SMS', false),
];
