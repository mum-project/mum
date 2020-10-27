<?php

namespace Database\Factories;

use App\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'domain'        => $this->faker->unique()->domainName,
            'description'   => $this->faker->sentence,
            'quota'         => null,
            'max_quota'     => null,
            'max_aliases'   => null,
            'max_mailboxes' => null
        ];
    }
}