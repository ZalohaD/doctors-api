<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Enums\DoctorSpecialization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DoctorService>
 */
class DoctorServiceFactory extends Factory
{
    protected $model = DoctorService::class;

    public function definition(): array
    {
        return [
            'doctor_id'      => Doctor::factory(),
            'clinic_id'      => Clinic::factory(),
            'name'           => $this->faker->sentence(3),
            'specialization' => $this->faker->randomElement(DoctorSpecialization::cases()),
            'price'          => $this->faker->randomFloat(2, 200, 2000),
            'duration'       => $this->faker->numberBetween(15, 120),
            'description'    => $this->faker->paragraph(),
            'is_active'      => $this->faker->boolean(90),
        ];
    }
}
