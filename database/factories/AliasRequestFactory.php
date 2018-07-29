<?php

use Faker\Generator as Faker;
use App\Domain;
use App\Mailbox;

$factory->define(App\AliasRequest::class, function (Faker $faker) {
    $domain = Domain::all()
        ->random(1)
        ->first();

    $mailbox = Mailbox::all()
        ->random(1)
        ->first();

    return [
        'mailbox_id' => $mailbox->id,
        'domain_id' => $domain->id,
        'local_part' => $faker->unique()->username,
        'status' => $faker->randomElement(['open', 'dismissed'])
    ];
});
