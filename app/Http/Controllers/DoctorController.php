<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialization;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class DoctorController extends Controller
{
    public function formOptions()
    {
        $clinics = Clinic::all(["id", "name"]);
        $specializations = DoctorSpecialization::options();

        return response()->json([
            "clinics" => $clinics,
            "specializations" => $specializations
        ]);
    }

    public function index()
    {
        $doctors = Doctor::with(['clinics', 'clinicDoctors'])->get();

        $doctors = $doctors->map(function ($doctor) {
            $specializations = $doctor->clinicDoctors->pluck('specialization')->flatten()->unique()->values();

            $specializations = $specializations->map(function ($spec) {
                return DoctorSpecialization::tryFrom($spec)?->label() ?? $spec;
            });

            return [
                'id' => $doctor->id,
                'first_name' => $doctor->first_name,
                'last_name' => $doctor->last_name,
                'name' => $doctor->first_name . ' ' . $doctor->last_name,
                'email' => $doctor->email,
                'phone' => $doctor->phone,
                'address' => $doctor->address,
                'available_time' => json_decode($doctor->available_time),

                'specializations' => $specializations,
                'clinics' => $doctor->clinics->map(function ($clinic) {
                    return [
                        'id' => $clinic->id,
                        'name' => $clinic->name,
                    ];
                }),
            ];
        });

        return response()->json($doctors);
    }

    public function profile($id)
    {
        $doctor = Doctor::with(['clinics', 'clinicDoctors'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $specializations = $doctor->clinicDoctors->pluck('specialization')->flatten()->unique()->values();
        $specializations = $specializations->map(function ($spec) {
            return DoctorSpecialization::tryFrom($spec)?->label() ?? $spec;
        });

        return response()->json([
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'available_time' => json_decode($doctor->available_time),
            'specializations' => $specializations,
            'clinics' => $doctor->clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'address' => $clinic->address ?? null,
                ];
            }),
        ]);
    }

    public function me(Request $request)
    {
        $doctor = auth()->user();
        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found',
                'debug' => [
                    'bearerToken' => $request->bearerToken(),
                    'authDoctorUser' => auth('doctor')->user(),
                    'authDefaultUser' => auth()->user(),
                ]
            ], 404);
        }

        $doctor->load(['clinics', 'clinicDoctors']);

        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->map(fn($spec) => \App\Enums\DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

        return response()->json([
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'specializations' => $specializations,
            'clinics' => $doctor->clinics->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address ?? null,
            ]),
        ]);
    }

}
