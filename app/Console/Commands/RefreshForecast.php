<?php

namespace App\Console\Commands;

use App\Services\Forecasting\MonthlyForecast;
use Illuminate\Console\Command;

class RefreshForecast extends Command
{
    protected $signature = 'forecast:refresh';

    protected $description = 'Perbarui prediksi pemasukan untuk bulan berjalan WIB';

    public function handle(MonthlyForecast $forecast): int
    {
        $status = $forecast->refresh();
        $this->line('Forecasting: '.$status);

        return $status === 'unavailable' ? self::FAILURE : self::SUCCESS;
    }
}
