<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\HealthRecord;
use App\Support\YouTube;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminHealthRecordController extends Controller
{
    public function store(Request $request, Cow $cow): RedirectResponse
    {
        $data = $request->validate([
            'recorded_at' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'in:illness,vaccination,medication,care'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'youtube_url' => [
                'nullable',
                'url:http,https',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && ! YouTube::embed($value)) {
                        $fail('Masukkan tautan video YouTube yang valid.');
                    }
                },
            ],
        ]);

        $cow->healthRecords()->create($data);

        return back()->with('success', 'Riwayat kesehatan berhasil dicatat.');
    }

    public function destroy(Cow $cow, HealthRecord $healthRecord): RedirectResponse
    {
        abort_unless($healthRecord->cow_id === $cow->id, 404);

        $healthRecord->delete();

        return back()->with('success', 'Riwayat kesehatan berhasil dihapus.');
    }
}
