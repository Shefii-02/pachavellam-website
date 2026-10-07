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
          
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'), // log | smsgatewayhub | msg91
        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'template_id' => env('MSG91_TEMPLATE_ID'),
        ],
    ],

    'smsgatewayhub' => [
        'key'         => env('SMSGATEWAYHUB_API_KEY'),
        'senderid'    => env('SMSGATEWAYHUB_SENDERID'),
        'entity_id'   => env('SMSGATEWAYHUB_ENTITY_ID'),
        'template_id' => env('SMSGATEWAYHUB_TEMPLATE_ID'),
        // Must match the DLT-registered template text exactly. {otp} and {minutes} are substituted.
        'message'     => env('SMSGATEWAYHUB_MESSAGE', '{otp} is your One Time Password (OTP) for login/signup at Pachavellam Education. This OTP will only be valid for {minutes} minutes. Do not share anyone'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_MODE', false) ? 'http://127.0.0.1:8000/auth/google/callback' : 'https://www.pachavellam.com/auth/google/callback',
    ],

    'chat' => [
        'internal_secret' => env('CHAT_PUSH_INTERNAL_SECRET'),
    ],

];
