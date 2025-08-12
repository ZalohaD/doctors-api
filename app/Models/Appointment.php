<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Appointment extends Model
{
    protected $fillable = [
        'clinic_id', 'doctor_id', 'from', 'to', 'first_name', 'last_name', 'status'
    ];

    protected $casts = [
      'from' => 'datetime',
      'to' => 'datetime',
      'status' => AppointmentStatus::class
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Scopes
    public function scopeByStatus($query, AppointmentStatus $status)
    {
        return $query->where('status', $status);
    }

}
