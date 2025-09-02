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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->company . ' Clinic',
            'address'   => $this->faker->address,
            'latitude'  => $this->faker->latitude(48.0, 52.0),
            'longitude' => $this->faker->longitude(23.0, 38.0),
            'phone'     => $this->faker->phoneNumber,
        ];
    }
}
