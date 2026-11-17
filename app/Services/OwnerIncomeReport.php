<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;

class OwnerIncomeReport
{
    public const TIMEZONE = 'Asia/Jakarta';

    public readonly CarbonImmutable $month;

    public function __construct(public readonly string $search)
    {
        $this->month = CarbonImmutable::now(self::TIMEZONE)->startOfMonth();
    }

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
            $pattern = '%'.strtr($this->search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
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

    public static function rupiah(string|int $amount): string
    {
        return 'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $amount);
    }
}
