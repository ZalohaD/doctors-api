<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:sanctum');

Route::post('/register-doctor', [AuthController::class, 'registerDoctor']);
Route::post('/login-doctor', [AuthController::class, 'loginDoctor']);
Route::get('/doctor-options', [DoctorController::class, 'formOptions']);

Route::post('/submit-review');

Route::get('/doctors', [DoctorController::class, 'index']);

Route::get('/doctor/{id}', [DoctorController::class, 'profile']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/doctor/me', [DoctorController::class, 'me']);
});
