<?php

namespace App\Support;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Models\SiteSetting;

class CowPublicUrl
{
    public static function for(Cow $cow): string
    {
        $baseUrl = SiteSetting::get('public_url', config('app.url'));

        $cow->loadMissing('currentTag');

        return rtrim((string) $baseUrl, '/').route('cows.show', ['slug' => $cow->currentTag?->slug ?? $cow->slug], false);
    }

    public static function forTag(LivestockTag $tag): string
    {
        $baseUrl = SiteSetting::get('public_url', config('app.url'));

        return rtrim((string) $baseUrl, '/').route('cows.show', ['slug' => $tag->slug], false);
    }
}
