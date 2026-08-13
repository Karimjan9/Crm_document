<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
        'url' => env('OPENWEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/weather'),
    ],

    'notifications' => [
        'enabled_channels' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('NOTIFICATION_CHANNELS', 'internal'))
        ))),
        'dispatch' => filter_var(env('NOTIFICATION_DISPATCH', false), FILTER_VALIDATE_BOOL),
        'sms' => [
            'endpoint' => env('SMS_ENDPOINT'),
            'login' => env('SMS_LOGIN'),
            'password' => env('SMS_PASSWORD'),
            'sender' => env('SMS_SENDER'),
            'timeout' => (int) env('SMS_TIMEOUT', 15),
        ],
        'telegram' => [
            'endpoint' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'timeout' => (int) env('TELEGRAM_TIMEOUT', 15),
        ],
        'whatsapp' => [
            'endpoint' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v20.0'),
            'token' => env('WHATSAPP_ACCESS_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'timeout' => (int) env('WHATSAPP_TIMEOUT', 15),
        ],
    ],

    'payment' => [
        'provider' => env('PAYMENT_PROVIDER', 'manual'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    ],

    'ocr' => [
        'endpoint' => env('OCR_ENDPOINT'),
        'token' => env('OCR_TOKEN'),
        'provider' => env('OCR_PROVIDER', 'external'),
        'timeout' => (int) env('OCR_TIMEOUT', 60),
    ],

];
