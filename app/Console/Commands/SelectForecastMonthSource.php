<?php

namespace App\Console\Commands;

use App\Services\Forecasting\MonthlyRevenueHistory;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SelectForecastMonthSource extends Command
{
    protected $signature = 'forecast:month-source
        {month : Bulan WIB YYYY-MM yang sudah berakhir}
        {source : application atau import}
        {--import-id= : ID batch untuk sumber import}';

    protected $description = 'Tetapkan sumber lengkap untuk satu bulan Forecasting';

    public function handle(MonthlyRevenueHistory $history): int
    {
        $value = $this->option('import-id');
        $importId = $value === null ? null : filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($importId === false) {
            $this->error('ID batch harus bilangan bulat positif.');

            return self::FAILURE;
        }

        try {
            $history->selectSource($this->argument('month'), $this->argument('source'), $importId);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Sumber Forecasting untuk bulan '.$this->argument('month').' telah ditetapkan.');

        return self::SUCCESS;
    }
}
