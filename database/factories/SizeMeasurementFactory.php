<?php

use App\Domain;
use App\Mailbox;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\SizeMeasurement::class, function (Faker $faker) {
    $measurableClass = $faker->randomElement([
        Mailbox::class,
        Domain::class
    ]);
    return [
        'measurable_id'   => $measurableClass::all()
            ->random(1)
            ->first()->id,
        'measurable_type' => $measurableClass,
        'size'            => $faker->numberBetween(100, 10000),
        'created_at'      => $faker->dateTimeBetween(Carbon::now()->subYear(), Carbon::now())
    ];
});
