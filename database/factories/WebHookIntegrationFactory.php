<?php

namespace Database\Factories;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\WebHookIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebHookIntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WebHookIntegration::class;

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
            'type'        => WebHookIntegration::class,
            'value'       => $this->faker->url
        ];
    }
}