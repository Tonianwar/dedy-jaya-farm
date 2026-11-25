<?php

namespace App\Models;

use App\Support\YouTube;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cow_id', 'recorded_at', 'category', 'title', 'description', 'youtube_url'])]
class HealthRecord extends Model
{
    protected function casts(): array
    {
        return ['recorded_at' => 'date'];
    }

    public function cow(): BelongsTo
    {
        return $this->belongsTo(Cow::class);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        return YouTube::embed($this->youtube_url);
    }
}
