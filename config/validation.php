<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local Part Validation
    |--------------------------------------------------------------------------
    |
    | Here you can configure whether user input for local parts should be
    | validated with the PHP filter_var() function using the
    | FILTER_VALIDATE_EMAIL flag.
    | If 'unicode' is true, the flag FILTER_FLAG_EMAIL_UNICODE will be added.
    |
    */

    'local_part' => [
        'enabled' => env('VALIDATE_LOCAL_PART', true),
        'unicode' => env('ALLOW_UNICODE_LOCAL_PART', true)
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Validation
    |--------------------------------------------------------------------------
    |
    | Here you can configure whether domains should be validated with the
    | PHP filter_var() function using the FILTER_VALIDATE_DOMAIN flag.
    | If 'hostname' is true, the flag FILTER_FLAG_HOSTNAME will be added.
    |
    */

    'domain' => [
        'enabled' => env('VALIDATE_DOMAIN', true),
        'hostname' => env('VALIDATE_DOMAIN_AS_HOSTNAME', false)
    ]
];
