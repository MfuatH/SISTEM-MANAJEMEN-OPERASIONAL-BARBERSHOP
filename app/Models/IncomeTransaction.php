<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['barber_name', 'customer_name', 'recorded_at', 'session_scope', 'request_key', 'request_hash'])]
class IncomeTransaction extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $hidden = ['session_scope', 'request_key', 'request_hash'];

    protected function casts(): array
    {
        return ['recorded_at' => 'immutable_datetime'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(IncomeProductSale::class);
    }
}
