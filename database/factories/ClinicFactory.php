<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        $latitude = fake()->latitude(49.0, 51.0);
        $longitude = fake()->longitude(22.0, 40.0);

        return [
            'name' => fake()->company() . ' Medical Center',
            'address' => fake()->address(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'phone' => fake()->phoneNumber(),
        ];
    }

    public function withoutCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}

