@extends('layouts.app')

@section('title', $cow->display_name.' · Detail sapi')

@section('content')
    <div class="breadcrumbs"><a href="{{ route('catalog') }}">Katalog sapi</a><span>/</span><span>{{ $cow->tag_number }}</span></div>

    <section class="animal-hero">
        <div class="animal-photo">
            @if ($cow->photo_url)
                <img src="{{ $cow->photo_url }}" alt="Foto {{ $cow->display_name }}" loading="lazy">
            @else
                <span class="image-placeholder" aria-hidden="true">✳</span>
            @endif
            <span class="status-pill {{ $cow->status === 'sold' ? 'status-sold' : 'status-available' }}">
                {{ $cow->status === 'sold' ? 'Sudah terjual' : 'Tersedia' }}
            </span>
        </div>
        <div class="animal-intro">
            <p class="eyebrow eyebrow-green">PROFIL TERNAK · TAG {{ $cow->tag_number }}</p>
            <h1>{{ $cow->display_name }}</h1>
            <p class="animal-breed">{{ $cow->breed }} <span>·</span> {{ ucfirst($cow->gender) }}</p>
            <p class="animal-description">{{ $cow->description ?: 'Data ternak dan riwayat pemeliharaan tercatat secara digital untuk membantu Anda mengenal sapi ini.' }}</p>

            <div class="animal-price-card">
                <div>
                    <span class="field-label">HARGA</span>
                    <strong class="price price-large">{{ $cow->price !== null ? 'Rp '.number_format($cow->price, 0, ',', '.') : 'Hubungi kami' }}</strong>
                </div>
                @if ($cow->status === 'available' && $whatsappUrl)
                    <a class="button button-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Tanya via WhatsApp <span aria-hidden="true">↗</span></a>
                @elseif ($cow->status === 'sold')
                    <span class="sold-note">Sapi ini sudah terjual</span>
                @endif
            </div>
        </div>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div><p class="eyebrow eyebrow-green">DATA INDIVIDU</p><h2>Kenali sapinya</h2></div>
            <span class="section-note">Informasi khusus sapi dengan tag {{ $cow->tag_number }}</span>
        </div>
        <div class="detail-grid">
            <article class="detail-card"><span class="field-label">NOMOR TAG</span><strong>{{ $cow->tag_number }}</strong></article>
            <article class="detail-card"><span class="field-label">RAS / JENIS</span><strong>{{ $cow->breed }}</strong></article>
            <article class="detail-card"><span class="field-label">JENIS KELAMIN</span><strong>{{ ucfirst($cow->gender) }}</strong></article>
            <article class="detail-card"><span class="field-label">UMUR</span><strong>{{ $cow->age_label ?? 'Belum dicatat' }}</strong></article>
            <article class="detail-card"><span class="field-label">MASUK PETERNAKAN</span><strong>{{ $cow->arrived_at->format('d/m/Y') }}</strong></article>
            <article class="detail-card detail-card-highlight"><span class="field-label">BOBOT TERKINI</span><strong>{{ $cow->current_weight ? number_format((float) $cow->current_weight, 1, ',', '.') : '—' }} kg</strong></article>
        </div>
    </section>

    <section class="history-section">
        <div class="section-heading">
            <div><p class="eyebrow eyebrow-green">CATATAN PEMELIHARAAN</p><h2>Riwayat bobot</h2></div>
            <span class="result-count">{{ $cow->weightLogs->count() }} kali timbang</span>
        </div>
        @if ($cow->weightLogs->isNotEmpty())
            <div class="history-list">
                @foreach ($cow->weightLogs as $log)
                    <article class="history-card">
                        <span class="history-marker" aria-hidden="true">↗</span>
                        <div class="history-main">
                            <div class="history-card-heading"><strong>{{ number_format((float) $log->weight_kg, 1, ',', '.') }} kg</strong><time>{{ $log->weighed_at->format('d/m/Y') }}</time></div>
                            @if ($log->note)<p>{{ $log->note }}</p>@endif
                            @if ($log->photo_path)
                                <a class="photo-link" href="{{ asset('storage/'.$log->photo_path) }}" target="_blank" rel="noopener noreferrer">Lihat dokumentasi penimbangan ↗</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="empty-inline">Riwayat penimbangan belum tersedia.</p>
        @endif
    </section>

    <section class="history-section care-section">
        <div class="section-heading">
            <div><p class="eyebrow eyebrow-green">KESEHATAN & PERAWATAN</p><h2>Catatan perawatan</h2></div>
            <span class="result-count">{{ $cow->healthRecords->count() }} catatan</span>
        </div>
        @if ($cow->healthRecords->isNotEmpty())
            <div class="care-grid">
                @foreach ($cow->healthRecords as $record)
                    <article class="care-card">
                        <div class="care-card-top">
                            <span class="category-pill">{{ ['illness' => 'Kesehatan', 'vaccination' => 'Vaksinasi', 'medication' => 'Pengobatan', 'care' => 'Perawatan'][$record->category] ?? 'Perawatan' }}</span>
                            <time>{{ $record->recorded_at->format('d/m/Y') }}</time>
                        </div>
                        <h3>{{ $record->title }}</h3>
                        @if ($record->description)<p>{{ $record->description }}</p>@endif
                        @if ($record->youtube_embed_url)
                            <div class="video-frame">
                                <iframe src="{{ $record->youtube_embed_url }}" title="Video {{ $record->title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="empty-inline">Belum ada catatan kesehatan atau perawatan.</p>
        @endif
    </section>

    <div class="profile-footnote"><span>✳</span> {{ $farmName }} · Riwayat sapi ini mengikuti identitas individunya.</div>
@endsection
