<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'barber_name',
    'customer_name',
    'service_id',
    'service_name',
    'amount',
    'recorded_at',
    'session_scope',
    'request_key',
    'request_hash',
])]
class IncomeEntry extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $hidden = ['session_scope', 'request_key', 'request_hash'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'recorded_at' => 'immutable_datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
