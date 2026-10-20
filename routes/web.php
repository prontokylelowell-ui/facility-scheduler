<?php

use App\Http\Controllers\Web\BookingPageController;
use App\Http\Controllers\Web\FacilityPageController;
use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes (Blade UI)  —  MVC: Route -> Middleware -> Controller -> Model -> View
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('facilities.index');
});

/*
| Guest-only: authentication (login / register)
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register', [LoginController::class, 'register']);
});

/*
| Authenticated (any role). 'set.rls' aligns the PostgreSQL session variable
| `app.user_id` so every RLS policy applies to this request.
*/
Route::middleware(['auth', 'set.rls'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ---- Facilities: READ for every role ----
    Route::get('/facilities', [FacilityPageController::class, 'index'])->name('facilities.index');

    // ---- Facilities: WRITE only for app_admin ----
    Route::middleware('role:app_admin')->group(function () {
        Route::post('/facilities', [FacilityPageController::class, 'store'])->name('facilities.store');
        Route::put('/facilities/{facility}', [FacilityPageController::class, 'update'])->name('facilities.update');
        Route::delete('/facilities/{facility}', [FacilityPageController::class, 'destroy'])->name('facilities.destroy');
    });

    // ---- Bookings: own bookings for app_user, all for app_admin ----
    Route::get('/bookings', [BookingPageController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingPageController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [BookingPageController::class, 'cancel'])->name('bookings.cancel');

    // ---- Bookings: approve / reject only for app_admin ----
    Route::middleware('role:app_admin')->group(function () {
        Route::post('/bookings/{booking}/approve', [BookingPageController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [BookingPageController::class, 'reject'])->name('bookings.reject');
    });
});
