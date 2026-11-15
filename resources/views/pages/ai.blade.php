@extends('layouts.app')
@section('title', 'Tutor TOEFL')
@section('content')
    <h1 class="page-title">Tutor TOEFL</h1>
    <p class="page-sub">Tanya apa saja seputar materi Structure, Listening, Reading, atau Writing. Dapatkan pembahasan mendalam dan strategi pengerjaan soal.</p>

    <div class="card">
        @if ($aiReady)
            <p>Tutor aktif & siap membantu belajarmu.</p>
            @guest
                <p class="small muted" style="margin-top:6px"><a href="{{ route('login') }}"><b>Masuk</b></a> dulu untuk memakai tutor.</p>
            @endguest
        @else
            <p><b>Belum aktif.</b> Isi <code>GEMINI_API_KEY</code> di file <code>.env</code> (ambil di aistudio.google.com/apikey), lalu jalankan <code>php artisan config:clear</code>.</p>
        @endif
    </div>

    <button type="button" class="btn btn-primary btn-block" style="margin-top:14px" x-data @click="$store.tutor.show()">💬 Buka Tanya Tutor</button>
@endsection
