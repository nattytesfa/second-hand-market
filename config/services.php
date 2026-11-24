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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sms' => [
        // "log" is used for local development and automated tests.
        'provider' => env('SMS_PROVIDER', 'log'),

        // AfroMessage / AfroSMS (official API documented at afromessage.com/developers).
        'afrosms' => [
            // AFROSMS_API_KEY is accepted as a backwards-compatible alias for the token.
            'token' => env('AFROSMS_TOKEN', env('AFROSMS_API_KEY')),
            'identifier_id' => env('AFROSMS_IDENTIFIER_ID'),
            'sender' => env('AFROSMS_SENDER_NAME', env('AFROSMS_SENDER_ID')),
            'api_base' => env('AFROSMS_API_BASE', 'https://api.afromessage.com/api'),
            'endpoint' => env('AFROSMS_ENDPOINT', 'https://api.afromessage.com/api/send'),
            'callback' => env('AFROSMS_CALLBACK_URL'),
        ],
    ],

];
