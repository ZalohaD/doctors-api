<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    protected $fillable = ['appointment_id', 'amount', 'status'];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PaymentStatus::class,
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
