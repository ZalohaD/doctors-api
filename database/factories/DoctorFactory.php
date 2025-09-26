<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'photo' => fake()->imageUrl(300, 400, 'people'),
            'social_links' => json_encode([
                'facebook' => fake()->optional()->url(),
                'linkedin' => fake()->optional()->url(),
                'twitter' => fake()->optional()->url(),
            ]),
            'password' => Hash::make('password'),
        ];
    }
}
