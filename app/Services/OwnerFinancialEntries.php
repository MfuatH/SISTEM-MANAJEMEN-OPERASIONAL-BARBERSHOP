<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OwnerFinancialEntries
{
    public static function query(?CarbonImmutable $month): Builder
    {
        $income = IncomeItems::query()
            ->select([])
            ->selectRaw("'IN' AS entry_type, items.item_type, items.item_id AS source_id,
                tx.id AS transaction_id, DATE(tx.recorded_at + INTERVAL 7 HOUR) AS business_date,
                tx.recorded_at, tx.barber_name, tx.customer_name, items.item_name AS label, items.amount");

        $expenses = DB::table('expenses')
            ->selectRaw("'OUT' AS entry_type, 'expense' AS item_type, id AS source_id,
                NULL AS transaction_id, incurred_on AS business_date, NULL AS recorded_at,
                NULL AS barber_name, NULL AS customer_name, description AS label, amount");

        if ($month !== null) {
            $income->where('tx.recorded_at', '>=', $month->utc())
                ->where('tx.recorded_at', '<', $month->addMonth()->utc());
            $expenses->whereBetween('incurred_on', [$month->toDateString(), $month->endOfMonth()->toDateString()]);
        }

        return DB::query()->fromSub($income->unionAll($expenses), 'financial_entries');
    }

    public static function selectTotals(Builder $entries): Builder
    {
        return $entries->selectRaw("COALESCE(SUM(CASE WHEN entry_type = 'IN' THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN entry_type = 'OUT' THEN amount ELSE 0 END), 0) AS expenses");
    }
}
