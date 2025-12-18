<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserDomainService;
use App\Validators\UserProfileValidator;

class UserController extends Controller
{
    public function __construct(
        private UserDomainService $userService
    ) {}

    public function dashboard()
    {
        return response()->json(
            $this->userService->getCurrentUser()
        );
    }

    public function edit(Request $request)
    {
        $user = $this->userService->getCurrentUser();
        $data = UserProfileValidator::validate($request->all());

        return response()->json(
            $this->userService->updateProfile($user, $data)
        );
    }

    public function appointments()
    {
        $user = $this->userService->getCurrentUser();

        return response()->json(
            $this->userService->getAppointments($user)
        );
    }
}
