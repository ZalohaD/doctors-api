<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $from = fake()->dateTimeBetween('now', '+1 month');
        $to = (clone $from)->modify('+' . fake()->numberBetween(30, 120) . ' minutes');

        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'user_id' => User::factory(),
            'from' => $from,
            'to' => $to,
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'status' => fake()->numberBetween(1, 4),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 3,
            'from' => fake()->dateTimeBetween('-1 month', 'now'),
            'to' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 4,
        ]);
    }
}
