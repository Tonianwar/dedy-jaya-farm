<?php

namespace App\Http\Controllers;

use App\Models\LivestockTag;
use Illuminate\Http\RedirectResponse;

class PublicTagController extends Controller
{
    public function show(LivestockTag $tag): RedirectResponse
    {
        return redirect()->route('cows.show', ['slug' => $tag->slug]);
    }
}
