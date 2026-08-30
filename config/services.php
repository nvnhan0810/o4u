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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'odoo_tenant' => [
        'base_url' => rtrim((string) env('ODOO_TENANT_BASE_URL', ''), '/'),
        'registration_url' => env(
            'ODOO_TENANT_REGISTRATION_URL',
            rtrim((string) env('ODOO_TENANT_BASE_URL', ''), '/').'/api/tenant/public/register',
        ),
        'registration_key' => env('ODOO_TENANT_REGISTRATION_KEY'),
        'daily_cap' => (int) env('ODOO_TENANT_REGISTRATION_DAILY_CAP', 50),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        // Local/tests only — never enable in production.
        'bypass' => (bool) env('TURNSTILE_BYPASS', false),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'registration_chat_id' => env(
            'TELEGRAM_REGISTRATION_CHAT_ID',
            env('TELEGRAM_CHAT_ID'),
        ),
    ],

];
