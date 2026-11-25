<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'tag_number',
    'slug',
    'name',
    'breed',
    'gender',
    'birth_date',
    'arrived_at',
    'price',
    'initial_weight_kg',
    'description',
    'photo_path',
    'status',
    'sold_at',
])]
class Cow extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'arrived_at' => 'date',
            'sold_at' => 'datetime',
            'price' => 'integer',
            'initial_weight_kg' => 'decimal:2',
        ];
    }

    public function weightLogs(): HasMany
    {
        return $this->hasMany(WeightLog::class);
    }

    public function latestWeightLog(): HasOne
    {
        return $this->hasOne(WeightLog::class)->latestOfMany('weighed_at');
    }

    public function currentTag(): HasOne
    {
        return $this->hasOne(LivestockTag::class);
    }

    public function tagAssignments(): HasMany
    {
        return $this->hasMany(LivestockTagAssignment::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function getCurrentWeightAttribute(): ?string
    {
        return $this->latestWeightLog?->weight_kg ?? $this->initial_weight_kg;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Sapi '.$this->tag_number;
    }

    public function getAgeLabelAttribute(): ?string
    {
        if (! $this->birth_date) {
            return null;
        }

        $age = $this->birth_date->diff(now());
        $parts = [];

        if ($age->y > 0) {
            $parts[] = $age->y.' tahun';
        }

        if ($age->m > 0) {
            $parts[] = $age->m.' bulan';
        }

        return $parts === [] ? 'Kurang dari 1 bulan' : implode(' ', $parts);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return str_starts_with($this->photo_path, 'images/')
            ? asset($this->photo_path)
            : asset('storage/'.$this->photo_path);
    }
}
