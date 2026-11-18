<?php

use App\Http\Controllers\BarberIncomeController;
use App\Http\Controllers\BarberSessionController;
use App\Http\Controllers\OwnerAuthController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\OwnerExpenseController;
use App\Http\Controllers\OwnerIncomeController;
use App\Http\Controllers\OwnerRecapController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\EnsureBarberSession;
use App\Http\Middleware\EnsureOwnerIsActive;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/services/active', [ServiceController::class, 'active'])->name('services.active');

Route::prefix('kapster')->name('barber.')->middleware('guest')->group(function (): void {
    Route::post('sesi', [BarberSessionController::class, 'store'])->name('session.store');

    Route::middleware(EnsureBarberSession::class)->group(function (): void {
        Route::get('/', [BarberIncomeController::class, 'index'])->name('dashboard');
        Route::get('tambah', [BarberIncomeController::class, 'create'])->name('entry-form');
        Route::post('pencatatan', [BarberIncomeController::class, 'store'])->name('entries.store');
        Route::get('produk', [BarberIncomeController::class, 'createProductSale'])->name('product-form');
        Route::post('produk', [BarberIncomeController::class, 'store'])->name('products.store');
        Route::post('keluar', [BarberSessionController::class, 'destroy'])->name('session.destroy');
    });
});

Route::prefix('owner')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [OwnerAuthController::class, 'create'])->name('login');
        Route::post('login', [OwnerAuthController::class, 'store'])
            ->middleware('throttle:owner-login')
            ->name('owner.login.store');
    });

    Route::middleware(['auth', EnsureOwnerIsActive::class])->group(function (): void {
        Route::get('/', OwnerDashboardController::class)->name('owner.home');
        Route::post('logout', [OwnerAuthController::class, 'destroy'])->name('owner.logout');

        Route::name('owner.')->group(function (): void {
            Route::get('pemasukan', [OwnerIncomeController::class, 'index'])->name('income.index');
            Route::get('pemasukan/ekspor', [OwnerIncomeController::class, 'export'])->name('income.export');
            Route::get('pengeluaran', [OwnerExpenseController::class, 'index'])->name('expenses.index');
            Route::get('pengeluaran/tambah', [OwnerExpenseController::class, 'create'])->name('expenses.create');
            Route::post('pengeluaran', [OwnerExpenseController::class, 'store'])->name('expenses.store');
            Route::get('pengeluaran/ekspor', [OwnerExpenseController::class, 'export'])->name('expenses.export');
            Route::get('rekap', [OwnerRecapController::class, 'index'])->name('recap.index');
            Route::get('rekap/ekspor', [OwnerRecapController::class, 'export'])->name('recap.export');
            Route::resource('services', ServiceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::patch('services/{service}/archive', [ServiceController::class, 'archive'])->name('services.archive');
            Route::patch('services/{service}/restore', [ServiceController::class, 'restore'])->name('services.restore');
            Route::resource('products', ProductController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::patch('products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
            Route::patch('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        });
    });
});
