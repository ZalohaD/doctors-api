<?php

namespace App\Models;

use App\Enums\DoctorSpecialization;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'password',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_doctors')
            ->withPivot('specialization')
            ->withTimestamps();
    }

    public function clinicDoctors(): HasMany
    {
        return $this->hasMany(ClinicDoctor::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getAllSpecializations(): array
    {
        $specializations = [];
        foreach ($this->clinicDoctors as $clinicDoctor) {
            $specializations = array_merge($specializations, $clinicDoctor->specialization ?? []);
        }
        return array_unique($specializations);
    }

    // Отримуємо підписи всіх спеціалізацій
    public function getAllSpecializationLabels(): array
    {
        return array_map(
            fn($value) => DoctorSpecialization::from($value)->label(),
            $this->getAllSpecializations()
        );
    }

    // Перевіряємо чи має лікар певну спеціалізацію
    public function hasSpecialization(DoctorSpecialization $specialization): bool
    {
        return in_array($specialization->value, $this->getAllSpecializations());
    }

    // Отримуємо спеціалізації лікаря в певній клініці
    public function getSpecializationsInClinic(int $clinicId): array
    {
        $clinicDoctor = $this->clinicDoctors()->where('clinic_id', $clinicId)->first();
        return $clinicDoctor ? $clinicDoctor->specialization ?? [] : [];
    }
}
