<?php

/* Where the apps can be downloaded (shown on the website). Set APP_IOS_URL when the iOS app is on the App Store. */
return [
    'android' => env('APP_ANDROID_URL', 'https://play.google.com/store/apps/details?id=com.pachavellam.pachavellamofficial'),
    'ios' => env('APP_IOS_URL'),
];
