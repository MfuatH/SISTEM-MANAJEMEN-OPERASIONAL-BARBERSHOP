<?php

namespace App\Http\Controllers;

use App\Services\Forecasting\MonthlyForecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class OwnerForecastController extends Controller
{
    public function index(MonthlyForecast $forecast): Response
    {
        return response()->view('owner.forecasting.index', ['forecast' => $forecast->status()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function data(MonthlyForecast $forecast): JsonResponse
    {
        return response()->json($forecast->status())->header('Cache-Control', 'private, no-store');
    }

    public function refresh(MonthlyForecast $forecast): RedirectResponse
    {
        $message = match ($forecast->refresh(force: true)) {
            'ready' => 'Prediksi berhasil diperbarui.',
            'insufficient_data' => 'Prediksi memerlukan tiga bulan histori lengkap.',
            'processing' => 'Pembaruan prediksi sedang berjalan.',
            'disabled' => 'Forecasting sedang dinonaktifkan.',
            default => 'Prediksi belum berhasil diperbarui. Periksa informasi status.',
        };

        return to_route('owner.forecasting.index', status: 303)->with('forecast_refresh', $message);
    }
}
