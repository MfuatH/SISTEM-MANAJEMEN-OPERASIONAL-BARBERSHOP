<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OwnerExpenseReport extends MonthlyOwnerReport
{
    public function query(): Builder
    {
        $query = DB::table('expenses')
            ->select(['id', 'incurred_on', 'description', 'amount'])
            ->where('incurred_on', '>=', $this->month->toDateString())
            ->where('incurred_on', '<', $this->month->addMonth()->toDateString())
            ->orderByDesc('incurred_on')
            ->orderByDesc('id');

        if ($this->search !== '') {
            $query->where(function (Builder $matches): void {
                $matches->whereRaw("description LIKE ? ESCAPE '!'", [$this->searchPattern()]);

                if (preg_match('/\A(?:EXP-)?([1-9][0-9]{0,19})\z/i', $this->search, $reference)) {
                    $matches->orWhereRaw('id = CAST(? AS DECIMAL(20,0))', [$reference[1]]);
                }
            });
        }

        return $query;
    }
}
