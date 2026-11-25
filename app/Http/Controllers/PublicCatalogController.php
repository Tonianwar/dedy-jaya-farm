<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $cows = Cow::query()
            ->where('status', 'available')
            ->with(['latestWeightLog', 'currentTag'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tag_number', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('breed', 'like', '%'.$search.'%');
                });
            })
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('public.catalog', [
            'cows' => $cows,
            'search' => $search,
            'farmName' => SiteSetting::get('farm_name', 'Dedy Jaya Farm'),
            'heroEyebrow' => SiteSetting::get('hero_eyebrow', 'PETERNAKAN SAPI · BANYUMAS'),
            'heroTitle' => SiteSetting::get('hero_title', 'Ternak terpilih. Dirawat dengan perhatian.'),
            'heroDescription' => SiteSetting::get('hero_description', 'Kenali sapi pilihan Dedy Jaya Farm melalui profil dan riwayat perawatan yang tercatat.'),
        ]);
    }

    public function show(string $slug)
    {
        $tag = LivestockTag::query()->with('cow')->where('slug', $slug)->first();

        if ($tag) {
            if (! $tag->cow) {
                return view('public.tags.unassigned', ['tag' => $tag]);
            }

            $cow = $tag->cow;
        } else {
            $cow = Cow::query()->where('slug', $slug)->firstOrFail();
        }

        $cow->load([
            'latestWeightLog',
            'weightLogs' => fn ($query) => $query->latest('weighed_at'),
            'healthRecords' => fn ($query) => $query->latest('recorded_at'),
        ]);

        $whatsapp = SiteSetting::get('whatsapp');
        $message = rawurlencode('Halo, saya tertarik dengan '.$cow->display_name.' ('.$cow->tag_number.').');

        return view('public.cows.show', [
            'cow' => $cow,
            'whatsappUrl' => $whatsapp ? 'https://wa.me/'.$whatsapp.'?text='.$message : null,
            'farmName' => SiteSetting::get('farm_name', 'Dedy Jaya Farm'),
        ]);
    }
}
