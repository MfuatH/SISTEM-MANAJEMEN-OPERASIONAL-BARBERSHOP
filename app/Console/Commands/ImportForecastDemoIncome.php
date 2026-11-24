<?php

namespace App\Console\Commands;

use App\Services\Forecasting\DemoIncomeImport;
use App\Services\Forecasting\HistoryPackage;
use Illuminate\Console\Command;
use LogicException;

class ImportForecastDemoIncome extends Command
{
    protected $signature = 'forecast:demo-import {directory : Nama folder paket di storage/app/private/imports}';

    protected $description = 'Catat transaksi sintetis ke histori Pemasukan di lingkungan lokal atau pengujian';

    public function handle(DemoIncomeImport $import): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Impor demo hanya tersedia untuk lingkungan local dan testing.');

            return self::FAILURE;
        }

        try {
            $count = $import->store(HistoryPackage::fromDirectory($this->argument('directory'), includeTransactions: true));
        } catch (LogicException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Transaksi demo baru: $count. Periksa histori di Rekap.");

        return self::SUCCESS;
    }
}
