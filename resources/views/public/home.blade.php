@extends('layouts.app')

@section('title', 'Peternakan Sapi Banyumas')

@section('content')
    @if ($showHomeHero)
        <section class="home-hero">
            <div class="hero-landscape" data-parallax style="background-image: url('{{ asset('images/cattle/pasture-01.svg') }}')"></div>
            <div class="hero-grain" aria-hidden="true"></div>
            <div class="hero-glass" data-reveal>
                <p class="eyebrow">{{ $heroEyebrow }}</p>
                <h1>{{ $heroTitle }}</h1>
                <p class="hero-description">{{ $heroDescription }}</p>
                <div class="hero-actions">
                    <a class="button button-light" href="{{ route('catalog') }}">Jelajahi sapi <span aria-hidden="true">↗</span></a>
                    <a class="hero-secondary-link" href="{{ route('catalog') }}">Lihat katalog <span aria-hidden="true">↓</span></a>
                </div>
            </div>
            <div class="hero-caption"><span>01 — 03</span><span>TERNAK TERPILIH · BANYUMAS</span></div>
            <div class="hero-orbit" aria-hidden="true"><span>FARM · FAMILY · FUTURE</span><i>✳</i><span>TRACEABILITY SINCE 2026</span></div>
        </section>
    @endif

    @if ($showHomeTraceability)
        <section class="traceability-strip" data-reveal>
            <div class="traceability-count"><strong>{{ str_pad((string) $availableCount, 2, '0', STR_PAD_LEFT) }}</strong><span>ekor tersedia</span></div>
            <div class="traceability-divider"></div>
            <div class="traceability-copy"><span>SETIAP TERNAK PUNYA CERITA</span><p>Satu identitas. Catatan yang jelas. Dirawat dengan perhatian.</p></div>
            <a href="{{ route('catalog') }}">Lihat katalog <span aria-hidden="true">↗</span></a>
        </section>
    @endif

    @if ($showHomeFeatured)
        <section class="featured-section" id="pilihan">
        <div class="section-heading" data-reveal>
            <div><p class="eyebrow eyebrow-green">KATALOG PILIHAN</p><h2>Kenali sebelum memilih.</h2><p class="section-description">Lihat profil, bobot, dan perjalanan setiap individu ternak.</p></div>
            <a class="button button-outline" href="{{ route('catalog') }}">Semua sapi <span aria-hidden="true">↗</span></a>
        </div>
        @if ($featuredCows->isNotEmpty())
            <div class="cow-grid">
                @foreach ($featuredCows as $cow)
                    <article class="cow-card" data-reveal>
                        <a class="cow-card-image" href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}">
                            @if ($cow->photo_url)<img src="{{ $cow->photo_url }}" alt="Foto {{ $cow->display_name }}" loading="lazy">@else<span class="image-placeholder" aria-hidden="true">✳</span>@endif
                            <span class="status-pill status-available">Tersedia</span>
                            <span class="card-image-tag">{{ $cow->tag_number }}</span>
                        </a>
                        <div class="cow-card-body">
                            <div class="cow-card-meta"><span>{{ $cow->breed }}</span><span>{{ ucfirst($cow->gender) }}</span></div>
                            <h3><a href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}">{{ $cow->display_name }}</a></h3>
                            <div class="cow-card-stats"><span>Bobot terkini <strong>{{ $cow->current_weight ? number_format((float) $cow->current_weight, 0, ',', '.') : '—' }} kg</strong></span></div>
                            <div class="cow-card-footer"><strong class="price">{{ $cow->price !== null ? 'Rp '.number_format($cow->price, 0, ',', '.') : 'Hubungi kami' }}</strong><a class="arrow-link" href="{{ route('cows.show', $cow->currentTag?->slug ?? $cow->slug) }}" aria-label="Lihat detail {{ $cow->display_name }}">↗</a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h3>Katalog sedang diperbarui</h3><p>Hubungi kami untuk informasi ketersediaan sapi.</p></div>
        @endif
        </section>
    @endif

    @if ($showHomeCareStory)
        <section class="care-story" data-reveal>
            <div class="care-photo">
                <img src="{{ asset('images/cattle/pasture-03.svg') }}" alt="Ilustrasi suasana peternakan sapi di Banyumas" loading="lazy">
                <span class="care-photo-note">CATATAN PEMELIHARAAN · DEDY JAYA FARM</span>
            </div>
            <div class="care-story-copy">
                <p class="eyebrow eyebrow-green">DIRAWAT DENGAN KONSISTEN</p>
                <h2>Perjalanan setiap sapi, tercatat.</h2>
                <p>Identitas digital membantu kami menghubungkan setiap ternak dengan catatan pertumbuhan, penimbangan, dan perawatan kesehatannya.</p>
                <div class="care-points">
                    <div><span>01</span><strong>Riwayat bobot</strong><small>Perubahan berat badan dari waktu ke waktu.</small></div>
                    <div><span>02</span><strong>Catatan kesehatan</strong><small>Vaksinasi, perawatan, dan pengobatan.</small></div>
                    <div><span>03</span><strong>Informasi transparan</strong><small>Profil ternak terbuka lewat pemindaian QR.</small></div>
                </div>
            </div>
        </section>
    @endif

    @if ($showHomeContact)
        <section class="contact-panel" id="kontak" data-reveal>
            <div class="contact-heading">
                <p class="eyebrow">MARI BERKUNJUNG</p>
                <h2>Terbuka untuk<br><em>berbincang.</em></h2>
                <p>Kunjungi peternakan atau hubungi kami untuk informasi sapi yang tersedia.</p>
                @if ($whatsapp)
                    <a class="button button-light" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">Hubungi via WhatsApp <span aria-hidden="true">↗</span></a>
                @endif
            </div>
            <div class="contact-details">
                <div><span>ALAMAT</span><p>{{ $address }}</p><a class="contact-map-link" href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer">Buka di Google Maps <span aria-hidden="true">↗</span></a></div>
                @if ($contactPhone)<div><span>TELEPON</span><p><a href="tel:{{ $contactPhone }}">{{ $contactPhone }}</a></p></div>@endif
                @if ($contactEmail)<div><span>EMAIL</span><p><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p></div>@endif
                @if ($openingHours)<div><span>JAM KUNJUNGAN</span><p>{{ $openingHours }}</p></div>@endif
                @if ($instagramUrl)<div><span>INSTAGRAM</span><p><a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">@dedyjayafarm ↗</a></p></div>@endif
            </div>
            <div class="contact-ornament" aria-hidden="true">✳</div>
        </section>
    @endif
@endsection
