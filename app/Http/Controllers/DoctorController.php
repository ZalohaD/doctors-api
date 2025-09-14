<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialization;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\Schedule;
use Illuminate\Http\Request;

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
            $schedules = $doctor->schedules->pluck('schedule');

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
                'available_time' => $schedules,

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
        $doctor = Doctor::with(['clinics', 'clinicDoctors', 'appointments.user'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->values()
            ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

        $schedules = $doctor->schedules
            ->map(fn($schedule) => $schedule->schedule)
            ->flatten(1)
            ->values();

        $userId = auth('sanctum')->id();

        $appointments = $doctor->appointments()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->get()
            ->map(fn($appointment) => [
                'id' => $appointment->id,
                'from' => $appointment->from->toDateTimeString(),
                'to' => $appointment->to->toDateTimeString(),
            ]);

        $canLeaveReview = $appointments->isNotEmpty();

        $reviews = Review::whereHas('appointment', fn($q) => $q->where('doctor_id', $doctor->id))
            ->with('appointment.user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($review) => [
                'id' => $review->id,
                'comment' => $review->comment,
                'rating' => $review->rating,
                'user_name' => $review->appointment?->user?->name,
                'created_at' => $review->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'id' => $doctor->id,
            'user_id' => $userId,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'available_time' => $schedules,
            'specializations' => $specializations,
            'clinics' => $doctor->clinics->map(fn($clinic) => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'address' => $clinic->address ?? null,
            ]),
            'reviews' => $reviews,
            'can_leave_review' => $canLeaveReview,
            'appointments' => $appointments,
        ]);
    }
    public function me(Request $request)
    {
        $doctor = $request->user();
        if (!$doctor) {
            return response()->json([
                'debug' => [
                    'bearerToken' => $request->bearerToken(),
                    'authDoctorUser' => auth('doctor')->user(),
                    'authDefaultUser' => $request->user(),
                ]
            ], 404);
        }

        $doctor->load(['clinics', 'clinicDoctors']);

        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->map(fn($spec) => \App\Enums\DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

        $schedules = $doctor->schedules->map(function($schedule) {
            return $schedule->schedule;
        })->flatten(1)->values();

        return response()->json([
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'name' => $doctor->first_name . ' ' . $doctor->last_name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'address' => $doctor->address,
            'specializations' => $specializations,
            'available_time' => $schedules,
            'clinics' => $doctor->clinics->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address ?? null,
            ]),
        ]);
    }


    public function edit(Request $request)
    {
        $doctor = $request->user();
        if (!$doctor) {
            return response()->json([
                'debug' => [
                    'bearerToken' => $request->bearerToken(),
                    'authDoctorUser' => auth('doctor')->user(),
                    'authDefaultUser' => $request->user(),
                ]
            ]);
        }


        $validatedData = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name'  => 'sometimes|string',
            'email'      => 'sometimes|string|email',
            'phone'      => 'sometimes|string',
            'address'    => 'sometimes|string',
            'available_time' => 'sometimes|array|min:1',
            'available_time.*.day'  => 'sometimes|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'available_time.*.from' => 'sometimes|date_format:H:i',
            'available_time.*.to'   => 'sometimes|date_format:H:i',
        ]);

        $doctor->update($validatedData);

        $doctor->load(['clinics', 'clinicDoctors']);
        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

        if ($request->has('available_time')) {
            $times = collect($request->available_time)
                ->map(fn($t) => [
                    'day'  => $t['day'],
                    'from' => $t['from'],
                    'to'   => $t['to'],
                ])
                ->values()
                ->toArray();

            $doctor->schedules()->where('clinic_id', $request->clinic_id)->where('type', 1)->delete();

            $doctor->schedules()->create([
                'clinic_id' => $request->clinic_id,
                'type'      => 1,
                'schedule'  => $times,
            ]);
        }

        return response()->json([
            'id' => $doctor->id,
            'first_name' => $doctor->first_name,
            'last_name'  => $doctor->last_name,
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

    public function createAppointments(Request $request)
    {
        $doctor = $request->user();
        if (!$doctor) {
            return response()->json([
                'debug' => [
                    'bearerToken' => $request->bearerToken(),
                    'authDoctorUser' => auth('doctor')->user(),
                    'authDefaultUser' => $request->user(),
                ]
            ]);
        }



    }

    public function logout(Request $request)
        {
           $request->user()->currentAccessToken()->delete();
        }
}
