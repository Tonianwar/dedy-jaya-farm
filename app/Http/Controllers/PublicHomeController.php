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
            'aboutTitle' => SiteSetting::get('about_title', 'Peternakan sapi potong di Banyumas.'),
            'aboutDescription' => SiteSetting::get('about_description', 'Kami merawat dan memantau sapi secara rutin. Setiap bobot, pemeriksaan, dan perawatan dicatat agar pelanggan mengenal kondisi ternak yang dipilih.'),
            'address' => SiteSetting::get('address', 'Purbadana, Kembaran, Banyumas, Jawa Tengah'),
            'contactPhone' => SiteSetting::get('contact_phone'),
            'contactEmail' => SiteSetting::get('contact_email'),
            'openingHours' => SiteSetting::get('opening_hours', 'Senin–Sabtu, 08.00–16.00 WIB'),
            'whatsapp' => SiteSetting::get('whatsapp'),
        ]);
    }
}
