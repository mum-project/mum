<?php

use Faker\Generator as Faker;

$factory->define(App\SystemService::class, function (Faker $faker) {
    $selectedService = $faker->unique()->words(5, true);

    return [
        'service' => strtolower($selectedService),
        'name'    => $selectedService
    ];
});
