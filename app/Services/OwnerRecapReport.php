<?php

namespace App\Services;

use App\Models\Expense;
use Brick\Math\BigInteger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;

class OwnerRecapReport extends OwnerReport
{
    public const TYPES = ['IN' => 'Pemasukan', 'OUT' => 'Pengeluaran'];

    public const DETAIL_TOTAL_LABEL = 'Total nominal rincian';

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
        $query = OwnerFinancialEntries::query($this->month)
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
        return $this->totals(OwnerFinancialEntries::query($this->month));
    }

    /** @return array<string, string> */
    public function detailTotals(): array
    {
        $details = $this->query();
        if ($this->type !== null) {
            return [self::DETAIL_TOTAL_LABEL => (string) $details->sum('amount')];
        }

        $totals = $this->totals($details);

        return [
            self::DETAIL_TOTAL_LABEL => (string) BigInteger::of($totals['income'])->plus($totals['expenses']),
            'Total pendapatan kotor' => $totals['income'],
            'Total pendapatan bersih' => $totals['net'],
        ];
    }

    /** @return array{income: string, expenses: string, net: string} */
    private function totals(Builder $items): array
    {
        $totals = OwnerFinancialEntries::selectTotals($items->reorder())->first();

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
}
