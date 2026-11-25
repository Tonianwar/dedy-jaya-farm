<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'farmName' => SiteSetting::get('farm_name', 'Dedy Jaya Farm'),
            'publicUrl' => SiteSetting::get('public_url', config('app.url')),
            'whatsapp' => SiteSetting::get('whatsapp'),
            'heroEyebrow' => SiteSetting::get('hero_eyebrow', 'PETERNAKAN SAPI · BANYUMAS'),
            'heroTitle' => SiteSetting::get('hero_title', 'Ternak terpilih. Dirawat dengan perhatian.'),
            'heroDescription' => SiteSetting::get('hero_description', 'Kenali sapi pilihan Dedy Jaya Farm melalui profil dan riwayat perawatan yang tercatat.'),
            'aboutTitle' => SiteSetting::get('about_title', 'Peternakan sapi potong di Banyumas.'),
            'aboutDescription' => SiteSetting::get('about_description', 'Kami merawat dan memantau sapi secara rutin. Setiap bobot, pemeriksaan, dan perawatan dicatat agar pelanggan mengenal kondisi ternak yang dipilih.'),
            'address' => SiteSetting::get('address', 'Purbadana, Kembaran, Banyumas, Jawa Tengah'),
            'contactPhone' => SiteSetting::get('contact_phone', ''),
            'contactEmail' => SiteSetting::get('contact_email', ''),
            'openingHours' => SiteSetting::get('opening_hours', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'farm_name' => ['required', 'string', 'max:80'],
            'public_url' => [
                'required',
                'url:http,https',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $parts = parse_url((string) $value);

                    if (
                        ! is_array($parts)
                        || empty($parts['host'])
                        || isset($parts['user'])
                        || isset($parts['pass'])
                        || isset($parts['query'])
                        || isset($parts['fragment'])
                        || ! in_array($parts['path'] ?? '', ['', '/'], true)
                    ) {
                        $fail('Masukkan domain publik saja, tanpa path, parameter, atau fragmen.');
                    }
                },
            ],
            'whatsapp' => ['nullable', 'regex:/^[0-9]{8,15}$/'],
            'hero_eyebrow' => ['required', 'string', 'max:100'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_description' => ['required', 'string', 'max:500'],
            'about_title' => ['required', 'string', 'max:150'],
            'about_description' => ['required', 'string', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'opening_hours' => ['nullable', 'string', 'max:150'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus berupa 8–15 digit dengan kode negara, tanpa tanda + atau spasi.',
        ]);

        $data['public_url'] = rtrim($data['public_url'], '/');
        SiteSetting::setMany($data);

        return back()->with('success', 'Pengaturan peternakan berhasil disimpan.');
    }
}
