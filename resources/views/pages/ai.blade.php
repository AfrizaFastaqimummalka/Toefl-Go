@extends('layouts.app')
@section('title', 'Tutor AI')
@section('content')
    <h1 class="page-title">Tutor AI</h1>
    <p class="page-sub">Tanya apa saja soal Structure. Di dalam soal, tombol AI menjelaskan kenapa salah dan memberi hint tanpa membocorkan jawaban.</p>

    <div class="card">
        @if ($aiReady)
            <p>Tutor aktif. Model: <b>{{ config('services.gemini.model') }}</b>.</p>
            @guest
                <p class="small muted" style="margin-top:6px"><a href="{{ route('login') }}"><b>Masuk</b></a> dulu untuk memakai tutor.</p>
            @endguest
        @else
            <p><b>Belum aktif.</b> Isi <code>GEMINI_API_KEY</code> di file <code>.env</code> (ambil di aistudio.google.com/apikey), lalu jalankan <code>php artisan config:clear</code>. Key hanya dibaca server dan tidak pernah sampai ke browser.</p>
        @endif
    </div>

    <button type="button" class="btn btn-primary btn-block" style="margin-top:14px" x-data @click="$store.tutor.show()">Buka tutor</button>
@endsection
