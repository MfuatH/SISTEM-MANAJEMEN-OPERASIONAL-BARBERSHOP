<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class IncomeItems
{
    public static function query(): Builder
    {
        $services = DB::table('income_entries')
            ->selectRaw("'service' AS item_type, id AS item_id, income_transaction_id, service_name AS item_name,
                used_product_name, 1 AS quantity, amount AS unit_price, amount");

        $productSales = DB::table('income_product_sales')
            ->selectRaw("'product' AS item_type, id AS item_id, income_transaction_id, product_name AS item_name,
                NULL AS used_product_name, quantity, unit_price, quantity * unit_price AS amount");

        return DB::table('income_transactions AS tx')
            ->joinSub($services->unionAll($productSales), 'items', 'items.income_transaction_id', '=', 'tx.id')
            ->select('tx.id AS transaction_id', 'tx.barber_name', 'tx.customer_name', 'tx.recorded_at', 'items.*');
    }
}
