<?php

namespace App\Services;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Models\LivestockTagAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LivestockTagService
{
    public function assign(LivestockTag $tag, Cow $cow): void
    {
        DB::transaction(function () use ($tag, $cow) {
            $tag = LivestockTag::query()->lockForUpdate()->findOrFail($tag->id);
            $cow = Cow::query()->lockForUpdate()->findOrFail($cow->id);

            if ($cow->status !== 'available') {
                throw ValidationException::withMessages([
                    'tag_id' => 'QR hanya dapat dipasangkan ke sapi yang tersedia.',
                ]);
            }

            if ($tag->cow_id && $tag->cow_id !== $cow->id) {
                $previousCow = Cow::query()->findOrFail($tag->cow_id);

                if ($previousCow->status !== 'sold') {
                    throw ValidationException::withMessages([
                        'tag_id' => 'QR ini masih dipasang pada sapi yang belum terjual.',
                    ]);
                }

                $this->release($tag);
            }

            if ($tag->cow_id === $cow->id) {
                return;
            }

            $currentCowTag = LivestockTag::query()
                ->where('cow_id', $cow->id)
                ->lockForUpdate()
                ->first();

            if ($currentCowTag) {
                $this->release($currentCowTag);
            }

            $tag->update(['cow_id' => $cow->id]);
            $tag->assignments()->create([
                'cow_id' => $cow->id,
                'assigned_at' => now(),
            ]);
        });
    }

    public function releaseCowTag(Cow $cow): void
    {
        DB::transaction(function () use ($cow) {
            $tag = LivestockTag::query()
                ->where('cow_id', $cow->id)
                ->lockForUpdate()
                ->first();

            if ($tag) {
                $this->release($tag);
            }
        });
    }

    private function release(LivestockTag $tag): void
    {
        $assignment = LivestockTagAssignment::query()
            ->where('livestock_tag_id', $tag->id)
            ->whereNull('released_at')
            ->latest('assigned_at')
            ->first();

        if ($assignment) {
            $cow = Cow::query()->lockForUpdate()->findOrFail($assignment->cow_id);

            if ($cow->status === 'sold') {
                $archiveNumber = $tag->assignments()->count();
                do {
                    $archiveSlug = $tag->slug.'-sold'.$archiveNumber;
                    $archiveNumber++;
                } while (
                    Cow::query()->where('slug', $archiveSlug)->whereKeyNot($cow->id)->exists()
                    || LivestockTag::query()->where('slug', $archiveSlug)->exists()
                );

                $cow->update(['slug' => $archiveSlug]);
            }

            $assignment->update(['released_at' => now()]);
        }

        $tag->update(['cow_id' => null]);
    }
}
