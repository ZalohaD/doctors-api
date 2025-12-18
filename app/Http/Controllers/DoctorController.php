<?php

namespace App\Http\Controllers;

use App\Services\DoctorDomainService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(
        private DoctorDomainService $doctorService
    ) {}

    public function formOptions()
    {
        return response()->json($this->doctorService->getFormOptions());
    }

    public function index()
    {
        return response()->json($this->doctorService->getAllDoctors());
    }

    public function profile($id)
    {
        $userId = auth('sanctum')->id();
        $profile = $this->doctorService->getDoctorProfile($id, $userId);

        if (!$profile) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json($profile);
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

        return response()->json($this->doctorService->getDoctorData($doctor));
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

        $validatedData = \App\Validators\DoctorValidator::validateUpdate($request->all());
        $result = $this->doctorService->updateDoctorProfile($doctor, $validatedData);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json($result);
    }

    public function createAppointments(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Token missing or invalid.'
            ], 401);
        }

        $data = \App\Validators\DoctorValidator::validateAppointment($request->all());
        $appointment = $this->doctorService->createAppointment($user->id, $data);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
