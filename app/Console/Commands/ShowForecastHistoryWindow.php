<?php

namespace App\Console\Commands;

use App\Services\Forecasting\MonthlyRevenueHistory;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ShowForecastHistoryWindow extends Command
{
    protected $signature = 'forecast:history-window {target? : Bulan target YYYY-MM, default bulan berjalan WIB}';

    protected $description = 'Tampilkan tiga bulan input Forecasting dalam JSON';

    public function handle(MonthlyRevenueHistory $history): int
    {
        try {
            $window = $history->window($this->argument('target'));
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line(json_encode($window, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
