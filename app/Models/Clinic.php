<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
   use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'phone',
    ];

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'clinic_doctors')
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

    public function payments(): HasMany
    {
        return $this->hasManyThrough(Payment::class, Appointment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasManyThrough(Review::class, Appointment::class);
    }

}
