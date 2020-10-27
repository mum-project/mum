<?php

namespace Database\Factories;

use App\ServiceHealthCheck;
use App\SystemService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceHealthCheckFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceHealthCheck::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeThisMonth();
        return [
            'system_service_id' => SystemService::query()
                ->inRandomOrder()
                ->firstOrFail(),
            'output'            => $this->faker->boolean(80) ? 'running' : 'dead',
            'created_at'        => $date,
            'updated_at'        => $date
        ];
    }
}