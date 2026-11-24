<?php

namespace App\Services\Forecasting;

use App\Models\HistoricalImport;
use App\Services\IncomeItems;
use App\Services\OwnerReport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class MonthlyRevenueHistory
{
    public function import(HistoryPackage $package, bool $activate, ?int $ownerId): int
    {
        return DB::transaction(function () use ($package, $activate, $ownerId): int {
            $identity = [
                'manifest_sha256' => $package->manifestHash,
                'mapping_version' => HistoryPackage::MAPPING_VERSION,
            ];
            DB::table('historical_imports')->upsert([$identity + [
                'dataset_version' => $package->version,
                'data_kind' => $package->dataKind,
                'imported_at' => CarbonImmutable::now('UTC'),
                'imported_by' => $ownerId,
            ]], array_keys($identity), ['manifest_sha256']);

            $importId = HistoricalImport::query()->where($identity)->lockForUpdate()->firstOrFail()->getKey();
            if (! DB::table('historical_monthly_revenues')->where('import_id', $importId)->exists()) {
                DB::table('historical_monthly_revenues')->insert(array_map(
                    fn (array $month): array => $month + ['import_id' => $importId],
                    $package->months,
                ));
            }

            if ($activate) {
                $currentMonth = self::currentMonth()->toDateString();
                foreach ($package->months as $month) {
                    if ($month['is_complete'] && $month['month_start'] < $currentMonth) {
                        $this->activateImportedMonth($month['month_start'], $importId);
                    }
                }
            }

            return $importId;
        }, attempts: 3);
    }

    public function selectSource(string $month, string $source, ?int $importId): void
    {
        $start = self::parseMonth($month);
        if ($start >= self::currentMonth()) {
            throw new InvalidArgumentException('Pilih bulan kalender WIB yang sudah berakhir.');
        }
        if (($source === 'application' && $importId !== null) ||
            ($source === 'import' && $importId === null) ||
            ! in_array($source, ['application', 'import'], true)) {
            throw new InvalidArgumentException('Pilih application tanpa ID atau import dengan --import-id.');
        }

        $date = $start->toDateString();
        DB::transaction(function () use ($date, $source, $importId): void {
            if ($importId !== null && ! DB::table('historical_monthly_revenues')
                ->where('import_id', $importId)->where('month_start', $date)->exists()) {
                throw new InvalidArgumentException('Batch impor tidak memuat bulan yang dipilih.');
            }

            DB::table('forecast_month_sources')->upsert([[
                'month_start' => $date,
                'source' => $source,
                'import_id' => $importId,
            ]], ['month_start'], ['source', 'import_id']);
        }, attempts: 3);
    }

    public function window(?string $target = null): array
    {
        $end = $target === null ? self::currentMonth() : self::parseMonth($target);
        $currentMonth = self::currentMonth();
        $start = $end->subMonths(3);
        if ($start->year < 1000) {
            throw new InvalidArgumentException('Pilih bulan target dengan tiga bulan sebelumnya dalam rentang MySQL.');
        }

        return DB::transaction(function () use ($start, $end, $currentMonth): array {
            $sources = DB::table('forecast_month_sources as selected')
                ->leftJoin('historical_monthly_revenues as historical', function ($join): void {
                    $join->on('historical.import_id', '=', 'selected.import_id')
                        ->on('historical.month_start', '=', 'selected.month_start');
                })
                ->leftJoin('historical_imports as batch', 'batch.id', '=', 'selected.import_id')
                ->where('selected.month_start', '>=', $start->toDateString())
                ->where('selected.month_start', '<', $end->toDateString())
                ->get([
                    'selected.month_start', 'selected.source', 'selected.import_id',
                    'historical.revenue', 'historical.transaction_count', 'historical.is_complete',
                    'batch.data_kind', 'batch.dataset_version', 'batch.manifest_sha256',
                ])->keyBy('month_start');

            $actual = $missing = [];
            for ($month = $start; $month < $end; $month = $month->addMonth()) {
                $date = $month->toDateString();
                $label = $month->format('Y-m');
                $source = $sources->get($date);

                if ($month >= $currentMonth || ($source?->source === 'import' && ! $source->is_complete)) {
                    $missing[] = $label;

                    continue;
                }

                if ($source?->source === 'import') {
                    $actual[] = [
                        'month' => $label,
                        'amount' => (string) $source->revenue,
                        'transaction_count' => (int) $source->transaction_count,
                        'complete' => true,
                        'source' => 'import',
                        'data_kind' => $source->data_kind,
                        'import_id' => (int) $source->import_id,
                        'dataset_version' => $source->dataset_version,
                        'manifest_sha256' => $source->manifest_sha256,
                    ];

                    continue;
                }

                $application = $this->applicationMonth($month);
                if ($source === null && $application['transaction_count'] === 0) {
                    $missing[] = $label;

                    continue;
                }

                $actual[] = $application;
            }

            $kinds = array_unique(array_column($actual, 'data_kind'));

            return [
                'status' => $missing === [] ? 'available' : 'insufficient_data',
                'target_month' => $end->format('Y-m'),
                'data_kind' => count($kinds) > 1 ? 'mixed' : ($kinds[0] ?? null),
                'missing_months' => $missing,
                'actual' => $actual,
            ];
        });
    }

    private function activateImportedMonth(string $month, int $importId): void
    {
        DB::table('forecast_month_sources')->upsert([[
            'month_start' => $month,
            'source' => 'import',
            'import_id' => $importId,
        ]], ['month_start'], ['month_start']);

        $active = DB::table('forecast_month_sources')->where('month_start', $month)->lockForUpdate()->first();
        if ($active->source !== 'import' || (int) $active->import_id !== $importId) {
            throw new LogicException("Bulan $month sudah memiliki sumber lain. Impor tanpa --activate atau pilih sumber secara eksplisit.");
        }
    }

    private function applicationMonth(CarbonImmutable $month): array
    {
        $totals = IncomeItems::query()
            ->where('tx.recorded_at', '>=', $month->utc()->toDateTimeString())
            ->where('tx.recorded_at', '<', $month->addMonth()->utc()->toDateTimeString())
            ->select([])
            ->selectRaw('COALESCE(SUM(items.amount), 0) AS revenue, COUNT(DISTINCT tx.id) AS transaction_count,
                COUNT(DISTINCT CASE WHEN tx.demo_import_id IS NOT NULL THEN tx.id END) AS demo_count')
            ->first();

        return [
            'month' => $month->format('Y-m'),
            'amount' => (string) $totals->revenue,
            'transaction_count' => (int) $totals->transaction_count,
            'complete' => true,
            'source' => 'application',
            'data_kind' => match (true) {
                (int) $totals->demo_count === 0 => 'actual',
                (int) $totals->demo_count === (int) $totals->transaction_count => 'synthetic',
                default => 'mixed',
            },
            'import_id' => null,
            'dataset_version' => null,
            'manifest_sha256' => null,
        ];
    }

    private static function parseMonth(string $month): CarbonImmutable
    {
        if (! preg_match('/\A[1-9][0-9]{3}-(?:0[1-9]|1[0-2])\z/', $month)) {
            throw new InvalidArgumentException('Gunakan format bulan YYYY-MM.');
        }

        return CarbonImmutable::parse($month.'-01', OwnerReport::TIMEZONE);
    }

    private static function currentMonth(): CarbonImmutable
    {
        return CarbonImmutable::now(OwnerReport::TIMEZONE)->startOfMonth();
    }
}
