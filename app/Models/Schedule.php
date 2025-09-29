<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ScheduleType;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'type', 'clinic_id', 'doctor_id', 'schedule'
    ];

    protected $casts = [
        'type' => ScheduleType::class,
        'schedule' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
