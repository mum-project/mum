<?php

namespace Database\Factories;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\ShellCommandIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShellCommandIntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShellCommandIntegration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->boolean ? $this->faker->sentence : null,
            'active'      => $this->faker->boolean(80),
            'model_class' => $this->faker->randomElement([
                Domain::class,
                Mailbox::class,
                Alias::class
            ]),
            'event_type'  => $this->faker->randomElement([
                'created',
                'updated',
                'deleted'
            ]),
            'type'        => ShellCommandIntegration::class,
            'value'       => '01'
        ];
    }
}