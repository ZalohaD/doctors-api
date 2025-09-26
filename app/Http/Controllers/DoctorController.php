<?php

namespace App\Http\Controllers;

use App\Enums\DoctorSpecialization;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Models\Review;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function formOptions()
    {
        $clinics = Clinic::all(["id", "name"]);
        $specializations = DoctorSpecialization::options();

        $specializations = array_map(fn($k, $v) => ['value' => $k, 'label' => $v], array_keys($specializations), $specializations);

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

        $services = $doctor->services
        ->map(function($doctorService) {
            return [
                'id' => $doctorService->id,
                'name' => $doctorService->name ?? $doctorService->service->name ?? '',
                'description' => $doctorService->description ?? $doctorService->service->description ?? '',
                'price' => $doctorService->price ?? 0,
                'duration' => $doctorService->duration ?? 30,
                'specialization' => $doctorService->specialization ?? '',
                'is_active' => $doctorService->is_active ?? true,
            ];
        })
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
            'services' => $services,
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

        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

        $schedules = $doctor->schedules->map(function($schedule) {
            return $schedule->schedule;
        })->flatten(1)->values();

        $services = $doctor->services
        ->map(function($doctorService) {
            return [
                'id' => $doctorService->id,
                'name' => $doctorService->name ?? $doctorService->service->name ?? '',
                'description' => $doctorService->description ?? $doctorService->service->description ?? '',
                'price' => $doctorService->price ?? 0,
                'duration' => $doctorService->duration ?? 30,
                'specialization' => $doctorService->specialization ?? '',
                'is_active' => $doctorService->is_active ?? true,
            ];
        })
            ->values();

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
            'services' => $services,
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
            ], 404);
        }

        $validatedData = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name'  => 'sometimes|string',
            'email'      => 'sometimes|string|email',
            'phone'      => 'sometimes|string',
            'address'    => 'sometimes|string',
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
        ]);

        $doctor->update(collect($validatedData)->only([
            'first_name', 'last_name', 'email', 'phone', 'address'
        ])->toArray());

        $clinicId = $request->clinic_id ?? $doctor->clinics->first()?->id;
        if (!$clinicId) {
            return response()->json(['message' => 'Не вказана клініка'], 422);
        }

        if ($request->has('specializations')) {
            $doctor->clinicDoctors()->where('clinic_id', $clinicId)->delete();

            $specValues = collect($request->specializations)
                ->map(fn($specLabel) => collect(DoctorSpecialization::cases())
                    ->first(fn($case) => $case->label() === $specLabel)?->value
                )
                ->filter()
                ->values()
                ->toArray();

            if (!empty($specValues)) {
                $doctor->clinicDoctors()->create([
                    'clinic_id' => $clinicId,
                    'specialization' => $specValues,
                ]);
            }
        }

        if ($request->has('available_time')) {
            $times = collect($request->available_time)
                ->map(fn($t) => [
                    'day' => $t['day'],
                    'from' => $t['from'],
                    'to' => $t['to'],
                ])->values()->toArray();

            $doctor->schedules()->where('clinic_id', $clinicId)->where('type', 1)->delete();

            $doctor->schedules()->create([
                'clinic_id' => $clinicId,
                'type' => 1,
                'schedule' => $times,
            ]);
        }

        if ($request->has('services')) {
            $existingServiceIds = [];
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['id'])) {
                    $service = $doctor->services()->where('id', $serviceData['id'])->first();
                    if ($service) {
                        $service->update([
                            'clinic_id' => $clinicId,
                            'name' => $serviceData['name'],
                            'specialization' => $serviceData['specialization'],
                            'price' => $serviceData['price'],
                            'duration' => $serviceData['duration'] ?? null,
                            'description' => $serviceData['description'] ?? null,
                            'is_active' => $serviceData['is_active'] ?? true,
                        ]);
                        $existingServiceIds[] = $service->id;
                    }
                } else {
                    $service = $doctor->services()->create([
                        'clinic_id' => $clinicId,
                        'name' => $serviceData['name'],
                        'specialization' => $serviceData['specialization'],
                        'price' => $serviceData['price'],
                        'duration' => $serviceData['duration'] ?? null,
                        'description' => $serviceData['description'] ?? null,
                        'is_active' => $serviceData['is_active'] ?? true,
                    ]);
                    $existingServiceIds[] = $service->id;
                }
            }
        }

        $specializations = $doctor->clinicDoctors
            ->pluck('specialization')
            ->flatten()
            ->unique()
            ->map(fn($spec) => DoctorSpecialization::tryFrom($spec)?->label() ?? $spec);

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

    public function createAppointments(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Token missing or invalid.'
            ], 401);
        }

        $data = $request->validate([
            'doctor_id'   => 'required|integer|exists:doctors,id',
            'service_id'  => 'required|integer|exists:doctor_services,id',
            'from'        => 'required|date_format:Y-m-d H:i',
            'to'          => 'required|date_format:Y-m-d H:i|after:from',
            'firstname'   => 'required|string|max:255',
            'lastname'    => 'required|string|max:255',
            'status'      => 'required|integer',
        ]);

        $service = DoctorService::findOrFail($data['service_id']);

        $data['user_id']   = $user->id;
        $data['clinic_id'] = $service->clinic_id;

        $appointment = Appointment::create($data);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }


    public function logout(Request $request)
        {
           $request->user()->currentAccessToken()->delete();
        }
}
