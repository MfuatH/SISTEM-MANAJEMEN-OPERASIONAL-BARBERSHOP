<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Forecasting\HistoryPackage;
use App\Services\Forecasting\MonthlyRevenueHistory;
use Illuminate\Console\Command;
use LogicException;

class ImportForecastHistory extends Command
{
    protected $signature = 'forecast:history-import
        {directory : Nama folder paket di storage/app/private/imports}
        {--preview : Validasi dan tampilkan total tanpa menulis database}
        {--activate : Pilih batch untuk bulan lengkap yang belum memiliki sumber lain}
        {--owner= : Username Owner aktif untuk metadata audit}';

    protected $description = 'Rekonsiliasi dan impor paket CSV histori untuk Forecasting';

    public function handle(MonthlyRevenueHistory $history): int
    {
        try {
            $package = HistoryPackage::fromDirectory($this->argument('directory'));
            if (! $this->option('preview')) {
                $ownerId = $this->findOwnerId();
                $id = $history->import($package, (bool) $this->option('activate'), $ownerId);
                $this->info("Batch #$id tersimpan atau sudah tersedia.");
            }
        } catch (LogicException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line($package->version.' ('.$package->dataKind.')');
        $this->line(sprintf(
            'Transaksi: %d | Item: %d | Nama pelanggan kosong: %d',
            $package->statistics['transactions'],
            $package->statistics['items'],
            $package->statistics['blank_names'],
        ));
        $this->table(['Bulan WIB', 'Pemasukan IDR', 'Transaksi', 'Lengkap'], array_map(
            fn (array $month): array => [
                substr($month['month_start'], 0, 7),
                $month['revenue'],
                $month['transaction_count'],
                $month['is_complete'] ? 'Ya' : 'Tidak',
            ],
            $package->months,
        ));

        return self::SUCCESS;
    }

    private function findOwnerId(): ?int
    {
        $username = $this->option('owner');
        if ($username === null) {
            return null;
        }

        $owner = User::query()->where('username', User::normalizeUsername($username))
            ->whereNull('disabled_at')->first();
        if ($owner === null) {
            throw new LogicException('Username Owner aktif tidak ditemukan.');
        }

        return $owner->getKey();
    }
}
