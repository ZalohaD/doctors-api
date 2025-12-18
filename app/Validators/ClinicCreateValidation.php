<?php

namespace App\Validators;
use Illuminate\Support\Facades\Validator;

class ClinicCreateValidation
{
    public static function validate (array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'phone' => 'required|string|max:255',
        ])->validate();

    }
}
