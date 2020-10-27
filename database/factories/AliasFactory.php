<?php

namespace Database\Factories;

use App\Alias;
use App\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

class AliasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Alias::class;

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

        return [
            'local_part'  => $this->faker->unique()->username,
            'description' => $this->faker->boolean ? $this->faker->sentence : null,
            'domain_id'   => $domain->id
        ];
    }
}