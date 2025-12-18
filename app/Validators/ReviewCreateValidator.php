<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class ReviewCreateValidator
{
    public static function validate(array $data){
        return Validator::make($data, [
            'appointment_id' => 'required|integer',
            'comment' => 'required|string',
            'rating' => 'required|integer',
        ])->validate();
    }
}
