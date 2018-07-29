<?php

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\ShellCommandIntegration;
use Faker\Generator as Faker;

$factory->define(App\ShellCommandIntegration::class, function (Faker $faker) {
    return [
        'name'        => $faker->boolean ? $faker->sentence : null,
        'active'      => $faker->boolean(80),
        'model_class' => $faker->randomElement([
            Domain::class,
            Mailbox::class,
            Alias::class
        ]),
        'event_type'  => $faker->randomElement([
            'created',
            'updated',
            'deleted'
        ]),
        'type'        => ShellCommandIntegration::class,
        'value'       => '01'
    ];
});
