<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
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

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_PRIVATE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'dwolla' => [
        'client_id' => env('DWOLLA_PUBLIC_KEY'),
        'secret' => env('DWOLLA_PRIVATE_KEY'),
        'env' => env('DWOLLA_ENV'), // sandbox or production
        'js' => [
            'env' => env('DWOLLA_JS_ENV'), // sandbox or production
        ],
        'webhook_secret' => env('DWOLLA_WEBHOOK_SECRET'),
        'master' => env('DWOLLA_MASTER'),
        'verification_document_size' => env('DWOLLA_VERIFICATION_DOCUMENT_SIZE_LIMIT', 10240)
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'is_live' => env('PAYPA_IS_LIVE', FALSE)
    ],

    'usag' => [
        'env' => env('USAG_ENV', 'dev'),
        'require_key' => env('USAG_REQUIRE_KEY', false),
        'webhook_key' => env('USAG_WEBHOOK_KEY', ''),
        'log_payloads' => env('USAG_LOG_PAYLOADS', false),
    ],

    'usaigc' => [
        'env' => env('USAIGC_ENV', 'dev'),
    ],
];
