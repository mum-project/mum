<?php

use Faker\Generator as Faker;

$factory->define(App\TlsPolicy::class, function (Faker $faker) {
    return [
        'domain' => $faker->unique()->domainName,
        'policy' => $faker->randomElement([
            'none',
            'may',
            'encrypt',
            'dane',
            'dane-only',
            'verify',
            'secure'
        ])
    ];
});
