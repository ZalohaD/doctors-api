<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\DoctorService;
use Illuminate\Support\Collection;

class DoctorDomainRepository
{
    public function create(array $data): Doctor
    {
        return Doctor::create($data);
    }

    public function findByEmail(string $email): ?Doctor
    {
        return Doctor::where('email', $email)->first();
    }

    public function findWithRelations(int $id): ?Doctor
    {
        return Doctor::with(['clinics', 'clinicDoctors', 'appointments.user', 'schedules', 'services'])
            ->find($id);
    }

    public function getAllWithRelations(): Collection
    {
        return Doctor::with(['clinics', 'clinicDoctors', 'schedules'])->get();
    }

    public function update(int $id, array $data): bool
    {
        return Doctor::where('id', $id)->update($data);
    }

    public function getAllClinics(): Collection
    {
        return Clinic::all(['id', 'name']);
    }

    public function attachClinic(int $doctorId, int $clinicId, array $specializations): void
    {
        $doctor = Doctor::find($doctorId);
        $doctor->clinics()->attach($clinicId, [
            'specialization' => json_encode($specializations),
        ]);
    }

    public function createSchedule(int $doctorId, int $clinicId, array $schedule): Schedule
    {
        return Schedule::create([
            'doctor_id' => $doctorId,
            'clinic_id' => $clinicId,
            'type'      => 1,
            'schedule'  => $schedule,
        ]);
    }

    public function deleteSchedule(int $doctorId, int $clinicId, int $type): void
    {
        Schedule::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('type', $type)
            ->delete();
    }

    public function deleteClinicDoctors(int $doctorId, int $clinicId): void
    {
        $doctor = Doctor::find($doctorId);
        $doctor->clinicDoctors()->where('clinic_id', $clinicId)->delete();
    }

    public function createClinicDoctor(int $doctorId, int $clinicId, array $specializations): void
    {
        $doctor = Doctor::find($doctorId);
        $doctor->clinicDoctors()->create([
            'clinic_id' => $clinicId,
            'specialization' => $specializations,
        ]);
    }

    public function getCompletedAppointments(int $doctorId, ?int $userId): Collection
    {
        if (!$userId) {
            return collect();
        }

        return Appointment::where('doctor_id', $doctorId)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->get();
    }

    public function getDoctorReviews(int $doctorId): array
    {
        return Review::whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->with('appointment.user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($review) => [
                'id' => $review->id,
                'comment' => $review->comment,
                'rating' => $review->rating,
                'user_name' => $review->appointment?->user?->name,
                'created_at' => $review->created_at->toDateTimeString(),
            ])
            ->toArray();
    }

    public function findService(int $serviceId): DoctorService
    {
        return DoctorService::findOrFail($serviceId);
    }

    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function updateService(int $serviceId, int $clinicId, array $data): void
    {
        DoctorService::where('id', $serviceId)->update([
            'clinic_id' => $clinicId,
            'name' => $data['name'],
            'specialization' => $data['specialization'],
            'price' => $data['price'],
            'duration' => $data['duration'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function createService(int $doctorId, int $clinicId, array $data): DoctorService
    {
        return DoctorService::create([
            'doctor_id' => $doctorId,
            'clinic_id' => $clinicId,
            'name' => $data['name'],
            'specialization' => $data['specialization'],
            'price' => $data['price'],
            'duration' => $data['duration'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
