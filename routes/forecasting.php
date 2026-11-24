<?php

use App\Http\Controllers\OwnerForecastController;
use Illuminate\Support\Facades\Route;

Route::get('forecasting', [OwnerForecastController::class, 'index'])->name('forecasting.index');
Route::get('forecasting/data', [OwnerForecastController::class, 'data'])->name('forecasting.data');
