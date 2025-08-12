<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DoctorSpecialization;

class ClinicDoctor extends Model
{
    protected $fillable = ['clinic_id', 'doctor_id', 'specialization'];

    protected $casts = [
        'specialization' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getSpecializationLabelsAttribute()
    {
        return collect($this->specialization)
            ->map(fn($spec) => DoctorSpecialization::from($spec)->label())
            ->toArray();
    }
}
