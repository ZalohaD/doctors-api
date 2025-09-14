<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth('sanctum')->user();
        if (!$user){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return response()->json($user);
    }

    public function edit(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'avatar' => 'sometimes|string|max:255',
        ]);

        $user = User::update($validatedData);
        return response()->json($user);

    }

    public function appointments()
    {
        $user = auth('sanctum')->user();

        $appointments = $user->appointments()->get();

        return response()->json($appointments);

    }


}
