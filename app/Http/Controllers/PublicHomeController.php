<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'featuredCows' => Cow::query()
                ->where('status', 'available')
                ->with(['latestWeightLog', 'currentTag'])
                ->latest('created_at')
                ->limit(3)
                ->get(),
            'availableCount' => Cow::query()->where('status', 'available')->count(),
            'farmName' => SiteSetting::get('farm_name', 'Dedy Jaya Farm'),
            'heroEyebrow' => SiteSetting::get('hero_eyebrow', 'PETERNAKAN SAPI · BANYUMAS'),
            'heroTitle' => SiteSetting::get('hero_title', 'Ternak terpilih. Dirawat dengan perhatian.'),
            'heroDescription' => SiteSetting::get('hero_description', 'Kenali sapi pilihan Dedy Jaya Farm melalui profil dan riwayat perawatan yang tercatat.'),
            'showHomeHero' => SiteSetting::get('home_show_hero', '1') === '1',
            'showHomeTraceability' => SiteSetting::get('home_show_traceability', '1') === '1',
            'showHomeFeatured' => SiteSetting::get('home_show_featured', '1') === '1',
            'showHomeCareStory' => SiteSetting::get('home_show_care_story', '1') === '1',
            'showHomeContact' => SiteSetting::get('home_show_contact', '1') === '1',
            'address' => SiteSetting::get('address', 'Purbadana, Kembaran, Banyumas, Jawa Tengah'),
            'contactPhone' => SiteSetting::get('contact_phone'),
            'contactEmail' => SiteSetting::get('contact_email'),
            'openingHours' => SiteSetting::get('opening_hours', 'Senin–Sabtu, 08.00–16.00 WIB'),
            'whatsapp' => SiteSetting::get('whatsapp'),
            'instagramUrl' => SiteSetting::get('instagram_url', 'https://www.instagram.com/dedyjayafarm/'),
            'googleMapsUrl' => SiteSetting::get('google_maps_url', 'https://www.google.com/maps/place/Dedy+Jaya+Farm/data=!4m2!3m1!1s0x0:0xc51157099675347b?sa=X&ved=1t:2428&ictx=111'),
        ]);
    }
}
