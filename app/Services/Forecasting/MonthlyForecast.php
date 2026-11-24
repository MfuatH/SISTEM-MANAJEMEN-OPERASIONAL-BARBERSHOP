<?php

namespace App\Services\Forecasting;

use App\Services\OwnerReport;
use Brick\Math\BigInteger;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;

class MonthlyForecast
{
    private const LOCK = 'forecasting:refresh';

    private const RUNNING = 'forecasting:refreshing';

    private const LOCK_SECONDS = 180;

    public function __construct(private MonthlyRevenueHistory $history, private PythonLinearRegression $model) {}

    public function refresh(): string
    {
        if (! config('forecasting.enabled')) {
            return 'disabled';
        }

        return Cache::lock(self::LOCK, self::LOCK_SECONDS)->get(function (): string {
            $context = $this->context();
            if ($context['window']['status'] !== 'available') {
                return 'insufficient_data';
            }

            $last = $this->snapshot();
            if ($last !== null && $last['fingerprint'] === $context['hash'] && $last['response']['status'] === 'ready') {
                return 'up_to_date';
            }

            Cache::put(self::RUNNING, true, self::LOCK_SECONDS);
            try {
                try {
                    $output = $this->model->predict($context['input']);
                } catch (RuntimeException) {
                    $this->publish($context, $this->response('unavailable', 'generation_failed', $context['window']));

                    return 'unavailable';
                }

                $prediction = $output['prediction'];
                $response = $prediction['negative']
                    ? $this->response('unavailable', 'negative_prediction', $context['window'])
                    : $this->response('ready', null, $context['window']);

                if ($response['status'] === 'ready') {
                    $response['predictions'] = [[
                        'month' => $output['target_month'],
                        'amount' => $prediction['amount'],
                    ]];
                }
                $response['generated_at'] = CarbonImmutable::now(OwnerReport::TIMEZONE)->toIso8601String();
                $response['evaluation'] = $this->evaluation($context, $output['holdout']);
                $this->publish($context, $response, $output);

                return $response['status'];
            } finally {
                Cache::forget(self::RUNNING);
            }
        }) ?: 'processing';
    }

    public function status(): array
    {
        if (! config('forecasting.enabled')) {
            return $this->response('disabled', 'feature_disabled');
        }

        $context = $this->context();
        $snapshot = $this->snapshot();
        if ($context['window']['status'] === 'available' && $snapshot !== null && $snapshot['fingerprint'] === $context['hash']) {
            return $snapshot['response'];
        }
        if ($snapshot !== null && $snapshot['response']['status'] === 'ready') {
            return array_replace($snapshot['response'], ['status' => 'stale', 'reason_code' => 'refresh_required']);
        }
        if ($context['window']['status'] !== 'available') {
            return $this->response('insufficient_data', 'incomplete_history', $context['window']);
        }

        return Cache::get(self::RUNNING)
            ? $this->response('processing', 'refresh_in_progress', $context['window'])
            : $this->response('unavailable', 'forecast_not_generated', $context['window']);
    }

    private function context(): array
    {
        [$window, $holdout, $evaluationTarget] = DB::transaction(function (): array {
            $window = $this->history->window();
            if ($window['status'] !== 'available') {
                return [$window, null, null];
            }

            $target = $window['actual'][2];
            $preceding = $this->history->window($target['month']);

            return [$window, $preceding['status'] === 'available' ? $preceding : null, $target];
        });

        return [
            'hash' => hash('sha256', json_encode([PythonLinearRegression::VERSION, $window, $holdout], JSON_THROW_ON_ERROR)),
            'window' => $window,
            'holdout' => $holdout,
            'evaluation_target' => $evaluationTarget,
            'input' => [
                'target_month' => $window['target_month'],
                'actual' => $this->points($window),
                'holdout' => $holdout === null ? null : [
                    'target_month' => $holdout['target_month'],
                    'actual' => $this->points($holdout),
                ],
            ],
        ];
    }

    private function points(array $window): array
    {
        return array_map(
            fn (array $point): array => ['month' => $point['month'], 'amount' => $point['amount'], 'complete' => true],
            $window['actual'],
        );
    }

    private function evaluation(array $context, ?array $prediction): array
    {
        if ($prediction === null) {
            return [
                'status' => 'not_evaluated',
                'data_kind' => $context['window']['data_kind'],
                'test_months' => 0,
                'mae_idr' => null,
                'field_accuracy_verified' => false,
            ];
        }

        $target = $context['evaluation_target'];
        $dataKind = $target['data_kind'] === $context['holdout']['data_kind'] ? $target['data_kind'] : 'mixed';
        $actual = BigInteger::of($target['amount']);
        $baseline = $context['holdout']['actual'][2]['amount'];

        return [
            'status' => 'evaluated_'.$dataKind,
            'data_kind' => $dataKind,
            'test_months' => 1,
            'mae_idr' => (string) $actual->minus($prediction['amount'])->abs(),
            'field_accuracy_verified' => false,
            'test_month' => $target['month'],
            'actual_idr' => $target['amount'],
            'prediction_idr' => $prediction['amount'],
            'baseline_mae_idr' => (string) $actual->minus($baseline)->abs(),
        ];
    }

    private function response(string $status, ?string $reason, ?array $window = null): array
    {
        $actual = $window === null ? [] : $this->points($window);
        $kind = $window['data_kind'] ?? null;
        $warnings = match ($kind) {
            'synthetic' => ['Data sintetis untuk demonstrasi dan pengujian.'],
            'mixed' => ['Deret menggunakan gabungan data aktual dan sintetis.'],
            default => [],
        };

        return [
            'schema_version' => 1,
            'status' => $status,
            'reason_code' => $reason,
            'target' => 'gross_revenue',
            'currency' => 'IDR',
            'timezone' => OwnerReport::TIMEZONE,
            'data_kind' => $kind,
            'training_window_months' => 3,
            'horizon_months' => 1,
            'data_through' => $actual === [] ? null : end($actual)['month'],
            'generated_at' => null,
            'actual' => $actual,
            'predictions' => [],
            'evaluation' => null,
            'warnings' => $warnings,
        ];
    }

    private function snapshot(): ?array
    {
        try {
            $value = json_decode(File::get(config('forecasting.snapshot_path')), true, 32, JSON_THROW_ON_ERROR);
        } catch (FileNotFoundException|JsonException) {
            return null;
        }

        return is_string($value['fingerprint'] ?? null) && isset($value['response']['status']) ? $value : null;
    }

    private function publish(array $context, array $response, ?array $output = null): void
    {
        $path = config('forecasting.snapshot_path');
        File::ensureDirectoryExists(dirname($path), 0700);
        File::replace($path, json_encode([
            'fingerprint' => $context['hash'],
            'model_version' => PythonLinearRegression::VERSION,
            'input' => $context['window'],
            'evaluation_input' => $context['holdout'],
            'model_output' => $output,
            'response' => $response,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), 0600);
    }
}
