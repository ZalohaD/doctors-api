<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentPageController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/api/auth/google/callback', [AuthController::class, 'googleAuth'])->name('google.callback');

Route::get('/payment/success', [PaymentPageController::class, 'success'])->name('payment.success');;
