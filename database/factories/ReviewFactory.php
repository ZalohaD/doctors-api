<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $comments = [
            'Excellent service and professional staff!',
            'Very satisfied with the treatment received.',
            'The doctor was very knowledgeable and helpful.',
            'Clean facilities and friendly atmosphere.',
            'Would definitely recommend to others.',
            'Professional approach and timely service.',
            'Great experience overall.',
            'The staff was very accommodating.',
            'Modern equipment and efficient service.',
            'Felt very comfortable throughout the visit.',
        ];

        return [
            'appointment_id' => Appointment::factory()->completed(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.8)->randomElement($comments),
        ];
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => fake()->randomElement([
                'Outstanding service! Highly recommended.',
                'Exceptional care and professionalism.',
                'Perfect experience from start to finish.',
            ]),
        ]);
    }

    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1, 2),
            'comment' => fake()->randomElement([
                'Service could be improved.',
                'Had to wait longer than expected.',
                'Not satisfied with the experience.',
            ]),
        ]);
    }
}
