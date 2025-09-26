<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'clinic_id', 'doctor_id', 'user_id','from', 'to', 'firstname', 'lastname', 'status'
    ];

    protected $casts = [
      'from' => 'datetime',
      'to' => 'datetime',
      'status' => AppointmentStatus::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
        return $this->hasOne(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }




}
