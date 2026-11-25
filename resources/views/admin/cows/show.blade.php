@extends('layouts.app')

@section('title', 'Kelola '.$cow->display_name)

@section('content')
    <div class="breadcrumbs"><a href="{{ route('admin.cows.index') }}">Data sapi</a><span>/</span><span>{{ $cow->tag_number }}</span></div>
    <section class="admin-cow-header">
        <div class="admin-cow-photo">
            @if ($cow->photo_url)<img src="{{ $cow->photo_url }}" alt="Foto {{ $cow->display_name }}">@else<span class="image-placeholder" aria-hidden="true">✳</span>@endif
        </div>
        <div class="admin-cow-summary">
            <p class="eyebrow eyebrow-green">TAG {{ $cow->tag_number }}</p>
            <h1>{{ $cow->display_name }}</h1>
            <p class="muted">{{ $cow->breed }} · {{ ucfirst($cow->gender) }} · Masuk {{ $cow->arrived_at->format('d/m/Y') }}</p>
            <div class="admin-cow-actions">
                <a class="button button-outline" href="{{ route('admin.cows.edit', $cow) }}">Edit data</a>
                <a class="button button-outline" href="{{ \App\Support\CowPublicUrl::for($cow) }}" target="_blank" rel="noopener noreferrer">Lihat halaman publik ↗</a>
                @if ($cow->status === 'available')
                    <form method="POST" action="{{ route('admin.cows.sold', $cow) }}" onsubmit="return confirm('Tandai sapi ini sudah terjual? Tag QR akan dilepas agar dapat digunakan kembali, sedangkan histori sapi tetap tersimpan.')">
                        @csrf @method('PATCH')
                        <button class="button button-danger" type="submit">Tandai terjual</button>
                    </form>
                @else
                    <span class="status-pill status-sold">Terjual {{ $cow->sold_at?->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </section>

    <section class="qr-panel">
        <div>
            <p class="eyebrow eyebrow-green">IDENTITAS FISIK YANG DAPAT DIGUNAKAN ULANG</p>
            <h2>QR untuk name tag</h2>
            @if ($cow->currentTag)
                <p>QR ini milik tag <strong>{{ $cow->currentTag->code }}</strong> dan mengarah ke profil sapi ini. Saat sapi ditandai terjual, tag otomatis dilepas. Pasangkan ke sapi berikutnya tanpa mencetak ulang.</p>
                <code>{{ \App\Support\CowPublicUrl::forTag($cow->currentTag) }}</code>
            @elseif ($cow->status === 'sold')
                <p>Tag fisik sapi ini sudah dilepas dan siap dipasangkan ke sapi lain. URL profil sapi ini tetap berstatus terjual; pemindaian tag fisik akan menampilkan sapi baru setelah tag dipasangkan ulang.</p>
                @if ($cow->tagAssignments->isNotEmpty() && $cow->tagAssignments->first()->tag)
                    <code>Tag terakhir: {{ $cow->tagAssignments->first()->tag->code }}</code>
                @endif
            @else
                <p>Sapi ini belum memiliki tag QR. Pasangkan tag fisik yang tersedia untuk mengaktifkan pemindaian.</p>
            @endif
        </div>
        @if ($cow->currentTag)
            <div class="qr-actions">
                <a class="button button-dark" href="{{ route('admin.tags.qr.svg', $cow->currentTag->code) }}">Unduh SVG</a>
                <a class="button button-outline" href="{{ route('admin.tags.qr.png', $cow->currentTag->code) }}">Unduh PNG</a>
            </div>
        @endif
    </section>

    @if ($cow->status === 'available' && $reusableTags->isNotEmpty())
        <section class="tag-assignment-panel">
            <div><p class="eyebrow eyebrow-green">PENGGANTIAN TAG</p><h2>Pasangkan QR fisik yang sudah ada</h2><p>Pilih tag tanpa mencetak ulang. Jika dipasangkan ke sapi ini, QR lama otomatis dialihkan ke profil barunya.</p></div>
            <form method="POST" action="{{ route('admin.cows.tag.assign', $cow) }}" class="tag-assignment-form">
                @csrf
                <label for="tag_id">QR siap digunakan kembali</label>
                <div>
                    <select id="tag_id" name="tag_id" required>
                        <option value="">Pilih tag fisik</option>
                        @foreach ($reusableTags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->code }}{{ $tag->cow_id ? ' · dari sapi terjual' : ' · tersedia' }}</option>
                        @endforeach
                    </select>
                    <button class="button button-dark" type="submit">Pasangkan QR</button>
                </div>
            </form>
        </section>
    @endif

    <div class="admin-history-grid">
        <section class="admin-panel">
            <div class="section-heading"><div><p class="eyebrow eyebrow-green">PERTUMBUHAN</p><h2>Catat penimbangan</h2></div></div>
            <form method="POST" action="{{ route('admin.cows.weights.store', $cow) }}" enctype="multipart/form-data" class="stack-form compact-form">
                @csrf
                <div class="form-grid">
                    <label>Tanggal <span class="required">*</span><input type="date" name="weighed_at" value="{{ old('weighed_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required></label>
                    <label>Bobot (kg) <span class="required">*</span><input type="number" name="weight_kg" min="1" max="2000" step="0.1" required></label>
                    <label class="form-span">Catatan<input name="note" maxlength="2000" placeholder="Opsional"></label>
                    <label class="form-span">Foto penimbangan<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><small>Video tidak diunggah; video perawatan memakai tautan YouTube.</small></label>
                </div>
                <button class="button button-dark" type="submit">Simpan bobot</button>
            </form>
            <div class="admin-record-list">
                @forelse ($cow->weightLogs as $log)
                    <article class="admin-record">
                        <div><strong>{{ number_format((float) $log->weight_kg, 1, ',', '.') }} kg</strong><span>{{ $log->weighed_at->format('d/m/Y') }}{{ $log->note ? ' · '.$log->note : '' }}</span></div>
                        <form method="POST" action="{{ route('admin.cows.weights.destroy', [$cow, $log]) }}" onsubmit="return confirm('Hapus catatan penimbangan ini?')">@csrf @method('DELETE')<button class="delete-link" type="submit">Hapus</button></form>
                    </article>
                @empty
                    <p class="empty-inline">Belum ada penimbangan.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-panel">
            <div class="section-heading"><div><p class="eyebrow eyebrow-green">KESEHATAN & PERAWATAN</p><h2>Tambah catatan</h2></div></div>
            <form method="POST" action="{{ route('admin.cows.health-records.store', $cow) }}" class="stack-form compact-form">
                @csrf
                <div class="form-grid">
                    <label>Tanggal <span class="required">*</span><input type="date" name="recorded_at" value="{{ old('recorded_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required></label>
                    <label>Kategori <span class="required">*</span>
                        <select name="category" required>
                            <option value="care">Perawatan</option>
                            <option value="illness">Kesehatan / penyakit</option>
                            <option value="vaccination">Vaksinasi</option>
                            <option value="medication">Pengobatan</option>
                        </select>
                    </label>
                    <label class="form-span">Judul catatan <span class="required">*</span><input name="title" maxlength="150" required placeholder="Contoh: Vaksinasi PMK"></label>
                    <label class="form-span">Keterangan<textarea name="description" rows="3" maxlength="3000" placeholder="Catatan tindakan, obat, atau kondisi sapi"></textarea></label>
                    <label class="form-span">Tautan video YouTube<input type="url" name="youtube_url" maxlength="255" placeholder="https://youtu.be/..."><small>Video ditampilkan dengan embed YouTube; unggah video langsung tidak tersedia.</small></label>
                </div>
                <button class="button button-dark" type="submit">Simpan riwayat</button>
            </form>
            <div class="admin-record-list">
                @forelse ($cow->healthRecords as $record)
                    <article class="admin-record">
                        <div><strong>{{ $record->title }}</strong><span>{{ $record->recorded_at->format('d/m/Y') }} · {{ ['illness' => 'Kesehatan', 'vaccination' => 'Vaksinasi', 'medication' => 'Pengobatan', 'care' => 'Perawatan'][$record->category] ?? 'Perawatan' }}</span></div>
                        <form method="POST" action="{{ route('admin.cows.health-records.destroy', [$cow, $record]) }}" onsubmit="return confirm('Hapus riwayat ini?')">@csrf @method('DELETE')<button class="delete-link" type="submit">Hapus</button></form>
                    </article>
                @empty
                    <p class="empty-inline">Belum ada riwayat perawatan.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
