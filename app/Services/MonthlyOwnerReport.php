<?php

namespace App\Services;

use Carbon\CarbonImmutable;

abstract class MonthlyOwnerReport
{
    public const TIMEZONE = 'Asia/Jakarta';

    public readonly CarbonImmutable $month;

    public function __construct(public readonly string $search)
    {
        $this->month = CarbonImmutable::now(self::TIMEZONE)->startOfMonth();
    }

    protected function searchPattern(): string
    {
        return '%'.strtr($this->search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
    }

    public static function rupiah(string|int $amount): string
    {
        return 'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $amount);
    }
}
