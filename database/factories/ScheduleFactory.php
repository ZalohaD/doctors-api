<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'type'       => $this->faker->randomElement(ScheduleType::cases()), // enum тип
            'clinic_id'  => Clinic::factory(),
            'doctor_id'  => Doctor::factory(),
            'schedule'   => $this->generateSchedule(),
        ];
    }

    protected function generateSchedule(): array
    {
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

        $schedule = [];
        $workingDays = $this->faker->randomElements($days, $this->faker->numberBetween(1, 5));

        foreach ($workingDays as $day) {
            $startHour = $this->faker->numberBetween(8, 14);
            $endHour = $this->faker->numberBetween($startHour + 1, 20);

            $schedule[] = [
                'day'  => $day,
                'from' => sprintf("%02d:00", $startHour),
                'to'   => sprintf("%02d:00", $endHour),
            ];
        }

        return $schedule;
    }
}
