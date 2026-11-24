<?php

namespace App\Http\Controllers;

use App\Services\Forecasting\MonthlyForecast;
use Illuminate\Http\JsonResponse;
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
}
