<?php

use App\Domain;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(App\Mailbox::class, function (Faker $faker) {
    $domain = Domain::all()->random(1)->first();
    $username = $faker->unique()->userName;
    return [
        'local_part'        => $username,
        'password'          => Hash::make('secret'),
        'name'              => $faker->name,
        'domain_id'         => $domain->id,
        'alternative_email' => $faker->boolean ? $faker->safeEmail : null,
        'quota'             => null,
        'homedir'           => getHomedirForMailbox($username, $domain->domain),
        'maildir'           => getMaildirForMailbox($username, $domain->domain),
        'is_super_admin'    => $faker->boolean(20),
        'send_only'         => $faker->boolean(20),
        'active'            => $faker->boolean(80)
    ];
});
