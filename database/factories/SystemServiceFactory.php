<?php

namespace Database\Factories;

use App\SystemService;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SystemService::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $selectedService = $this->faker->unique()->words(5, true);

        return [
            'service' => strtolower($selectedService),
            'name'    => $selectedService
        ];
    }
}