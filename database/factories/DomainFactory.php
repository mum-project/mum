<?php

use Faker\Generator as Faker;

$factory->define(App\Domain::class, function (Faker $faker) {
    return [
        'domain'        => $faker->unique()->domainName,
        'description'   => $faker->sentence,
        'quota'         => null,
        'max_quota'     => null,
        'max_aliases'   => null,
        'max_mailboxes' => null
    ];
});
