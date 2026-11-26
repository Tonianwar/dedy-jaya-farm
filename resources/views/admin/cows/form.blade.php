@extends('layouts.app')

@php($editing = $cow->exists)

@section('title', $editing ? 'Edit data sapi' : 'Tambah sapi')

@section('content')
    <div class="breadcrumbs"><a href="{{ route('admin.cows.index') }}">Data sapi</a><span>/</span><span>{{ $editing ? 'Edit' : 'Tambah baru' }}</span></div>
    <section class="form-page">
        <div class="form-heading"><p class="eyebrow eyebrow-green">IDENTITAS INDIVIDU</p><h1>{{ $editing ? 'Perbarui data sapi' : 'Catat sapi baru' }}</h1><p class="muted">Setiap sapi dibuat sebagai profil baru. QR dapat dipasangkan sekarang atau dari menu QR ternak.</p></div>
        <form method="POST" action="{{ $editing ? route('admin.cows.update', $cow) : route('admin.cows.store') }}" enctype="multipart/form-data" class="form-card">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="form-grid">
                <label><span class="label-caption">Nomor tag <span class="required">*</span></span><input name="tag_number" value="{{ old('tag_number', $cow->tag_number) }}" maxlength="80" required placeholder="Contoh: DJF-0001"><small>Harus unik, ini identitas fisik sapi.</small></label>
                <label>Nama panggilan<input name="name" value="{{ old('name', $cow->name) }}" maxlength="100" placeholder="Opsional"></label>
                <label><span class="label-caption">Ras / jenis sapi <span class="required">*</span></span><input name="breed" value="{{ old('breed', $cow->breed) }}" maxlength="100" required placeholder="Contoh: Simental"></label>
                <label><span class="label-caption">Jenis kelamin <span class="required">*</span></span>
                    <select name="gender" required>
                        <option value="">Pilih jenis kelamin</option>
                        <option value="jantan" @selected(old('gender', $cow->gender) === 'jantan')>Jantan</option>
                        <option value="betina" @selected(old('gender', $cow->gender) === 'betina')>Betina</option>
                    </select>
                </label>
                <label>Tanggal lahir<input type="date" name="birth_date" value="{{ old('birth_date', $cow->birth_date?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}"></label>
                <label><span class="label-caption">Tanggal masuk kandang <span class="required">*</span></span><input type="date" name="arrived_at" value="{{ old('arrived_at', $cow->arrived_at?->format('Y-m-d') ?? now()->toDateString()) }}" max="{{ now()->toDateString() }}" required></label>
                <label>Harga jual (Rp)<input type="number" name="price" value="{{ old('price', $cow->price) }}" min="0" step="1000" placeholder="Kosongkan jika belum ditentukan"></label>
                @unless ($editing)
                    <label>Bobot awal (kg)<input type="number" name="initial_weight_kg" value="{{ old('initial_weight_kg') }}" min="1" max="2000" step="0.1" placeholder="Opsional"></label>
                    <label class="form-span">QR fisik (opsional)<select name="livestock_tag_id"><option value="">Pasangkan nanti dari menu QR ternak</option>@foreach ($reusableTags as $tag)<option value="{{ $tag->id }}" @selected(old('livestock_tag_id', request('livestock_tag_id')) == $tag->id)>{{ $tag->code }} · {{ $tag->slug }}{{ $tag->cow_id ? ' · sebelumnya milik sapi terjual' : ' · belum digunakan' }}</option>@endforeach</select><small>QR dibuat dan dikelola terpisah dari data sapi, lalu dapat dipasangkan di sini atau dari halaman kelola sapi.</small></label>
                @endunless
                <label class="form-span">Foto sapi<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP · maksimal 8 MB</small></label>
                <label class="form-span">Catatan profil<textarea name="description" rows="4" maxlength="5000" placeholder="Informasi tambahan tentang sapi">{{ old('description', $cow->description) }}</textarea></label>
            </div>
            <div class="form-actions"><a class="button button-outline" href="{{ $editing ? route('admin.cows.show', $cow) : route('admin.cows.index') }}">Batal</a><button class="button button-dark" type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan & buat profil' }}</button></div>
        </form>
    </section>
@endsection
