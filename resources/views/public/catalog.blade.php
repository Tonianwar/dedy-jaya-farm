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

        <form class="catalog-filter-form" method="GET" action="{{ route('catalog') }}" data-responsive-page-size>
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="catalog-filters-grid">
                <label for="catalog-search">Cari sapi
                    <input id="catalog-search" type="search" name="q" value="{{ $search }}" placeholder="Nama, nomor tag, atau ras">
                </label>
                <label for="catalog-breed">Jenis sapi
                    <select id="catalog-breed" name="breed">
                        <option value="">Semua jenis</option>
                        @foreach ($breeds as $availableBreed)
                            <option value="{{ $availableBreed }}" @selected($breed === $availableBreed)>{{ $availableBreed }}</option>
                        @endforeach
                    </select>
                </label>
                <fieldset class="weight-range">
                    <legend>Berat badan (kg)</legend>
                    <div>
                        <label class="sr-only" for="weight-min">Berat minimum (kg)</label>
                        <input id="weight-min" type="number" name="weight_min" min="0" max="99999" step="0.1" value="{{ $weightMin }}" placeholder="Minimum">
                        <label class="sr-only" for="weight-max">Berat maksimum (kg)</label>
                        <input id="weight-max" type="number" name="weight_max" min="0" max="99999" step="0.1" value="{{ $weightMax }}" placeholder="Maksimum">
                    </div>
                </fieldset>
                <label for="catalog-price-sort">Urutkan harga
                    <select id="catalog-price-sort" name="price_sort">
                        <option value="newest" @selected($priceSort === 'newest')>Terbaru</option>
                        <option value="price_asc" @selected($priceSort === 'price_asc')>Termurah</option>
                        <option value="price_desc" @selected($priceSort === 'price_desc')>Termahal</option>
                    </select>
                </label>
            </div>
            <div class="catalog-filter-actions">
                <button class="button button-dark" type="submit">Terapkan filter</button>
                @if ($hasFilters)
                    <a class="text-link" href="{{ route('catalog') }}">Hapus filter</a>
                @endif
            </div>
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
            <p class="pagination-summary">Menampilkan {{ $cows->firstItem() }}–{{ $cows->lastItem() }} dari {{ $cows->total() }} sapi tersedia</p>
        @else
            <div class="empty-state">
                <span class="empty-icon">✳</span>
                <h3>{{ $hasFilters ? 'Sapi tidak ditemukan' : 'Belum ada sapi di katalog' }}</h3>
                <p>{{ $hasFilters ? 'Coba ubah kata kunci atau rentang filter yang dipilih.' : 'Silakan kembali lagi nanti untuk melihat sapi yang tersedia.' }}</p>
                @if ($hasFilters)
                    <a class="button button-outline" href="{{ route('catalog') }}">Lihat semua sapi</a>
                @endif
            </div>
        @endif
    </section>
@endsection
