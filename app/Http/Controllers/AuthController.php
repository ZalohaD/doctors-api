<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialization;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use \Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request){
       $data = $request->validate([
           'name' => 'required|string',
           'email' => 'required|string|email|unique:users',
           'password' => 'required|string|confirmed',
           'password_confirmation' => 'required|string'
       ]);

      $user = User::create([
          'name' => $data['name'],
           'email' => $data['email'],
           'password' => Hash::make($data['password']),
       ]);
       $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json(['token' => $token], 200);
    }

    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();

        if(!$user || !Hash::check($data['password'], $user->password)){
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token]);

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleAuth()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(10)),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            $token = $user->createToken('api_token')->plainTextToken;

            return redirect()->away(
                'http://localhost:5173/auth/callback?token=' . $token . '&user=' . urlencode(json_encode([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]))
            );

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->away(
                'http://localhost:5173/login?error=' . urlencode($e->getMessage())
            );
        }
    }






    public function registerDoctor(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|string|email|unique:doctors,email',
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
        ]);

        try {
            $doctor = Doctor::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'address'    => $data['address'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
            ]);

            $doctor->clinics()->attach($data['clinic_id'], [
                'specialization' => json_encode($data['specialization']),
            ]);



            Schedule::create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $data['clinic_id'],
                'type'      => 1,
                'schedule'  => $data['available_time'],
            ]);


            $token = $doctor->createToken('auth_token')->plainTextToken;

            return response()->json([
                'doctor' => $doctor->load('clinics', 'schedules'),
                'token'  => $token,
            ]);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'general' => $exception->getMessage(),
            ]);
        }
    }



    public function loginDoctor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $doctor = Doctor::where('email', $request->email)->first();

        if (!$doctor || !Hash::check($request->password, $doctor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $doctor->createToken('doctor-auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'doctor' => [
                'id' => $doctor->id,
                'first_name' => $doctor->first_name,
                'last_name' => $doctor->last_name,
                'email' => $doctor->email,
            ]
        ]);
    }


}
