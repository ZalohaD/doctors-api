<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ScheduleType;

class Schedule extends Model
{
    protected $fillable = [
        'type', 'clinic_id', 'doctor_id', 'from', 'to'
    ];

    protected $casts = [
        'type' => ScheduleType::class,
        'from' => 'datetime',
        'to' => 'datetime',
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
