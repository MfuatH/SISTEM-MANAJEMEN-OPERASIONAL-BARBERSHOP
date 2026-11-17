<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_id', 'service_name', 'amount', 'used_product_id', 'used_product_name'])]
class IncomeEntry extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['amount' => 'decimal:0'];
    }

    public function incomeTransaction(): BelongsTo
    {
        return $this->belongsTo(IncomeTransaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function usedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
