@extends('layouts.app')
@section('title', 'Tes')
@section('content')
    <h1 class="page-title">Tes</h1>
    <p class="page-sub">Simulasi dengan timer yang bisa dimatikan. Hadir bertahap.</p>
    <div class="stack">
        <a class="card card-link" href="{{ route('test.structure') }}">
            <div class="chapter-row">
                <div style="flex:1"><h3>Structure</h3><p class="small muted">25 menit · 15 completion + 25 error identification</p></div>
                <span class="badge">Mulai</span>
            </div>
        </a>
        <a class="card card-link" href="{{ route('test.listening') }}">
            <div class="chapter-row">
                <div style="flex:1"><h3>Listening</h3><p class="small muted">35 menit · Part A, B, C (55 soal)</p></div>
                <span class="badge">Mulai</span>
            </div>
        </a>
        <a class="card card-link" href="{{ route('test.reading') }}">
            <div class="chapter-row">
                <div style="flex:1"><h3>Reading</h3><p class="small muted">55 menit · 5 passage (38 soal)</p></div>
                <span class="badge">Mulai</span>
            </div>
        </a>
        <a class="card card-link" href="{{ route('write') }}">
            <div class="chapter-row">
                <div style="flex:1"><h3>Writing (TWE)</h3><p class="small muted">30 menit · esai dengan koreksi AI</p></div>
                <span class="badge">Mulai</span>
            </div>
        </a>
        @foreach ([] as [$name, $desc, $phase])
            <div class="card is-locked">
                <div class="chapter-row">
                    <div style="flex:1"><h3>{{ $name }}</h3><p class="small muted">{{ $desc }}</p></div>
                    <span class="badge badge-lock">Fase {{ $phase }}</span>
                </div>
            </div>
        @endforeach
    </div>
    <p class="muted small" style="margin-top:16px">Untuk sekarang, pemanasan terbaik adalah <a href="{{ route('chapter', 'structure-diagnostic-completion') }}"><b>Diagnostic completion</b></a>.</p>
@endsection
