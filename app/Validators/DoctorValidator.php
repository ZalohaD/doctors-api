<?php

namespace App\Validators;

use Illuminate\Validation\Rules\Enum;
use App\Enums\DoctorSpecialization;
use Illuminate\Support\Facades\Validator;
class DoctorValidator
{
    public static function validate(array $data){
        return Validator::make($data, [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:doctors,email',
            'phone'      => 'required|string',
            'address'    => 'required|string',
            'password'   => 'required|string|confirmed',
            'clinic_id'  => 'required|exists:clinics,id',
            'specialization' => 'required|array|min:1',
            'specialization.*' => [new Enum(DoctorSpecialization::class)],
            'available_time' => 'required|array|min:1',
            'available_time.*.day'  => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'available_time.*.from' => 'required|date_format:H:i',
            'available_time.*.to'   => 'required|date_format:H:i',
        ])->validate();
    }
}
