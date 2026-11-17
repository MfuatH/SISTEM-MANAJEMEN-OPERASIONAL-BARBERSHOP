<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;

class OwnerIncomeReport extends MonthlyOwnerReport
{
    public function query(): Builder
    {
        $query = IncomeItems::query()
            ->where('tx.recorded_at', '>=', $this->month->utc())
            ->where('tx.recorded_at', '<', $this->month->addMonth()->utc())
            ->orderByDesc('tx.recorded_at')
            ->orderByDesc('tx.id')
            ->orderBy('items.item_type')
            ->orderBy('items.item_id');

        if ($this->search !== '') {
            $pattern = $this->searchPattern();
            $query->where(function (Builder $matches) use ($pattern): void {
                $matches->whereRaw("items.item_name LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("tx.barber_name LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("tx.customer_name LIKE ? ESCAPE '!'", [$pattern]);

                if (ctype_digit($this->search)) {
                    $matches->orWhereRaw('CAST(tx.id AS CHAR) = ?', [$this->search]);
                }
            });
        }

        return $query;
    }

    public static function localTime(string $recordedAt): CarbonImmutable
    {
        return CarbonImmutable::parse($recordedAt, 'UTC')->setTimezone(self::TIMEZONE);
    }

    public static function itemType(string $type): string
    {
        return $type === 'service' ? 'Layanan' : 'Penjualan produk';
    }
}
