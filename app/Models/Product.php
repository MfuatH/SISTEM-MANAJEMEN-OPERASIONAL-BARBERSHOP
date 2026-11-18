<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'sale_price'])]
class Product extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:0',
            'archived_at' => 'datetime',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(IncomeProductSale::class);
    }

    public function historicalUsage(): HasMany
    {
        return $this->hasMany(IncomeEntry::class, 'used_product_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }
}
