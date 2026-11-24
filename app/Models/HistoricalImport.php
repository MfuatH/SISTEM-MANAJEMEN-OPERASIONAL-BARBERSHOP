<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImport extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['imported_at' => 'immutable_datetime'];
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
