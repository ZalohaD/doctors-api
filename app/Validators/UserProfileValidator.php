<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class UserProfileValidator
{
    public static function validate(array $data){
        return Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'avatar' => 'sometimes|string|max:255',
        ])->validate();
    }
}
