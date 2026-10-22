<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Admin Credentials
    |--------------------------------------------------------------------------
    |
    | Service-account JSON for server-side Firebase Admin SDK calls (push
    | notifications). This is NOT the same as the FIREBASE_* web/JS SDK
    | config already in .env — download it from Firebase Console ->
    | Project Settings -> Service Accounts -> Generate new private key,
    | for the same project the Flutter app initializes with (see
    | mobile_app/lib/firebase_options.dart), and place it at the path below
    | (git-ignored). Until that file exists, push sending no-ops safely.
    |
    */

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),

    'project_id' => env('FIREBASE_PROJECT_ID'),

    // Browser push (web). Needs the public web key pair from Firebase Console -> Cloud Messaging -> Web configuration.
    'web' => [
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
        'config' => [
            'apiKey' => env('FIREBASE_API_KEY'), 'authDomain' => env('FIREBASE_AUTH_DOMAIN'), 'projectId' => env('FIREBASE_PROJECT_ID'),
            'storageBucket' => env('FIREBASE_STORAGE_BUCKET'), 'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'), 'appId' => env('FIREBASE_APP_ID'),
        ],
    ],

];
