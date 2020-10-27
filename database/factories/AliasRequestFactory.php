<?php

namespace Database\Factories;

use App\AliasRequest;
use App\Domain;
use App\Mailbox;
use Illuminate\Database\Eloquent\Factories\Factory;

class AliasRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AliasRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $domain = Domain::all()
            ->random(1)
            ->first();

        $mailbox = Mailbox::all()
            ->random(1)
            ->first();

        return [
            'mailbox_id' => $mailbox->id,
            'domain_id'  => $domain->id,
            'local_part' => $this->faker->unique()->username,
            'status'     => $this->faker->randomElement(['open', 'dismissed'])
        ];
    }
}