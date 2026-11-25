@extends('layouts.app')

@section('title', 'Katalog sapi')

@section('content')
    <section class="catalog-hero catalog-hero-compact">
        <div class="hero-copy">
            <p class="eyebrow">{{ $heroEyebrow }}</p>
            <h1>{{ $heroTitle }}</h1>
            <p class="hero-description">{{ $heroDescription }}</p>
            <a class="button button-light" href="#daftar-sapi">Jelajahi katalog <span aria-hidden="true">↓</span></a>
        </div>
        <div class="hero-stamp">
            <span class="stamp-top">DEDY JAYA</span>
            <span class="stamp-icon">✳</span>
            <span class="stamp-bottom">FARM · EST. 2026</span>
        </div>
        <div class="hero-number">01<span> / TRACEABILITY</span></div>
    </section>

    <section class="catalog-section" id="daftar-sapi">
        <div class="section-heading">
            <div>
                <p class="eyebrow eyebrow-green">KATALOG TERSEDIA</p>
                <h2>Sapi siap dipilih</h2>
            </div>
            <span class="result-count">{{ $cows->total() }} ekor tersedia</span>
        </div>

        <form class="search-form" method="GET" action="{{ route('catalog') }}">
            <label class="sr-only" for="catalog-search">Cari sapi</label>
            <input id="catalog-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama, nomor tag, atau ras sapi">
            <button class="button button-dark" type="submit">Cari sapi</button>
            @if ($search)
                <a class="text-link" href="{{ route('catalog') }}">Hapus pencarian</a>
            @endif
        </form>

        @if ($cows->isNotEmpty())
            <div class="cow-grid">
                @foreach ($cows as $cow)
                    <article class="cow-card">
                        <a class="cow-card-image" href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}">
                            @if ($cow->photo_url)
                                <img src="{{ $cow->photo_url }}" alt="Foto {{ $cow->display_name }}" loading="lazy">
                            @else
                                <span class="image-placeholder" aria-hidden="true">✳</span>
                            @endif
                            <span class="status-pill status-available">Tersedia</span>
                        </a>
                        <div class="cow-card-body">
                            <div class="cow-card-meta"><span>{{ $cow->breed }}</span><span>TAG {{ $cow->tag_number }}</span></div>
                            <h3><a href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}">{{ $cow->display_name }}</a></h3>
                            <div class="cow-card-stats">
                                <span>Bobot terkini <strong>{{ $cow->current_weight ? number_format((float) $cow->current_weight, 0, ',', '.') : '—' }} kg</strong></span>
                                <span>Jenis kelamin <strong>{{ ucfirst($cow->gender) }}</strong></span>
                            </div>
                            <div class="cow-card-footer">
                                <strong class="price">{{ $cow->price !== null ? 'Rp '.number_format($cow->price, 0, ',', '.') : 'Hubungi kami' }}</strong>
                                <a class="arrow-link" href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}" aria-label="Lihat detail {{ $cow->display_name }}">↗</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="pagination-wrap">{{ $cows->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon">✳</span>
                <h3>{{ $search ? 'Sapi tidak ditemukan' : 'Belum ada sapi di katalog' }}</h3>
                <p>{{ $search ? 'Coba kata kunci lain atau lihat semua sapi yang tersedia.' : 'Silakan kembali lagi nanti untuk melihat sapi yang tersedia.' }}</p>
                @if ($search)
                    <a class="button button-outline" href="{{ route('catalog') }}">Lihat semua sapi</a>
                @endif
            </div>
        @endif
    </section>
@endsection
