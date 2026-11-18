<?php

namespace App\Services;

abstract class OwnerReport
{
    public const TIMEZONE = 'Asia/Jakarta';

    public function __construct(public readonly string $search) {}

    protected function searchPattern(): string
    {
        return '%'.strtr($this->search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
    }

    public static function rupiah(string|int $amount): string
    {
        $value = (string) $amount;
        $sign = str_starts_with($value, '-') ? '-' : '';

        return $sign.'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', ltrim($value, '-'));
    }
}
