<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dedy Jaya Farm') · {{ \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">DJ</span>
                <span>{{ \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm') }}<small>SMART LIVESTOCK</small></span>
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('home') }}#tentang">Tentang kami</a>
                <a href="{{ route('catalog') }}">Katalog</a>
                <a href="{{ route('home') }}#kontak">Kontak</a>
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
        <div class="footer-inner">
            <span>{{ \App\Models\SiteSetting::get('farm_name', 'Dedy Jaya Farm') }}</span>
            <span>Peternakan dengan perhatian dan keterbukaan.</span>
        </div>
    </footer>
</body>
</html>
