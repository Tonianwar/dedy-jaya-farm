@extends('layouts.app')

@section('title', 'Masuk admin')

@section('content')
    <section class="login-wrap">
        <div class="login-card">
            <p class="eyebrow eyebrow-green">AREA PENGELOLA</p>
            <h1>Selamat datang<br>kembali.</h1>
            <p class="muted">Masuk untuk mengelola data sapi dan membuat QR code.</p>
            <form method="POST" action="{{ route('admin.login') }}" class="stack-form">
                @csrf
                <label>Email admin<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
                <label>Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
                <button class="button button-dark button-wide" type="submit">Masuk ke dashboard <span aria-hidden="true">→</span></button>
            </form>
            <a class="text-link login-back" href="{{ route('catalog') }}">← Kembali ke katalog</a>
        </div>
        <div class="login-aside"><span class="aside-mark">✳</span><p>Setiap ternak punya cerita.<br>Rawat, catat, dan lacak dengan baik.</p><span class="aside-caption">SMART LIVESTOCK TRACEABILITY</span></div>
    </section>
@endsection
