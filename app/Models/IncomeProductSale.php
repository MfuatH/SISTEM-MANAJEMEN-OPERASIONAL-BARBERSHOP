<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'product_name', 'quantity', 'unit_price'])]
class IncomeProductSale extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'unit_price' => 'decimal:0'];
    }

    public function incomeTransaction(): BelongsTo
    {
        return $this->belongsTo(IncomeTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
