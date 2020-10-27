<?php

namespace Database\Factories;

use App\TlsPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class TlsPolicyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TlsPolicy::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'domain' => $this->faker->unique()->domainName,
            'policy' => $this->faker->randomElement([
                'none',
                'may',
                'encrypt',
                'dane',
                'dane-only',
                'verify',
                'secure'
            ])
        ];
    }
}