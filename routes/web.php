<?php

use App\Http\Controllers\OwnerAuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\EnsureOwnerIsActive;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get('/services/active', [ServiceController::class, 'active'])->name('services.active');

Route::prefix('owner')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [OwnerAuthController::class, 'create'])->name('login');
        Route::post('login', [OwnerAuthController::class, 'store'])
            ->middleware('throttle:owner-login')
            ->name('owner.login.store');
    });

    Route::middleware(['auth', EnsureOwnerIsActive::class])->group(function (): void {
        Route::view('/', 'owner.home')->name('owner.home');
        Route::post('logout', [OwnerAuthController::class, 'destroy'])->name('owner.logout');

        Route::name('owner.')->group(function (): void {
            Route::resource('services', ServiceController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::patch('services/{service}/archive', [ServiceController::class, 'archive'])->name('services.archive');
        });
    });
});
