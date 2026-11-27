<?php

use App\Http\Controllers\OwnerForecastController;
use Illuminate\Support\Facades\Route;

Route::get('forecasting', [OwnerForecastController::class, 'index'])->name('forecasting.index');
Route::get('forecasting/data', [OwnerForecastController::class, 'data'])->name('forecasting.data');
Route::post('forecasting/refresh', [OwnerForecastController::class, 'refresh'])->middleware('throttle:3,1')->name('forecasting.refresh');
