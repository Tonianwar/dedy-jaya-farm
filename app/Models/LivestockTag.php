<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'token', 'slug', 'cow_id'])]
class LivestockTag extends Model
{
    public function cow(): BelongsTo
    {
        return $this->belongsTo(Cow::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LivestockTagAssignment::class);
    }
}
