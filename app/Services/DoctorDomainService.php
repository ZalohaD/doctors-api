<?php

namespace App\Services;

use App\Repositories\DoctorDomainRepository;
use App\DTO\DoctorDTO;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoctorDomainService {
    public function __construct(private DoctorDomainRepository $repo) {}

    public function register(DoctorDTO $dto): array {
        $doctor = $this->repo->create([
            'first_name' => $dto->first_name,
            'last_name'  => $dto->last_name,
            'email'      => $dto->email,
            'phone'      => $dto->phone,
            'address'    => $dto->address,
            'password'   => Hash::make($dto->password),
        ]);

        $doctor->clinics()->attach($dto->clinic_id, [
            'specialization' => json_encode($dto->specialization),
        ]);

        Schedule::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $dto->clinic_id,
            'type'      => 1,
            'schedule'  => $dto->available_time,
        ]);

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
}
