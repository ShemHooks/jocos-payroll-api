<?php

return [

    'admin' => [

        'username' => env(
            'JOCOS_ADMIN_USERNAME',
            'administrator'
        ),

        'email' => env(
            'JOCOS_ADMIN_EMAIL'
        ),

        'password' => env(
            'JOCOS_ADMIN_PASSWORD'
        ),

    ],

];