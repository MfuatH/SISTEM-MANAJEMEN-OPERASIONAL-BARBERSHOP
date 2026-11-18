<?php

namespace App\Services;

use Brick\Math\BigInteger;
use Carbon\CarbonImmutable;

class OwnerDashboardReport
{
    /**
     * @return array{
     *     month: CarbonImmutable,
     *     today: CarbonImmutable,
     *     totals: array{income: string, expenses: string, net: string},
     *     days: list<array{date: string, income: string, expenses: string}>
     * }
     */
    public function currentMonth(): array
    {
        $today = CarbonImmutable::today(OwnerReport::TIMEZONE);
        $month = $today->startOfMonth();
        $entries = OwnerFinancialEntries::query($month)->select('business_date');
        $grouped = OwnerFinancialEntries::selectTotals($entries)
            ->groupBy('business_date')
            ->get()
            ->keyBy('business_date');

        $income = BigInteger::zero();
        $expenses = BigInteger::zero();
        foreach ($grouped as $amounts) {
            $income = $income->plus($amounts->income);
            $expenses = $expenses->plus($amounts->expenses);
        }

        $days = [];
        for ($day = $month; $day <= $today; $day = $day->addDay()) {
            $date = $day->toDateString();
            $amounts = $grouped->get($date);
            $days[] = [
                'date' => $date,
                'income' => (string) ($amounts->income ?? 0),
                'expenses' => (string) ($amounts->expenses ?? 0),
            ];
        }

        return [
            'month' => $month,
            'today' => $today,
            'totals' => [
                'income' => (string) $income,
                'expenses' => (string) $expenses,
                'net' => (string) $income->minus($expenses),
            ],
            'days' => $days,
        ];
    }
}
