<?php

namespace Database\Seeders;

use App\Enums\DoctorSpecialization;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\ClinicDoctor;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        $clinics = Clinic::factory(5)->create();

        $doctors = Doctor::factory(10)->create();

        foreach ($doctors as $doctor) {
            $clinic = $clinics->random();

            ClinicDoctor::factory()->create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinic->id,
                'specialization' => fake()->randomElements(
                    DoctorSpecialization::cases(),
                    rand(1, 3)
                ),
            ]);
        }

        foreach (ClinicDoctor::all() as $pair) {
            if (rand(1, 100) <= 70) {
                $appointmentsCount = rand(1, 3);
                Appointment::factory($appointmentsCount)
                    ->completed()
                    ->has(Review::factory())
                    ->create([
                        'clinic_id' => $pair->clinic_id,
                        'doctor_id' => $pair->doctor_id,
                        'user_id' => $users->random()->id,
                    ]);
            }
        }
        DoctorService::factory(10)->create();
        Schedule::factory(10)->create();
    }
}
