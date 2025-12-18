<?php

namespace App\Services;

use App\DTO\AppointmentDTO;
use App\DTO\DoctorLoginDTO;
use App\Repositories\DoctorDomainRepository;
use App\DTO\DoctorDTO;
use App\Models\Doctor;
use App\Enums\DoctorSpecialization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoctorDomainService
{
    public function __construct(
        private DoctorDomainRepository $repo
    ) {}

    public function register(DoctorDTO $dto, DoctorLoginDTO $loginDTO): array
    {
        $doctor = $this->repo->create([
            'first_name' => $dto->first_name,
            'last_name'  => $dto->last_name,
            'email'      => $dto->email,
            'phone'      => $dto->phone,
            'address'    => $dto->address,
            'password'   => Hash::make($loginDTO->password),
        ]);

        $this->repo->attachClinic($doctor->id, $dto->clinic_id, $dto->specializations);
        $this->repo->createSchedule($doctor->id, $dto->clinic_id, $dto->available_time);

        $token = $doctor->createToken('auth_token')->plainTextToken;

        return ['doctor' => $doctor->load('clinics', 'schedules'), 'token' => $token];
    }

    public function login(string $email, string $password): array
    {
        $doctor = $this->repo->findByEmail($email);

        if (!$doctor || !Hash::check($password, $doctor->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $doctor->createToken('doctor-auth-token')->plainTextToken;

        return [
            'doctor' => [
                'id' => $doctor->id,
                'first_name' => $doctor->first_name,
                'last_name' => $doctor->last_name,
                'email' => $doctor->email,
            ],
            'token' => $token,
        ];
    }

    public function getFormOptions(): array
    {
        $clinics = $this->repo->getAllClinics();
        $specializations = DoctorSpecialization::options();

        $specializations = array_map(
            fn($k, $v) => ['value' => $k, 'label' => $v],
            array_keys($specializations),
            $specializations
        );

        return [
            'clinics' => $clinics,
            'specializations' => $specializations
        ];
    }

    public function getAllDoctors(): array
    {
        $doctors = $this->repo->getAllWithRelations();

        return $doctors->map(function ($doctor) {
            $specializations = $doctor->clinicDoctors
                ->pluck('specialization')
                ->flatten()
                ->unique()
                ->values()
                ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

            $schedules = $doctor->schedules->pluck('schedule');

            return [
                'id' => $doctor->id,
                'first_name' => $doctor->first_name,
                'last_name' => $doctor->last_name,
                'name' => $doctor->first_name . ' ' . $doctor->last_name,
                'email' => $doctor->email,
                'phone' => $doctor->phone,
                'address' => $doctor->address,
                'available_time' => $schedules,
                'specializations' => $specializations,
                'clinics' => $doctor->clinics->map(fn($clinic) => [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                ]),
            ];
        })->toArray();
    }

    public function getDoctorProfile(int $doctorId, ?int $userId): ?array
    {
        $doctor = $this->repo->findWithRelations($doctorId);

        if (!$doctor) {
            return null;
        }

        $specializations = $this->extractSpecializations($doctor);
        $schedules = $this->extractSchedules($doctor);
        $services = $this->extractServices($doctor);

        $appointments = $this->repo->getCompletedAppointments($doctorId, $userId);
        $canLeaveReview = $appointments->isNotEmpty();

        $reviews = $this->repo->getDoctorReviews($doctorId);

        return [
            'id' => $doctor->id,
            'user_id' => $userId,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'available_time' => $schedules,
            'specializations' => $specializations,
            'services' => $services,
            'clinics' => $doctor->clinics->map(fn($clinic) => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'address' => $clinic->address ?? null,
            ])->toArray(),
            'reviews' => $reviews,
            'can_leave_review' => $canLeaveReview,
            'appointments' => $appointments->map(fn($appointment) => [
                'id' => $appointment->id,
                'from' => $appointment->from->toDateTimeString(),
                'to' => $appointment->to->toDateTimeString(),
            ])->toArray(),
        ];
    }

    public function getDoctorData(Doctor $doctor): array
    {
        $specializations = $this->extractSpecializations($doctor);
        $schedules = $this->extractSchedules($doctor);
        $services = $this->extractServices($doctor);

        return [
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'specializations' => $specializations,
            'available_time' => $schedules,
            'services' => $services,
            'clinics' => $doctor->clinics->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address ?? null,
            ])->toArray(),
        ];
    }

    public function updateDoctorProfile(Doctor $doctor, array $data): array
    {
        $basicFields = collect($data)->only([
            'first_name', 'last_name', 'email', 'phone', 'address'
        ])->toArray();

        if (!empty($basicFields)) {
            $this->repo->update($doctor->id, $basicFields);
        }

        $clinicId = $data['clinic_id'] ?? $doctor->clinics->first()?->id;

        if (!$clinicId) {
            return ['error' => 'Не вказана клініка'];
        }

        if (isset($data['specializations'])) {
            $this->updateSpecializations($doctor->id, $clinicId, $data['specializations']);
        }

        if (isset($data['available_time'])) {
            $this->updateSchedule($doctor->id, $clinicId, $data['available_time']);
        }

        if (isset($data['services'])) {
            $this->updateServices($doctor->id, $clinicId, $data['services']);
        }

        $doctor->refresh();
        $specializations = $this->extractSpecializations($doctor);

        return [
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'specializations' => $specializations,
            'clinics' => $doctor->clinics->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address ?? null,
            ])->toArray(),
        ];
    }

    public function createAppointment(int $userId, array $data): object
    {
        $service = $this->repo->findService($data['service_id']);

        $appointmentDTO = AppointmentDTO::fromArray($data);
        $appointmentDTO->user_id = $userId;
        $appointmentDTO->clinic_id = $service->clinic_id;

        return $this->repo->createAppointment($appointmentDTO->toArray());
    }

    private function extractSpecializations(Doctor $doctor): array
    {
        return $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->values()
            ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec)
            ->toArray();
    }

    private function extractSchedules(Doctor $doctor): array
    {
        return $doctor->schedules
            ->map(fn($schedule) => $schedule->schedule)
            ->flatten(1)
            ->values()
            ->toArray();
    }

    private function extractServices(Doctor $doctor): array
    {
        return $doctor->services
            ->map(function($doctorService) {
                return [
                    'id' => $doctorService->id,
                    'name' => $doctorService->name ?? $doctorService->service->name ?? '',
                    'description' => $doctorService->description ?? $doctorService->service->description ?? '',
                    'price' => $doctorService->price ?? 0,
                    'duration' => $doctorService->duration ?? 30,
                    'specialization' => $doctorService->specialization ?? '',
                    'is_active' => $doctorService->is_active ?? true,
                ];
            })
            ->values()
            ->toArray();
    }

    private function updateSpecializations(int $doctorId, int $clinicId, array $specializations): void
    {
        $this->repo->deleteClinicDoctors($doctorId, $clinicId);

        $specValues = collect($specializations)
            ->map(fn($specLabel) => collect(DoctorSpecialization::cases())
                ->first(fn($case) => $case->label() === $specLabel)?->value
            )
            ->filter()
            ->values()
            ->toArray();

        if (!empty($specValues)) {
            $this->repo->createClinicDoctor($doctorId, $clinicId, $specValues);
        }
    }

    private function updateSchedule(int $doctorId, int $clinicId, array $availableTime): void
    {
        $times = collect($availableTime)
            ->map(fn($t) => [
                'day' => $t['day'],
                'from' => $t['from'],
                'to' => $t['to'],
            ])
            ->values()
            ->toArray();

        $this->repo->deleteSchedule($doctorId, $clinicId, 1);
        $this->repo->createSchedule($doctorId, $clinicId, $times);
    }

    private function updateServices(int $doctorId, int $clinicId, array $services): void
    {
        foreach ($services as $serviceData) {
            if (isset($serviceData['id'])) {
                $this->repo->updateService($serviceData['id'], $clinicId, $serviceData);
            } else {
                $this->repo->createService($doctorId, $clinicId, $serviceData);
            }
        }
    }
}
