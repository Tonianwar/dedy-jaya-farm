<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Services\LivestockTagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCowController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $cows = Cow::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tag_number', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('breed', 'like', '%'.$search.'%');
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cows.index', compact('cows', 'search'));
    }

    public function create(): View
    {
        $reusableTags = LivestockTag::query()
            ->whereNull('cow_id')
            ->orderBy('code')
            ->get();

        return view('admin.cows.form', ['cow' => new Cow, 'reusableTags' => $reusableTags]);
    }

    public function store(Request $request, LivestockTagService $tags): RedirectResponse
    {
        $data = $this->validatedCow($request);
        $photo = $data['photo'] ?? null;
        $tagId = $data['livestock_tag_id'] ?? null;
        unset($data['photo']);
        unset($data['livestock_tag_id']);

        $data['slug'] = Str::slug(($data['name'] ?: 'sapi').'-'.$data['tag_number'])
            .'-'.Str::lower(Str::random(6));
        $data['status'] = 'available';
        if ($photo) {
            $photoPath = $photo->store('cows', 'public');
            throw_unless(is_string($photoPath), new \RuntimeException('Foto sapi gagal disimpan.'));
            $data['photo_path'] = $photoPath;
        }

        $cow = DB::transaction(function () use ($data, $tagId, $tags) {
            $cow = Cow::create($data);

            if ($tagId) {
                $tag = LivestockTag::query()->lockForUpdate()->findOrFail($tagId);
                $tags->assign($tag, $cow);
            }

            if ($cow->initial_weight_kg) {
                $cow->weightLogs()->create([
                    'weighed_at' => $cow->arrived_at->toDateString(),
                    'weight_kg' => $cow->initial_weight_kg,
                    'note' => 'Berat awal saat registrasi.',
                ]);
            }

            return $cow;
        });

        return redirect()
            ->route('admin.cows.show', $cow)
            ->with('success', 'Data sapi berhasil ditambahkan sebagai individu baru.');
    }

    public function show(Cow $cow): View
    {
        $cow->load([
            'weightLogs' => fn ($query) => $query->latest('weighed_at'),
            'healthRecords' => fn ($query) => $query->latest('recorded_at'),
            'currentTag',
            'tagAssignments' => fn ($query) => $query->with('tag')->latest('assigned_at'),
        ]);

        $reusableTags = LivestockTag::query()
            ->where(function ($query) {
                $query->whereNull('cow_id')
                    ->orWhereHas('cow', fn ($query) => $query->where('status', 'sold'));
            })
            ->orderBy('code')
            ->get();

        return view('admin.cows.show', compact('cow', 'reusableTags'));
    }

    public function edit(Cow $cow): View
    {
        return view('admin.cows.form', ['cow' => $cow, 'reusableTags' => collect()]);
    }

    public function update(Request $request, Cow $cow): RedirectResponse
    {
        $data = $this->validatedCow($request, $cow);
        $photo = $data['photo'] ?? null;
        unset($data['photo']);

        if ($photo) {
            $photoPath = $photo->store('cows', 'public');
            throw_unless(is_string($photoPath), new \RuntimeException('Foto sapi gagal disimpan.'));
            $data['photo_path'] = $photoPath;
        }

        $cow->update($data);

        return redirect()
            ->route('admin.cows.show', $cow)
            ->with('success', 'Data sapi berhasil diperbarui.');
    }

    public function markSold(Cow $cow, LivestockTagService $tags): RedirectResponse
    {
        abort_unless($cow->status === 'available', 422);

        DB::transaction(function () use ($cow, $tags) {
            $cow->update([
                'status' => 'sold',
                'sold_at' => now(),
            ]);
            $tags->releaseCowTag($cow);
        });

        return redirect()
            ->route('admin.cows.show', $cow)
            ->with('success', 'Sapi ditandai terjual. QR yang sama kini siap dipasangkan ke sapi berikutnya; seluruh histori sapi lama tetap tersimpan.');
    }

    public function assignTag(Request $request, Cow $cow, LivestockTagService $tags): RedirectResponse
    {
        $data = $request->validate([
            'tag_id' => ['required', 'integer', 'exists:livestock_tags,id'],
        ]);
        $tag = LivestockTag::query()->findOrFail($data['tag_id']);
        $tags->assign($tag, $cow);

        return back()->with('success', 'QR fisik '.$tag->code.' sekarang mengarah ke '.$cow->display_name.'. QR tidak perlu dicetak ulang.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCow(Request $request, ?Cow $cow = null): array
    {
        return $request->validate([
            'tag_number' => ['required', 'string', 'max:80', 'unique:cows,tag_number,'.$cow?->id],
            'name' => ['nullable', 'string', 'max:100'],
            'breed' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:jantan,betina'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'arrived_at' => ['required', 'date', 'before_or_equal:today'],
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'initial_weight_kg' => ['nullable', 'numeric', 'min:1', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'livestock_tag_id' => [
                $cow ? 'prohibited' : 'nullable',
                'integer',
                'exists:livestock_tags,id',
            ],
        ]);
    }
}
