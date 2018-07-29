<?php

use App\Domain;
use App\Mailbox;
use App\Alias;
use App\WebHookIntegration;
use Faker\Generator as Faker;

$factory->define(App\WebHookIntegration::class, function (Faker $faker) {
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
        'type'        => WebHookIntegration::class,
        'value'       => $faker->url
    ];
});
