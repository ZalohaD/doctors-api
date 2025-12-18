<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class UserValidator
{
    public static function validate(array $data){
        return Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string'
        ])->validate();
    }
}
