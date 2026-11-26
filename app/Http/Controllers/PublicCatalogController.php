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
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:100'],
            'weight_min' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'weight_max' => ['nullable', 'numeric', 'min:0', 'max:99999', 'gte:weight_min'],
            'price_sort' => ['nullable', 'in:newest,price_asc,price_desc'],
            'per_page' => ['nullable', 'integer', 'in:6,9'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $breed = $filters['breed'] ?? '';
        $weightMin = isset($filters['weight_min']) ? (float) $filters['weight_min'] : null;
        $weightMax = isset($filters['weight_max']) ? (float) $filters['weight_max'] : null;
        $priceSort = $filters['price_sort'] ?? 'newest';
        $perPage = (int) ($filters['per_page'] ?? 9);

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
            ->when($breed, fn ($query) => $query->where('breed', $breed))
            ->when($weightMin !== null, fn ($query) => $query->whereRaw(
                'CAST(COALESCE((SELECT weight_kg FROM weight_logs WHERE weight_logs.cow_id = cows.id ORDER BY weighed_at DESC, id DESC LIMIT 1), cows.initial_weight_kg) AS DECIMAL(10, 2)) >= CAST(? AS DECIMAL(10, 2))',
                [$weightMin],
            ))
            ->when($weightMax !== null, fn ($query) => $query->whereRaw(
                'CAST(COALESCE((SELECT weight_kg FROM weight_logs WHERE weight_logs.cow_id = cows.id ORDER BY weighed_at DESC, id DESC LIMIT 1), cows.initial_weight_kg) AS DECIMAL(10, 2)) <= CAST(? AS DECIMAL(10, 2))',
                [$weightMax],
            ))
            ->when($priceSort === 'price_asc', fn ($query) => $query
                ->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')
                ->orderBy('price'))
            ->when($priceSort === 'price_desc', fn ($query) => $query
                ->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('price'))
            ->when($priceSort === 'newest', fn ($query) => $query->latest('created_at'))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('public.catalog', [
            'cows' => $cows,
            'search' => $search,
            'breed' => $breed,
            'breeds' => Cow::query()
                ->where('status', 'available')
                ->select('breed')
                ->distinct()
                ->orderBy('breed')
                ->pluck('breed'),
            'weightMin' => $weightMin,
            'weightMax' => $weightMax,
            'priceSort' => $priceSort,
            'perPage' => $perPage,
            'hasFilters' => $search !== '' || $breed !== '' || $weightMin !== null || $weightMax !== null || $priceSort !== 'newest',
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
