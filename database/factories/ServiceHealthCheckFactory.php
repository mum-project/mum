<?php

use App\SystemService;
use Faker\Generator as Faker;

$factory->define(App\ServiceHealthCheck::class, function (Faker $faker) {
    $date = $faker->dateTimeThisMonth();
    return [
        'system_service_id' => SystemService::query()
            ->inRandomOrder()
            ->firstOrFail(),
        'output'            => $faker->boolean(80) ? 'running' : 'dead',
        'created_at'        => $date,
        'updated_at'        => $date
    ];
});
