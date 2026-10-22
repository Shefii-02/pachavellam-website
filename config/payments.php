<?php

return [

    /*
     | Which gateway new payments use when nobody has switched it in Admissions & Revenue: razorpay | phonepe.
     | An Admin can switch it any time on that page; the choice is kept in the database and wins over this value.
     */
    'default_gateway' => env('PAYMENT_GATEWAY', 'razorpay'),

    /* Platform commission, % of the money kept from each admission (payments minus refunds). Set it in .env: COMMISSION_PERCENT=10 */
    'commission_percent' => (float) env('COMMISSION_PERCENT', 0),

    'currency' => 'INR',

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'base_url' => 'https://api.razorpay.com/v1',
    ],

    'phonepe' => [
        'merchant_id' => env('PHONEPE_MERCHANT_ID'),
        'salt_key' => env('PHONEPE_SALT_KEY'),
        'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
        'env' => env('PHONEPE_ENV', 'sandbox'),   // sandbox | production
        'base_urls' => [
            'sandbox' => 'https://api-preprod.phonepe.com/apis/pg-sandbox',
            'production' => 'https://api.phonepe.com/apis/hermes',
        ],
    ],

    // staff can record money taken outside the gateways
    'manual_methods' => ['cash' => 'Cash', 'upi' => 'UPI / bank transfer (outside the app)', 'bank' => 'Bank deposit', 'unpaid' => 'Not paid yet (admit now, balance due)', 'free' => 'Free / complimentary (fee waived)'],
];
