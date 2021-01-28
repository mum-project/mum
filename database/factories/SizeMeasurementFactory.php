<?php

namespace Database\Factories;

use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SizeMeasurementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SizeMeasurement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $measurableClass = $this->faker->randomElement([
            Mailbox::class,
            Domain::class
        ]);
        return [
            'measurable_id'   => $measurableClass::all()
                ->random(1)
                ->first()->id,
            'measurable_type' => $measurableClass,
            'size'            => $this->faker->numberBetween(100, 10000),
            'created_at'      => $this->faker->dateTimeBetween(
                Carbon::now()->subYear(), Carbon::now()
            )
        ];
    }
}