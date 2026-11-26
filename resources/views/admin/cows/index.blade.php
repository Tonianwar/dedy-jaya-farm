@extends('layouts.app')

@section('title', 'Data sapi')

@section('content')
    <section class="admin-heading">
        <div><p class="eyebrow eyebrow-green">INVENTARIS DIGITAL</p><h1>Data sapi</h1><p class="muted">Sapi terjual tetap tercatat; tambah sapi baru sebagai individu baru.</p></div>
        <a class="button button-dark" href="{{ route('admin.cows.create') }}">+ Tambah sapi baru</a>
    </section>

    <section class="admin-panel">
        <form class="search-form admin-search admin-cow-search" method="GET" action="{{ route('admin.cows.index') }}">
            <label class="sr-only" for="admin-search">Cari sapi</label>
            <input id="admin-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama, nomor tag, atau ras">
            <label class="admin-page-size" for="admin-per-page">Baris per halaman
                <select id="admin-per-page" name="per_page">
                    @foreach ([15, 30, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button button-dark" type="submit">Terapkan</button>
            @if ($search || $perPage !== 15)<a class="text-link" href="{{ route('admin.cows.index') }}">Reset</a>@endif
        </form>

        <p class="pagination-summary">
            @if ($cows->total() > 0)
                Menampilkan {{ $cows->firstItem() }}–{{ $cows->lastItem() }} dari {{ $cows->total() }} data sapi
            @else
                Tidak ada data sapi
            @endif
        </p>

        @if ($cows->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Identitas</th><th>Ras / kelamin</th><th>Bobot awal</th><th>Harga</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($cows as $cow)
                            <tr>
                                <td><strong>{{ $cow->display_name }}</strong><small>Tag {{ $cow->tag_number }} · Masuk {{ $cow->arrived_at->format('d/m/Y') }}</small></td>
                                <td>{{ $cow->breed }}<small>{{ ucfirst($cow->gender) }}</small></td>
                                <td>{{ $cow->initial_weight_kg ? number_format((float) $cow->initial_weight_kg, 1, ',', '.').' kg' : '—' }}</td>
                                <td>{{ $cow->price !== null ? 'Rp '.number_format($cow->price, 0, ',', '.') : '—' }}</td>
                                <td><span class="status-pill {{ $cow->status === 'sold' ? 'status-sold' : 'status-available' }}">{{ $cow->status === 'sold' ? 'Terjual' : 'Tersedia' }}</span></td>
                                <td><a class="text-link" href="{{ route('admin.cows.show', $cow) }}">Kelola ↗</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $cows->links() }}</div>
        @else
            <div class="empty-state empty-state-small"><h3>{{ $search ? 'Sapi tidak ditemukan' : 'Belum ada data sapi' }}</h3><p>{{ $search ? 'Coba kata kunci yang berbeda.' : 'Mulai dengan menambahkan sapi secara manual.' }}</p></div>
        @endif
    </section>
@endsection
