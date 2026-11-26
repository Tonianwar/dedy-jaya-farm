<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dedy Jaya Farm') · {{ \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/dedy-jaya-farm-logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('images/dedy-jaya-farm-logo.png') }}" alt="">
                <span>{{ \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm') }}<small>SMART LIVESTOCK</small></span>
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('catalog') }}">Katalog</a>
                <a href="{{ route('home') }}#footer-contact">Kontak</a>
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <a href="{{ route('admin.cows.index') }}">Data sapi</a>
                        <a href="{{ route('admin.tags.index') }}">QR ternak</a>
                        <a href="{{ route('admin.settings.edit') }}">Pengaturan</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="nav-button" type="submit">Keluar</button>
                        </form>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="page-shell">
        @include('shared.flash')
        @yield('content')
    </main>

    <footer class="site-footer">
        @php
            $footerFarmName = \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm');
            $footerAddress = \App\Models\SiteSetting::get('address', 'Purbadana, Kembaran, Banyumas, Jawa Tengah');
            $footerWhatsApp = \App\Models\SiteSetting::get('whatsapp');
            $footerInstagram = \App\Models\SiteSetting::get('instagram_url', 'https://www.instagram.com/dedyjayafarm/');
            $footerMaps = \App\Models\SiteSetting::get('google_maps_url', 'https://www.google.com/maps/place/Dedy+Jaya+Farm/data=!4m2!3m1!1s0x0:0xc51157099675347b?sa=X&ved=1t:2428&ictx=111');
            $mapEmbedQuery = rawurlencode($footerFarmName.' '.$footerAddress);
        @endphp
        <div class="footer-main">
            <section class="footer-brand">
                <a class="footer-brand-lockup" href="{{ route('home') }}">
                    <img src="{{ asset('images/dedy-jaya-farm-logo.png') }}" alt="Logo {{ $footerFarmName }}" loading="lazy">
                    <span>{{ $footerFarmName }}<small>Peternakan · Banyumas</small></span>
                </a>
                <p>Peternakan dengan perhatian dan keterbukaan. Kenali cerita di balik setiap ternak.</p>
                <div class="footer-social-links">
                    @if ($footerInstagram)
                        <a href="{{ $footerInstagram }}" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi Instagram {{ $footerFarmName }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle class="icon-fill" cx="18" cy="6.5" r="1"></circle></svg>
                            Instagram
                        </a>
                    @endif
                    @if ($footerWhatsApp)
                        <a href="https://wa.me/{{ $footerWhatsApp }}" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.7a8 8 0 0 1-11.8 7L4 20l1.3-4A8 8 0 1 1 20 11.7Z"></path><path d="M9 8.5c.3 2.4 2.1 4.2 4.5 4.5"></path></svg>
                            WhatsApp
                        </a>
                    @endif
                </div>
            </section>
            <section class="footer-contact" id="footer-contact">
                <h2>Kunjungi kami</h2>
                <p>{{ $footerAddress }}</p>
                <a href="{{ $footerMaps }}" target="_blank" rel="noopener noreferrer">Buka petunjuk arah <span aria-hidden="true">↗</span></a>
                @if ($footerInstagram)
                    <a class="footer-instagram-url" href="{{ $footerInstagram }}" target="_blank" rel="noopener noreferrer">@dedyjayafarm di Instagram</a>
                @endif
            </section>
            <div class="footer-map">
                <iframe
                    src="https://www.google.com/maps?output=embed&amp;q={{ $mapEmbedQuery }}"
                    title="Peta lokasi {{ $footerFarmName }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ now()->year }} {{ $footerFarmName }}. Semua hak dilindungi.</span>
            <a href="{{ route('catalog') }}">Jelajahi katalog sapi <span aria-hidden="true">↗</span></a>
        </div>
    </footer>

    @if ($footerWhatsApp && ! request()->is('admin', 'admin/*'))
        <a class="whatsapp-float" href="https://wa.me/{{ $footerWhatsApp }}" target="_blank" rel="noopener noreferrer" aria-label="Hubungi {{ $footerFarmName }} melalui WhatsApp">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.7a8 8 0 0 1-11.8 7L4 20l1.3-4A8 8 0 1 1 20 11.7Z"></path><path d="M9 8.5c.3 2.4 2.1 4.2 4.5 4.5"></path></svg>
            <span>Chat WhatsApp</span>
        </a>
    @endif
</body>
</html>
