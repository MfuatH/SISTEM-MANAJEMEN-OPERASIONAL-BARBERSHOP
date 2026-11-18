<?php

namespace App\Services;

use Carbon\CarbonImmutable;

abstract class MonthlyOwnerReport extends OwnerReport
{
    public readonly CarbonImmutable $month;

    public function __construct(string $search)
    {
        parent::__construct($search);

        $this->month = CarbonImmutable::now(self::TIMEZONE)->startOfMonth();
    }
}
