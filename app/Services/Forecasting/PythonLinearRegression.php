<?php

namespace App\Services\Forecasting;

use Illuminate\Support\Facades\Process;
use JsonException;
use RuntimeException;

class PythonLinearRegression
{
    public const VERSION = 'linear-regression-v1';

    public function predict(array $input): array
    {
        $result = Process::timeout(20)
            ->input(json_encode($input, JSON_THROW_ON_ERROR))
            ->run([config('forecasting.python'), base_path('forecasting/linear_regression.py')]);

        if (! $result->successful()) {
            throw new RuntimeException('Proses model tidak berhasil.');
        }

        try {
            $output = json_decode($result->output(), true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Keluaran model bukan JSON yang valid.', previous: $e);
        }

        $holdoutMonth = $input['holdout']['target_month'] ?? null;
        $holdout = $output['holdout'] ?? null;
        if (($output['model'] ?? null) !== 'LinearRegression' ||
            ($output['target_month'] ?? null) !== $input['target_month'] ||
            ! $this->validPrediction($output['prediction'] ?? null) ||
            ! is_array($output) || ! array_key_exists('holdout', $output) ||
            ($holdoutMonth === null && $holdout !== null) ||
            ($holdoutMonth !== null && (($holdout['month'] ?? null) !== $holdoutMonth || ! $this->validPrediction($holdout)))) {
            throw new RuntimeException('Keluaran model tidak sesuai kontrak.');
        }

        return $output;
    }

    private function validPrediction(mixed $prediction): bool
    {
        $amount = $prediction['amount'] ?? null;
        $raw = $prediction['raw'] ?? null;
        $negative = $prediction['negative'] ?? null;

        return is_string($amount) && preg_match('/\A(?:0|-?[1-9][0-9]{0,30})\z/', $amount)
            && is_string($raw) && strlen($raw) <= 80 && preg_match('/\A-?[0-9]+(?:\.[0-9]+)?\z/', $raw)
            && is_bool($negative) && (! str_starts_with($amount, '-') || $negative);
    }
}
