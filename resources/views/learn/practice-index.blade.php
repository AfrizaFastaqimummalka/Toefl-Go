@extends('layouts.app')
@section('title', 'Latihan')
@section('content')
    <h1 class="page-title">Latihan</h1>
    <p class="page-sub">Pilih set soal, ulangi yang salah, atau latih kata rekomendasi.</p>

    @auth
        <a href="{{ route('review') }}" class="card card-hero card-link">
            <div class="chapter-row">
                <div class="num" aria-hidden="true">↻</div>
                <div style="flex:1">
                    <h3>Ulangi soal salah</h3>
                    <p class="small muted">{{ $due }} jatuh tempo sekarang · {{ $queued }} di antrean</p>
                </div>
                @if ($due)<span class="badge">{{ $due }}</span>@endif
            </div>
        </a>
    @else
        <div class="card card-hero">
            <h3 style="font-size:1.05rem">Ulangi soal salah</h3>
            <p class="small muted" style="margin-top:4px">Antrean ulang tersimpan per akun. <a href="{{ route('login') }}"><b>Masuk</b></a> supaya soal yang salah kembali muncul pada waktunya.</p>
        </div>
    @endauth

    <div class="section-head"><h2>Set soal</h2></div>
    <div class="stack">
        @foreach ($chapters as $chapter)
            @foreach ($chapter->lessons as $lesson)
                @php $total = $totals[$lesson->id] ?? 0; $d = $done[$lesson->id] ?? 0; @endphp
                <a class="card card-link" href="{{ route('lesson', [$chapter, $lesson->slug]) }}">
                    <div class="chapter-row">
                        <div class="num" aria-hidden="true">✎</div>
                        <div style="flex:1">
                            <h3>{{ $lesson->title }}</h3>
                            <p class="small muted">{{ $chapter->title }} · {{ $total }} soal</p>
                        </div>
                        <span class="badge">{{ $d }}/{{ $total }}</span>
                    </div>
                </a>
            @endforeach
        @endforeach
    </div>

    <div class="section-head"><h2>Kartu kata</h2></div>
    <div class="stack">
        @php
            $names = ['verb-forms' => 'Bentuk verb', 'connector-time' => 'Connector: time', 'connector-cause' => 'Connector: cause', 'connector-noun' => 'Connector: noun clause', 'connector-adjective' => 'Connector: adjective clause'];
        @endphp
        @foreach ($decks as $cat => $n)
            <a class="card card-link" href="{{ route('cards', $cat) }}">
                <div class="chapter-row">
                    <div class="num" aria-hidden="true">Aa</div>
                    <div style="flex:1"><h3>{{ $names[$cat] ?? $cat }}</h3><p class="small muted">{{ $n }} kartu</p></div>
                </div>
            </a>
        @endforeach
    </div>
@endsection
