<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['livestock_tag_id', 'cow_id', 'assigned_at', 'released_at'])]
class LivestockTagAssignment extends Model
{
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(LivestockTag::class, 'livestock_tag_id');
    }

    public function cow(): BelongsTo
    {
        return $this->belongsTo(Cow::class);
    }
}
