<?php

namespace App\Models;

use App\Enums\DoctorSpecialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorService extends Model
{
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'name',
        'specialization',
        'price',
        'duration',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_active' => 'boolean',
        'specialization' => DoctorSpecialization::class,
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
