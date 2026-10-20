<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FacilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (JSON) — same models/policies, token-free session auth
|--------------------------------------------------------------------------
*/

// ---- Public auth ----
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ---- Public read: facilities ----
Route::get('/facilities', [FacilityController::class, 'index']);
Route::get('/facilities/{facility}', [FacilityController::class, 'show']);
Route::get('/facilities/{facility}/availability', [FacilityController::class, 'availability']);

/*
| Authenticated (any role) + RLS session variable.
*/
Route::middleware(['auth', 'set.rls'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ---- Bookings (core entity #2) ----
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // ---- Bookings: admin-only decisions ----
    Route::middleware('role:app_admin')->group(function () {
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
        Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    });

    // ---- Facilities (core entity #1): admin-only writes ----
    Route::middleware('role:app_admin')->group(function () {
        Route::post('/facilities', [FacilityController::class, 'store']);
        Route::put('/facilities/{facility}', [FacilityController::class, 'update']);
        Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy']);
    });
});
