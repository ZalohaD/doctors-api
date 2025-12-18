<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserDomainService;
use App\Services\DoctorDomainService;
use App\Validators\UserValidator;
use App\Validators\DoctorValidator;
use App\DTO\UserDTO;
use App\DTO\DoctorDTO;

class AuthController extends Controller
{
    public function __construct(
        private UserDomainService $userService,
        private DoctorDomainService $doctorService
    ) {}

    public function register(Request $request) {
        $data = UserValidator::validate($request->all());
        $dto = new UserDTO($data['name'], $data['email'], $data['password']);

        return response()->json(
            $this->userService->register($dto)
        );
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        return response()->json(
            $this->userService->login($data['email'], $data['password'])
        );
    }

    public function logout(Request $request) {
        $this->userService->logout($request->user());

        return response()->json(['message' => 'Logged out.']);
    }

    public function redirectToGoogle() {
        return $this->userService->redirectToGoogle();
    }

    public function googleAuth() {
        return $this->userService->handleGoogleAuth();
    }

    public function registerDoctor(Request $request) {
        $data = DoctorValidator::validate($request->all());

        $dto = new DoctorDTO(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['password'],
            $data['clinic_id'],
            $data['specialization'],
            $data['available_time']
        );

        return response()->json(
            $this->doctorService->register($dto)
        );
    }

    public function loginDoctor(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        return response()->json(
            $this->doctorService->login($data['email'], $data['password'])
        );
    }
}

