<?php

namespace App\Validators;

use Illuminate\Validation\Rules\Enum;
use App\Enums\DoctorSpecialization;
use Illuminate\Support\Facades\Validator;

class DoctorValidator
{
    public static function validate(array $data): array
    {
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


    public static function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'first_name' => 'sometimes|string',
            'last_name'  => 'sometimes|string',
            'email'      => 'sometimes|string|email',
            'phone'      => 'sometimes|string',
            'address'    => 'sometimes|string',
            'clinic_id'  => 'sometimes|exists:clinics,id',
            'specializations' => 'sometimes|array',
            'available_time' => 'sometimes|array|min:1',
            'available_time.*.day'  => 'sometimes|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'available_time.*.from' => 'sometimes|date_format:H:i',
            'available_time.*.to'   => 'sometimes|date_format:H:i',
            'services' => 'sometimes|array',
            'services.*.id' => 'sometimes|integer|exists:doctor_services,id',
            'services.*.name' => 'required_with:services|string|max:255',
            'services.*.specialization' => 'required_with:services|string',
            'services.*.price' => 'required_with:services|numeric|min:0',
            'services.*.duration' => 'sometimes|integer|min:1',
            'services.*.description' => 'sometimes|string|nullable',
            'services.*.is_active' => 'sometimes|boolean',
        ])->validate();
    }

    public static function validateAppointment(array $data): array
    {
        return Validator::make($data, [
            'doctor_id'   => 'required|integer|exists:doctors,id',
            'service_id'  => 'required|integer|exists:doctor_services,id',
            'from'        => 'required|date_format:Y-m-d H:i',
            'to'          => 'required|date_format:Y-m-d H:i|after:from',
            'firstname'   => 'required|string|max:255',
            'lastname'    => 'required|string|max:255',
            'status'      => 'required|integer',
        ])->validate();
    }


    public static function validateLogin(array $data): array
    {
        return Validator::make($data, [
            'email'    => 'required|email',
            'password' => 'required|string',
        ])->validate();
    }
}
