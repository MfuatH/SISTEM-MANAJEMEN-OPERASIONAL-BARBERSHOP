<?php

namespace App\Console\Commands;

use App\Services\Forecasting\MonthlyForecast;
use Illuminate\Console\Command;

class RefreshForecast extends Command
{
    protected $signature = 'forecast:refresh {--monthly : Terbitkan satu hasil siap otomatis untuk setiap bulan target}';

    protected $description = 'Perbarui prediksi pemasukan untuk bulan berjalan WIB';

    public function handle(MonthlyForecast $forecast): int
    {
        $status = $forecast->refresh(monthly: (bool) $this->option('monthly'));
        $this->line('Forecasting: '.$status);

        return $status === 'unavailable' ? self::FAILURE : self::SUCCESS;
    }
}
