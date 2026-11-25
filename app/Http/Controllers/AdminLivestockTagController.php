<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\LivestockTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminLivestockTagController extends Controller
{
    public function index(): View
    {
        $tags = LivestockTag::query()
            ->with('cow')
            ->withCount('assignments')
            ->orderBy('code')
            ->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:livestock_tags,code'],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash:ascii', 'unique:livestock_tags,slug'],
        ]);

        $baseSlug = $data['slug'] ?? preg_replace('/^qr-/i', '', $data['code']);
        $slug = Str::slug($baseSlug);

        if ($slug === '') {
            return back()->withInput()->withErrors(['slug' => 'Slug QR harus berisi huruf atau angka.']);
        }

        if (Cow::query()->where('slug', $slug)->exists()) {
            return back()->withInput()->withErrors(['slug' => 'Slug QR sudah digunakan oleh profil sapi.']);
        }

        $tag = LivestockTag::query()->create([
            'code' => $data['code'],
            'slug' => $slug,
            'token' => bin2hex(random_bytes(20)),
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'QR '.$tag->code.' berhasil dibuat dengan URL pendek /sapi/'.$tag->slug.'.');
    }
}
