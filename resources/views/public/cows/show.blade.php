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
            @php
                $weightChartLogs = $cow->weightLogs->sortBy([['weighed_at', 'asc'], ['id', 'asc']])->values();
                $chartWidth = 760;
                $chartHeight = 280;
                $chartLeft = 76;
                $chartRight = 744;
                $chartTop = 18;
                $chartBottom = 218;
                $chartWeights = $weightChartLogs->map(fn ($log) => (float) $log->weight_kg);
                $weightMin = $chartWeights->min();
                $weightMax = $chartWeights->max();
                $weightRange = max($weightMax - $weightMin, 10);
                $chartMin = $weightMin - $weightRange * 0.15;
                $chartMax = $weightMax + $weightRange * 0.15;
                $chartPoints = $weightChartLogs->map(function ($log, $index) use ($weightChartLogs, $chartLeft, $chartRight, $chartTop, $chartBottom, $chartMin, $chartMax) {
                    $weight = (float) $log->weight_kg;
                    $pointX = $weightChartLogs->count() > 1
                        ? $chartLeft + $index * (($chartRight - $chartLeft) / ($weightChartLogs->count() - 1))
                        : ($chartLeft + $chartRight) / 2;
                    $pointY = $chartBottom - (($weight - $chartMin) / ($chartMax - $chartMin)) * ($chartBottom - $chartTop);

                    return [
                        'x' => round($pointX, 2),
                        'y' => round($pointY, 2),
                        'weight' => $weight,
                        'date' => $log->weighed_at->format('d/m/Y'),
                    ];
                });
                $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                $chartArea = $chartPoints->count() > 1
                    ? 'M '.$chartPoints->first()['x'].' '.$chartBottom.' L '.$chartPoints->map(fn ($point) => $point['x'].' '.$point['y'])->implode(' L ').' L '.$chartPoints->last()['x'].' '.$chartBottom.' Z'
                    : null;
                $chartTicks = collect(range(0, 4))->map(fn ($index) => [
                    'y' => $chartTop + $index * (($chartBottom - $chartTop) / 4),
                    'weight' => $chartMax - $index * (($chartMax - $chartMin) / 4),
                ]);
                $chartLabelIndexes = $chartPoints->count() <= 5
                    ? range(0, $chartPoints->count() - 1)
                    : collect([0, .25, .5, .75, 1])->map(fn ($position) => (int) round(($chartPoints->count() - 1) * $position))->unique()->values()->all();
                $weightChange = $chartPoints->last()['weight'] - $chartPoints->first()['weight'];
            @endphp
            <div class="weight-chart-card">
                <div class="weight-chart-heading">
                    <div>
                        <span class="field-label">PERKEMBANGAN BOBOT</span>
                        <p>Grafik berdasarkan {{ $weightChartLogs->count() }} kali penimbangan</p>
                    </div>
                    @if ($weightChartLogs->count() > 1)
                        <strong class="weight-chart-change {{ $weightChange >= 0 ? 'weight-change-up' : 'weight-change-down' }}">
                            {{ $weightChange >= 0 ? '+' : '' }}{{ number_format($weightChange, 1, ',', '.') }} kg
                        </strong>
                    @endif
                </div>
                <div class="weight-chart-scroll">
                    <svg class="weight-chart-svg" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-labelledby="weight-chart-title weight-chart-description">
                        <title id="weight-chart-title">Grafik riwayat bobot {{ $cow->display_name }}</title>
                        <desc id="weight-chart-description">
                            Perubahan bobot dari {{ $chartPoints->first()['date'] }} sebesar {{ number_format($chartPoints->first()['weight'], 1, ',', '.') }} kilogram
                            hingga {{ $chartPoints->last()['date'] }} sebesar {{ number_format($chartPoints->last()['weight'], 1, ',', '.') }} kilogram.
                        </desc>
                        @foreach ($chartTicks as $tick)
                            <line class="weight-chart-gridline" x1="{{ $chartLeft }}" y1="{{ $tick['y'] }}" x2="{{ $chartRight }}" y2="{{ $tick['y'] }}"></line>
                            <text class="weight-chart-axis-label" x="{{ $chartLeft - 12 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">{{ number_format($tick['weight'], 1, ',', '.') }}</text>
                        @endforeach
                        @if ($chartArea)
                            <path class="weight-chart-area" d="{{ $chartArea }}"></path>
                            <polyline class="weight-chart-line" points="{{ $chartLine }}"></polyline>
                        @endif
                        @foreach ($chartPoints as $point)
                            <circle class="weight-chart-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5">
                                <title>{{ $point['date'] }}: {{ number_format($point['weight'], 1, ',', '.') }} kg</title>
                            </circle>
                        @endforeach
                        @foreach ($chartLabelIndexes as $index)
                            <text class="weight-chart-date" x="{{ $chartPoints[$index]['x'] }}" y="{{ $chartBottom + 28 }}" text-anchor="middle">{{ $chartPoints[$index]['date'] }}</text>
                        @endforeach
                    </svg>
                </div>
                @if ($weightChartLogs->count() === 1)
                    <p class="weight-chart-note">Tambahkan penimbangan berikutnya untuk melihat perubahan bobot dari waktu ke waktu.</p>
                @endif
            </div>
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
