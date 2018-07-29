<?php

use App\Domain;
use Faker\Generator as Faker;

$factory->define(App\Alias::class, function (Faker $faker) {
    $domain = Domain::all()
        ->random(1)
        ->first();

    return [
        'local_part'  => $faker->unique()->username,
        'description' => $faker->boolean ? $faker->sentence : null,
        'domain_id'   => $domain->id
    ];
});
