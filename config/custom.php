<?php

return [

    'basic_auth_username' => env('BASIC_AUTH_USERNAME', null),
    'basic_auth_password' => env('BASIC_AUTH_PASSWORD', null),

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
                    'view permissions',
                    'assign permissions'
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

                ]
            ],
            [
                'major' => 'manage settings',
                'subs' => [

                ]
            ],
            [
                'major' => 'manage customer support',
                'subs' => [

                ]
            ],

    ]
];
