<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Please note:  SHA2 algorithms are generally not recommended for hashing
    |               your passwords; however, we included them to support older
    |               versions of Postfix and Dovecot, which don't have built-in
    |               support for bcrypt or argon.
    |
    | Supported: "sha256", "sha512", "bcrypt", "argon"
    | Strongly recommended: "bcrypt", "argon"
    |
    */

    'driver' => env('HASHING_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'bcrypt' => [
        'rounds' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'argon' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | SHA2 options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using one of SHA2's algorithms. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'sha256' => [
        'rounds' => 5000
    ],

    'sha512' => [
        'rounds' => 5000
    ]
];
