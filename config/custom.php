<?php

return [

    'basic_auth_username' => env('BASIC_AUTH_USERNAME', null),
    'basic_auth_password' => env('BASIC_AUTH_PASSWORD', null),
    'sms_api_token' => env('SMS_API_TOKEN', null),
    'sms_api_url' => env('SMS_API_URL', null),
    'currency' => env('CURRENCY', null),

    'payment' => [
        "callbackUrl" => env('PAYMENT_CALLBACK_URL', null),
        "cancellationUrl" => env('PAYMENT_CANCELLATION_URL', null),
        "returnUrl" =>  env('PAYMENT_RETURN_URL', null),
        "logo" => env('PAYMENT_LOGO_URL', null),
    ],

    'admin_permissions' => [
            [
                'major' => 'manage other admins',
                'subs' => [
                    'create admin',
                    'view all admins'
                ]
            ],
            [
                'major' => 'manage customer kyc',
                'subs' => [
                    'view customer kyc',
                    'edit customer kyc'
                ]
            ],
            [
                'major' => 'manage recovery officers',
                'subs' => [

                ]
            ],
            [
                'major' => 'manage roles and permissions',
                'subs' => [
                    'view permissions',
                    'assign permissions'
                ]
            ],
            [
                'major' => 'manage loans applications',
                'subs' => [
                    'access to pending loans',
                    'access to loan stage 0',
                ]
            ],
            [
                'major' => 'manage configurations',
                'subs' => [
                    'configure customer on-boarding fields',
                    'loan application configuration',
                    'configuration loan application parameters'
                ]
            ],
            [
                'major' => 'manage agents',
                'subs' => [
                    'assign agents',
                    'view agents',
                ]
            ],
            [
                'major' => 'manage customer support',
                'subs' => [

                ]
            ],

    ]
];
