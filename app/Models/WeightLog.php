<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cow_id', 'weighed_at', 'weight_kg', 'note', 'photo_path'])]
class WeightLog extends Model
{
    protected function casts(): array
    {
        return [
            'weighed_at' => 'date',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function cow(): BelongsTo
    {
        return $this->belongsTo(Cow::class);
    }
}
