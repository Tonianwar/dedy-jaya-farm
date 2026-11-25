<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\WeightLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminWeightLogController extends Controller
{
    public function store(Request $request, Cow $cow): RedirectResponse
    {
        $data = $request->validate([
            'weighed_at' => ['required', 'date', 'before_or_equal:today'],
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $photo = $data['photo'] ?? null;
        unset($data['photo']);
        if ($photo) {
            $photoPath = $photo->store('weighings', 'public');
            throw_unless(is_string($photoPath), new \RuntimeException('Foto penimbangan gagal disimpan.'));
            $data['photo_path'] = $photoPath;
        }

        $cow->weightLogs()->create($data);

        return back()->with('success', 'Data penimbangan berhasil dicatat.');
    }

    public function destroy(Cow $cow, WeightLog $weightLog): RedirectResponse
    {
        abort_unless($weightLog->cow_id === $cow->id, 404);

        if ($weightLog->photo_path) {
            Storage::disk('public')->delete($weightLog->photo_path);
        }

        $weightLog->delete();

        return back()->with('success', 'Catatan penimbangan berhasil dihapus.');
    }
}
