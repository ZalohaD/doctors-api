<?php

namespace Database\Factories;

use App\Enums\DoctorSpecialization;
use App\Models\Clinic;
use App\Models\ClinicDoctor;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicDoctor>
 */
class ClinicDoctorFactory extends Factory
{
    protected $model = ClinicDoctor::class;

    public function definition(): array
    {

        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'specialization' =>
                fake()->randomElements(
                    DoctorSpecialization::cases(),
                    fake()->numberBetween(1, 3)
            ),
        ];
    }
}
