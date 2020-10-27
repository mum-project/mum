<?php

namespace Database\Factories;

use App\Domain;
use App\Mailbox;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class MailboxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mailbox::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $domain = Domain::all()->random(1)->first();
        $username = $this->faker->unique()->userName;
        return [
            'local_part'        => $username,
            'password'          => Hash::make('secret'),
            'name'              => $this->faker->name,
            'domain_id'         => $domain->id,
            'alternative_email' => $this->faker->boolean ? $this->faker->safeEmail : null,
            'quota'             => null,
            'homedir'           => getHomedirForMailbox($username, $domain->domain),
            'maildir'           => getMaildirForMailbox($username, $domain->domain),
            'is_super_admin'    => $this->faker->boolean(20),
            'send_only'         => $this->faker->boolean(20),
            'active'            => $this->faker->boolean(80)
        ];
    }
}