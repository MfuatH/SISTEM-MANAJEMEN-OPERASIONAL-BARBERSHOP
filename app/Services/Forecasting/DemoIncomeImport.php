<?php

namespace App\Services\Forecasting;

use App\Models\IncomeTransaction;
use App\Models\Product;
use App\Models\Service;
use App\Services\OwnerReport;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DemoIncomeImport
{
    public function __construct(private MonthlyRevenueHistory $history) {}

    public function store(HistoryPackage $package): int
    {
        if ($package->dataKind !== 'synthetic' || $package->transactions === []) {
            throw new DomainException('Gunakan paket demo sintetis yang berisi transaksi.');
        }

        $currentMonth = CarbonImmutable::now(OwnerReport::TIMEZONE)->startOfMonth()->toDateString();
        foreach ($package->months as $month) {
            if (! $month['is_complete'] || $month['month_start'] >= $currentMonth) {
                throw new DomainException('Semua bulan pada paket demo harus lengkap dan sudah berakhir di WIB.');
            }
        }

        $result = Cache::lock('forecasting:demo-income-import', 300)->get(
            fn (): int => DB::transaction(fn (): int => $this->storeBatch($package), attempts: 3),
        );

        if ($result === false) {
            throw new DomainException('Impor demo sedang berjalan.');
        }

        return $result;
    }

    private function storeBatch(HistoryPackage $package): int
    {
        $batch = $this->history->import($package, false, null);
        $existing = IncomeTransaction::query()->where('demo_import_id', $batch)->count();
        if ($existing !== 0) {
            if ($existing !== count($package->transactions)) {
                throw new DomainException('Batch demo sudah ada dengan jumlah transaksi berbeda.');
            }

            return 0;
        }

        $months = array_column($package->months, 'month_start');
        $start = CarbonImmutable::parse($months[0], OwnerReport::TIMEZONE);
        $end = CarbonImmutable::parse($months[array_key_last($months)], OwnerReport::TIMEZONE)->addMonth();

        $otherDemo = IncomeTransaction::query()->whereNotNull('demo_import_id')
            ->where('recorded_at', '>=', $start->utc())
            ->where('recorded_at', '<', $end->utc())->exists();
        if ($otherDemo) {
            throw new DomainException('Rentang paket tumpang tindih dengan transaksi dari batch demo lain.');
        }

        $catalog = [];
        foreach ($package->transactions as $sourceId => $row) {
            $transaction = new IncomeTransaction([
                'barber_name' => Str::substr($row['barber_name'], 0, 113).' (Demo)',
                'customer_name' => $row['customer_name'],
                'recorded_at' => CarbonImmutable::parse($row['recorded_at_wib'], OwnerReport::TIMEZONE)->utc(),
                'session_scope' => hash('sha256', 'forecast-demo:'.$package->manifestHash, true),
                'request_key' => Uuid::uuid5(Uuid::NAMESPACE_URL, "barbershop-demo:{$package->manifestHash}:$sourceId")->toString(),
                'request_hash' => hash('sha256', json_encode($row, JSON_THROW_ON_ERROR), true),
            ]);
            $transaction->demo_import_id = $batch;
            $transaction->save();

            foreach ($row['items'] as $item) {
                if (mb_strlen($item['name']) > 120 || strlen($item['unit_price']) > 15) {
                    throw new DomainException('Nama atau harga item melebihi kapasitas tabel transaksi.');
                }
                $type = $item['type'];
                $name = $item['name'];
                $id = $catalog[$type][$name] ??= $this->catalogId($item);
                if ($type === 'service') {
                    $transaction->entries()->create([
                        'service_id' => $id, 'service_name' => $name, 'amount' => $item['unit_price'],
                    ]);
                } else {
                    $transaction->productSales()->create([
                        'product_id' => $id, 'product_name' => $name,
                        'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'],
                    ]);
                }
            }
        }

        $monthsWithTransactions = array_column(array_filter(
            $package->months, fn (array $month): bool => $month['transaction_count'] > 0,
        ), 'month_start');
        DB::table('forecast_month_sources')->where('source', 'import')
            ->where('import_id', $batch)->whereIn('month_start', $monthsWithTransactions)
            ->update(['source' => 'application', 'import_id' => null]);

        return count($package->transactions);
    }

    private function catalogId(array $item): int
    {
        $service = $item['type'] === 'service';
        $model = $service ? Service::class : Product::class;
        $price = $service ? 'current_price' : 'sale_price';
        $record = $model::query()->where('name', $item['name'])->orderBy('id')->first();
        if ($record === null) {
            $record = new $model(['name' => $item['name'], $price => $item['unit_price']]);
            $record->archived_at = now('UTC');
            $record->save();
        }

        return $record->getKey();
    }
}
