@extends('layouts.app')

@section('title', 'QR ternak')

@section('content')
    <section class="admin-heading">
        <div>
            <p class="eyebrow eyebrow-green">QR FISIK YANG DAPAT DIGUNAKAN ULANG</p>
            <h1>QR ternak</h1>
            <p class="muted">Buat QR pendek secara terpisah, lalu pasangkan dengan profil sapi saat siap.</p>
        </div>
        <a class="button button-outline" href="{{ route('admin.cows.index') }}">Data sapi</a>
    </section>

    <section class="admin-panel tag-create-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow eyebrow-green">BUAT IDENTITAS QR</p>
                <h2>Generate QR baru</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.tags.store') }}" class="tag-create-form">
            @csrf
            <label>
                <span class="label-caption">Kode QR <span class="required">*</span></span>
                <input name="code" value="{{ old('code') }}" maxlength="40" required placeholder="Contoh: DJF-1001">
                <small>Kode fisik unik untuk dicetak atau ditempel pada tag.</small>
            </label>
            <label>
                Slug URL (opsional)
                <input name="slug" value="{{ old('slug') }}" maxlength="80" pattern="[A-Za-z0-9_-]+" placeholder="Otomatis dari kode, misalnya djf-1001">
                <small>URL QR menjadi {{ rtrim(\App\Models\SiteSetting::get('public_url', config('app.url')), '/') }}/sapi/slug.</small>
            </label>
            <button class="button button-dark" type="submit">Buat QR</button>
        </form>
    </section>

    <section class="admin-panel tag-list-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow eyebrow-green">INVENTARIS TAG</p>
                <h2>Daftar QR</h2>
            </div>
            <span class="result-count">{{ $tags->total() }} QR</span>
        </div>
        @if ($tags->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Kode / URL pendek</th><th>Sapi terpasang</th><th>Riwayat pemakaian</th><th>QR</th></tr></thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr>
                                <td>
                                    <strong>{{ $tag->code }}</strong>
                                    <small><a href="{{ route('cows.show', $tag->slug) }}" target="_blank" rel="noopener noreferrer">/sapi/{{ $tag->slug }} ↗</a></small>
                                </td>
                                <td>
                                    @if ($tag->cow)
                                        <a href="{{ route('admin.cows.show', $tag->cow) }}">{{ $tag->cow->display_name }}</a>
                                        <small>{{ $tag->cow->tag_number }} · {{ $tag->cow->status === 'sold' ? 'Terjual' : 'Aktif' }}</small>
                                    @else
                                        <span class="status-pill status-sold">Belum dipasangkan</span>
                                    @endif
                                </td>
                                <td>{{ $tag->assignments_count }} kali</td>
                                <td>
                                    <div class="tag-download-actions">
                                        <a class="text-link" href="{{ route('admin.tags.qr.svg', $tag->code) }}">SVG</a>
                                        <a class="text-link" href="{{ route('admin.tags.qr.png', $tag->code) }}">PNG</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $tags->links() }}</div>
        @else
            <div class="empty-state empty-state-small">
                <h3>Belum ada QR fisik</h3>
                <p>Buat QR di atas. Setelah itu, pasangkan ke sapi dari halaman kelola sapi.</p>
            </div>
        @endif
    </section>
@endsection
