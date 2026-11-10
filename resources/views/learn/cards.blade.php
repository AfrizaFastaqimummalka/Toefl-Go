@extends('layouts.app')
@section('title', 'Kartu kata')
@section('content')
    <a href="{{ route('practice') }}" class="back">← Latihan</a>
    @php
        $names = ['verb-forms' => 'Bentuk verb', 'connector-time' => 'Time', 'connector-cause' => 'Cause', 'connector-noun' => 'Noun clause', 'connector-adjective' => 'Adjective clause'];
    @endphp
    <h1 class="page-title" style="margin-top:4px">Kartu kata</h1>
    <div class="reco-tabs" style="margin-bottom:14px">
        @foreach ($cats as $c)
            <a href="{{ route('cards', $c) }}" class="pill" style="min-height:44px;{{ $c === $category ? 'background:var(--accent);color:var(--accent-ink);border-color:var(--accent)' : '' }}">{{ $names[$c] ?? $c }}</a>
        @endforeach
    </div>

    <div x-data="cards(@js($cards))">
        <div class="flip" :class="flipped ? 'on' : ''">
            <div class="flip-inner" @click="flip()" role="button" tabindex="0" @keydown.enter="flip()" @keydown.space.prevent="flip()" :aria-label="flipped ? 'Balik ke depan' : 'Balik kartu'">
                <div class="flip-face">
                    <span class="badge" x-text="c.group"></span>
                    <div class="term" style="margin-top:10px" x-text="c.term"></div>
                    <p class="small muted" style="margin-top:12px">Tap untuk membalik</p>
                </div>
                <div class="flip-face flip-back">
                    <p style="font-weight:700" x-text="c.definition"></p>
                    <p class="muted" style="margin-top:10px" x-text="c.example"></p>
                </div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:center;margin-top:14px">
            <button type="button" class="btn btn-block" @click="go(-1)">← Sebelumnya</button>
            <span class="small muted" x-text="(i + 1) + '/' + cards.length"></span>
            <button type="button" class="btn btn-primary btn-block" @click="go(1)">Berikutnya →</button>
        </div>
    </div>
@endsection
