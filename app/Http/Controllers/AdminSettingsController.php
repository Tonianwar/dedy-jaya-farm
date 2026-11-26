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
            'instagramUrl' => SiteSetting::get('instagram_url', 'https://www.instagram.com/dedyjayafarm/'),
            'googleMapsUrl' => SiteSetting::get('google_maps_url', 'https://www.google.com/maps/place/Dedy+Jaya+Farm/data=!4m2!3m1!1s0x0:0xc51157099675347b?sa=X&ved=1t:2428&ictx=111'),
            'heroEyebrow' => SiteSetting::get('hero_eyebrow', 'PETERNAKAN SAPI · BANYUMAS'),
            'heroTitle' => SiteSetting::get('hero_title', 'Ternak terpilih. Dirawat dengan perhatian.'),
            'heroDescription' => SiteSetting::get('hero_description', 'Kenali sapi pilihan Dedy Jaya Farm melalui profil dan riwayat perawatan yang tercatat.'),
            'homeSections' => [
                'home_show_hero' => SiteSetting::get('home_show_hero', '1') === '1',
                'home_show_traceability' => SiteSetting::get('home_show_traceability', '1') === '1',
                'home_show_featured' => SiteSetting::get('home_show_featured', '1') === '1',
                'home_show_care_story' => SiteSetting::get('home_show_care_story', '1') === '1',
                'home_show_contact' => SiteSetting::get('home_show_contact', '1') === '1',
            ],
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
            'instagram_url' => [
                'nullable',
                'url:https',
                'max:500',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== null && ! in_array(parse_url((string) $value, PHP_URL_HOST), ['instagram.com', 'www.instagram.com'], true)) {
                        $fail('Masukkan tautan akun Instagram yang valid.');
                    }
                },
            ],
            'google_maps_url' => [
                'nullable',
                'url:https',
                'max:500',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $host = $value !== null ? parse_url((string) $value, PHP_URL_HOST) : null;
                    $path = $value !== null ? parse_url((string) $value, PHP_URL_PATH) : null;

                    if (
                        ! in_array($host, ['google.com', 'www.google.com', 'maps.google.com'], true)
                        || ! is_string($path)
                        || ! str_starts_with($path, '/maps')
                    ) {
                        $fail('Masukkan tautan Google Maps yang valid.');
                    }
                },
            ],
            'hero_eyebrow' => ['required', 'string', 'max:100'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_description' => ['required', 'string', 'max:500'],
            'home_show_hero' => ['sometimes', 'boolean'],
            'home_show_traceability' => ['sometimes', 'boolean'],
            'home_show_featured' => ['sometimes', 'boolean'],
            'home_show_care_story' => ['sometimes', 'boolean'],
            'home_show_contact' => ['sometimes', 'boolean'],
            'address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'opening_hours' => ['nullable', 'string', 'max:150'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus berupa 8–15 digit dengan kode negara, tanpa tanda + atau spasi.',
        ]);

        $data['public_url'] = rtrim($data['public_url'], '/');
        foreach ([
            'home_show_hero',
            'home_show_traceability',
            'home_show_featured',
            'home_show_care_story',
            'home_show_contact',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $request->boolean($key) ? '1' : '0';
            }
        }

        SiteSetting::setMany($data);

        return back()->with('success', 'Pengaturan peternakan berhasil disimpan.');
    }
}
