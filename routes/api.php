<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/register-doctor', [AuthController::class, 'registerDoctor']);
Route::post('/login-doctor', [AuthController::class, 'loginDoctor']);
Route::get('/doctor-options', [DoctorController::class, 'formOptions']);


Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{doctorId}/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/doctor/me', [DoctorController::class, 'me']);
    Route::patch('/doctor/me', [DoctorController::class, 'edit']);
    Route::get('/doctor/logout', [DoctorController::class, 'logout']);
    Route::post('/submit-review', [ReviewController::class, 'submit']);;

    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::post('/dashboard', [UserController::class, 'edit']);
    Route::get('/dashboard/appointments', [UserController::class, 'appointments']);
});



Route::get('/doctor/{id}', [DoctorController::class, 'profile']);
Route::post('/appointments', [DoctorController::class, 'createAppointments']);

Route::get('/search', [SearchController::class, 'result']);
