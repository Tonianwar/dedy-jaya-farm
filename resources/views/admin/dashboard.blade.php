@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="admin-heading">
        <div><p class="eyebrow eyebrow-green">PANEL PETERNAKAN</p><h1>Dashboard</h1><p class="muted">Ringkasan pengelolaan ternak Dedy Jaya Farm.</p></div>
        <div class="admin-heading-actions">
            <a class="button button-outline" href="{{ route('admin.tags.index') }}">Kelola QR</a>
            <a class="button button-dark" href="{{ route('admin.cows.create') }}">+ Tambah sapi baru</a>
        </div>
    </section>

    <div class="stats-grid">
        <article class="stat-card stat-card-dark"><span>Total individu tercatat</span><strong>{{ number_format($totalCows) }}</strong><small>Setiap ekor punya profil dan QR unik</small></article>
        <article class="stat-card"><span>Tersedia di katalog</span><strong>{{ number_format($availableCows) }}</strong><small>Dapat dilihat calon pembeli</small></article>
        <article class="stat-card"><span>Sudah terjual</span><strong>{{ number_format($soldCows) }}</strong><small>Histori dan QR tetap tersimpan</small></article>
        <article class="stat-card"><span>Catatan pemeliharaan</span><strong>{{ number_format($weightEntries + $healthEntries) }}</strong><small>{{ $weightEntries }} timbang · {{ $healthEntries }} kesehatan</small></article>
    </div>

    <section class="admin-panel">
        <div class="section-heading">
            <div><p class="eyebrow eyebrow-green">DATA TERBARU</p><h2>Sapi terakhir ditambahkan</h2></div>
            <a class="text-link" href="{{ route('admin.cows.index') }}">Semua data sapi →</a>
        </div>
        @if ($recentCows->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Identitas</th><th>Ras</th><th>Harga</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentCows as $cow)
                            <tr>
                                <td><strong>{{ $cow->display_name }}</strong><small>Tag {{ $cow->tag_number }}</small></td>
                                <td>{{ $cow->breed }}</td>
                                <td>{{ $cow->price !== null ? 'Rp '.number_format($cow->price, 0, ',', '.') : '—' }}</td>
                                <td><span class="status-pill {{ $cow->status === 'sold' ? 'status-sold' : 'status-available' }}">{{ $cow->status === 'sold' ? 'Terjual' : 'Tersedia' }}</span></td>
                                <td><a class="text-link" href="{{ route('admin.cows.show', $cow) }}">Kelola ↗</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state empty-state-small"><h3>Belum ada data sapi</h3><p>Tambahkan individu pertama untuk mulai mencatat riwayatnya.</p><a class="button button-outline" href="{{ route('admin.cows.create') }}">Tambah sapi</a></div>
        @endif
    </section>

    <div class="admin-tip"><span>TIP</span><p>Domain situs dan nomor WhatsApp dapat diatur kapan saja di <a href="{{ route('admin.settings.edit') }}">Pengaturan peternakan</a>.</p></div>
    <div class="admin-tip"><span>QR</span><p>Buat atau unduh QR dari menu <a href="{{ route('admin.tags.index') }}">QR ternak</a>. Setelah sapi terjual, slug arsip disimpan dan QR fisik dilepas untuk dipasangkan ke sapi berikutnya.</p></div>
@endsection
