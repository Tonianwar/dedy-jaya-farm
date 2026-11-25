@extends('layouts.app')

@section('title', 'Tag sedang disiapkan')

@section('content')
    <section class="tag-unassigned">
        <div class="tag-unassigned-mark" aria-hidden="true">✳</div>
        <p class="eyebrow eyebrow-green">QR {{ $tag->code }}</p>
        <h1>Tag sedang disiapkan.</h1>
        <p>URL pendek /sapi/{{ $tag->slug }} belum dipasangkan ke sapi. Setelah admin mengaitkan QR ini ke sapi berikutnya, URL yang sama akan langsung membuka profil sapi tersebut.</p>
        <a class="button button-dark" href="{{ route('home') }}">Kembali ke beranda</a>
    </section>
@endsection
