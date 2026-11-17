<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'incurred_on', 'description', 'amount', 'recorded_by_user_id', 'created_at', 'request_key', 'request_hash',
])]
class Expense extends Model
{
    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $hidden = ['request_key', 'request_hash'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:0', 'created_at' => 'immutable_datetime'];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public static function reference(string|int $id): string
    {
        return 'EXP-'.$id;
    }
}
