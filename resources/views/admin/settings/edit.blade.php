@extends('layouts.app')

@section('title', 'Pengaturan peternakan')

@section('content')
    <section class="form-page">
        <div class="form-heading"><p class="eyebrow eyebrow-green">KONFIGURASI SITUS</p><h1>Profil &amp; tampilan</h1><p class="muted">Ubah informasi yang terlihat di beranda, katalog, dan halaman kontak.</p></div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="form-card">
            @csrf @method('PUT')
            <section class="settings-group">
                <h2>Identitas peternakan</h2>
                <div class="stack-form">
                    <label>Nama peternakan <span class="required">*</span><input name="farm_name" value="{{ old('farm_name', $farmName) }}" maxlength="80" required></label>
                    <label>Alamat <span class="required">*</span><input name="address" value="{{ old('address', $address) }}" maxlength="255" required></label>
                    <label>Jam kunjungan<input name="opening_hours" value="{{ old('opening_hours', $openingHours) }}" maxlength="150" placeholder="Senin–Sabtu, 08.00–16.00 WIB"></label>
                </div>
            </section>
            <section class="settings-group">
                <h2>Konten beranda</h2>
                <div class="stack-form">
                    <label>Teks pembuka <span class="required">*</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $heroEyebrow) }}" maxlength="100" required></label>
                    <label>Judul utama <span class="required">*</span><textarea name="hero_title" rows="2" maxlength="150" required>{{ old('hero_title', $heroTitle) }}</textarea></label>
                    <label>Deskripsi utama <span class="required">*</span><textarea name="hero_description" rows="3" maxlength="500" required>{{ old('hero_description', $heroDescription) }}</textarea></label>
                    <label>Judul profil peternakan <span class="required">*</span><textarea name="about_title" rows="2" maxlength="150" required>{{ old('about_title', $aboutTitle) }}</textarea></label>
                    <label>Cerita peternakan <span class="required">*</span><textarea name="about_description" rows="4" maxlength="2000" required>{{ old('about_description', $aboutDescription) }}</textarea></label>
                </div>
            </section>
            <section class="settings-group">
                <h2>Kontak pelanggan</h2>
                <div class="stack-form">
                    <label>Nomor WhatsApp<input name="whatsapp" inputmode="numeric" value="{{ old('whatsapp', $whatsapp) }}" maxlength="15" placeholder="6281234567890"><small>Masukkan kode negara dan angka saja (contoh Indonesia: 62812…). Kosongkan jika belum ingin menerima pesan WhatsApp.</small></label>
                    <label>Nomor telepon<input type="tel" name="contact_phone" value="{{ old('contact_phone', $contactPhone) }}" maxlength="40"></label>
                    <label>Email kontak<input type="email" name="contact_email" value="{{ old('contact_email', $contactEmail) }}" maxlength="150"></label>
                </div>
            </section>
            <section class="settings-group">
                <h2>Domain &amp; QR tag</h2>
                <div class="stack-form">
                    <label>Domain publik <span class="required">*</span><input type="url" name="public_url" value="{{ old('public_url', $publicUrl) }}" maxlength="255" placeholder="https://dedijayafarm.id" required><small>QR fisik menggunakan URL tetap `/qr/...` dan dapat digunakan ulang saat sapi berganti. Domain wajib menjadi dasar URL QR. Jika domain situs berubah, pertahankan domain lama dengan pengalihan (redirect) menuju situs baru agar QR yang sudah dicetak tetap bisa dipindai; jika domain lama tidak bisa dipertahankan, QR perlu dicetak ulang.</small></label>
                </div>
            </section>
            <div class="form-actions"><a class="button button-outline" href="{{ route('admin.dashboard') }}">Kembali</a><button class="button button-dark" type="submit">Simpan pengaturan</button></div>
        </form>
    </section>
@endsection
