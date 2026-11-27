<?php

use Illuminate\Foundation\DevCommands;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('forecast:refresh --monthly')->hourly()->timezone('Asia/Jakarta')
    ->when(fn (): bool => (bool) config('forecasting.enabled'));

DevCommands::artisan('schedule:work', 'scheduler');
