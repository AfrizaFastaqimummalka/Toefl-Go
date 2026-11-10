@extends('layouts.app')
@section('title', 'Tes')
@section('content')
    <h1 class="page-title">Tes</h1>
    <p class="page-sub">Simulasi dengan timer yang bisa dimatikan. Hadir bertahap.</p>
    <div class="stack">
        @foreach ([['Structure', '25 menit · 15 completion + 25 error identification', 2], ['Listening', 'sekitar 35 menit', 3], ['Reading', '55 menit', 4], ['Writing (TWE)', '30 menit', 5]] as [$name, $desc, $phase])
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
