<?php

namespace App\Services;

use App\Models\Expense;
use Brick\Math\BigInteger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OwnerRecapReport extends OwnerReport
{
    public const TYPES = ['IN' => 'Pemasukan', 'OUT' => 'Pengeluaran'];

    private readonly ?CarbonImmutable $month;

    public function __construct(
        public readonly ?string $period,
        public readonly ?string $type,
        string $search,
    ) {
        parent::__construct($search);

        $this->month = $period === null ? null : CarbonImmutable::createFromFormat('!Y-m', $period, self::TIMEZONE);
    }

    public function query(): Builder
    {
        $query = $this->periodItems()
            ->orderByDesc('business_date')
            ->orderByDesc('recorded_at')
            ->orderBy('item_type')
            ->orderByDesc('source_id');

        if ($this->type !== null) {
            $query->where('entry_type', $this->type);
        }

        if ($this->search !== '') {
            $query->where(function (Builder $matches): void {
                $pattern = $this->searchPattern();
                $matches->whereRaw("label LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("barber_name LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("customer_name LIKE ? ESCAPE '!'", [$pattern]);

                if (preg_match('/\A(?:EXP-)?([1-9][0-9]{0,19})\z/i', $this->search, $reference)) {
                    $matches->orWhere(fn (Builder $expense) => $expense->where('entry_type', 'OUT')
                        ->whereRaw('source_id = CAST(? AS DECIMAL(20,0))', [$reference[1]]));

                    if ($reference[0] === $reference[1]) {
                        $matches->orWhereRaw('transaction_id = CAST(? AS DECIMAL(20,0))', [$reference[1]]);
                    }
                }
            });
        }

        return $query;
    }

    /** @return array{income: string, expenses: string, net: string} */
    public function summary(): array
    {
        $totals = $this->periodItems()
            ->selectRaw("COALESCE(SUM(CASE WHEN entry_type = 'IN' THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN entry_type = 'OUT' THEN amount ELSE 0 END), 0) AS expenses")
            ->first();

        return [
            'income' => (string) $totals->income,
            'expenses' => (string) $totals->expenses,
            'net' => (string) BigInteger::of($totals->income)->minus($totals->expenses),
        ];
    }

    /** @return array<string, string> */
    public function filters(): array
    {
        return array_filter(
            ['month' => $this->period, 'type' => $this->type, 'q' => $this->search],
            fn (?string $value): bool => $value !== null && $value !== '',
        );
    }

    public function periodLabel(): string
    {
        return $this->month?->locale('id')->translatedFormat('F Y') ?? 'Seluruh periode';
    }

    public static function reference(object $item): string
    {
        return $item->entry_type === 'OUT' ? Expense::reference($item->source_id) : (string) $item->transaction_id;
    }

    public static function category(string $itemType): string
    {
        return $itemType === 'expense' ? 'Pengeluaran' : OwnerIncomeReport::itemType($itemType);
    }

    private function periodItems(): Builder
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

        if ($this->month !== null) {
            $income->where('tx.recorded_at', '>=', $this->month->utc())
                ->where('tx.recorded_at', '<', $this->month->addMonth()->utc());
            $expenses->whereBetween('incurred_on', [$this->month->toDateString(), $this->month->endOfMonth()->toDateString()]);
        }

        return DB::query()->fromSub($income->unionAll($expenses), 'recap');
    }
}
