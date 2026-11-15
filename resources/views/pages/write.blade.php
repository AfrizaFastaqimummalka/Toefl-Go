@extends('layouts.app')
@section('title', 'Menulis')
@section('skill', 'writing')
@section('content')
<div x-data="essay(@js(['topics' => $topics, 'aiReady' => $aiReady]))">
    <a href="{{ route('chapter', 'writing-twe') }}" class="back">← Writing</a>
    <h1 class="page-title" style="margin-top:4px">Tulis esai</h1>
    <p class="page-sub">30 menit, lalu minta koreksi AI dengan skala 1-6. Drafmu tersimpan otomatis di perangkat ini.</p>

    <div class="card">
        <div class="eyebrow">Topik</div>
        <p style="margin-top:6px;font-weight:600" x-text="topic"></p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px" role="group" aria-label="Pilih topik">
            <template x-for="(t, i) in topics" :key="i">
                <button type="button" class="pill" style="min-width:44px;justify-content:center" :style="i === topicIdx ? 'background:var(--accent);color:var(--accent-ink);border-color:var(--accent)' : ''" :disabled="running" @click="pick(i)" x-text="i + 1"></button>
            </template>
        </div>
    </div>

    <div class="card" style="margin-top:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="small muted" x-text="words + ' kata'"></span>
            <div style="display:flex;gap:8px;align-items:center">
                <label class="check-row small"><input type="checkbox" x-model="timerOn"> Timer</label>
                <span x-show="running && timerOn" :style="proofreadTime ? 'color:var(--bad);font-weight:800' : 'font-weight:700'" x-text="'⏱ ' + clock"></span>
            </div>
        </div>
        <p class="small" style="margin-top:6px;color:var(--bad);font-weight:700" x-show="proofreadTime">Waktunya proofread (sisa 5 menit).</p>
        <textarea class="essay-area" style="margin-top:10px" x-model="text" placeholder="Tulis esaimu di sini..." aria-label="Esai"></textarea>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
            <button type="button" class="btn btn-sm" x-show="!running" @click="start()">Mulai timer 30 menit</button>
            <button type="button" class="btn btn-sm" x-show="running" @click="stop()">Berhenti</button>
            <button type="button" class="btn btn-primary btn-sm" :disabled="!canAsk" @click="ask()"><span x-text="loading ? 'Menilai...' : 'Koreksi esaiku'"></span></button>
        </div>
        <p class="small muted" style="margin-top:8px" x-show="words < 40">Tulis minimal 40 kata supaya bisa dinilai.</p>
        <p class="small" style="margin-top:8px;color:var(--bad)" x-show="error" x-text="error"></p>
        <p class="small muted" style="margin-top:8px" x-show="!aiReady">AI belum aktif: isi GEMINI_API_KEY di .env. Editor, timer, dan checklist tetap bisa dipakai.</p>
    </div>

    <div class="card" style="margin-top:12px" x-show="feedback" x-cloak>
        <div class="eyebrow">Umpan balik tutor</div>
        <p class="feedback-text" style="margin-top:6px" x-text="feedback"></p>
    </div>

    <div class="card" style="margin-top:12px">
        <h3 style="font-size:1.05rem;margin-bottom:6px">Checklist</h3>
        <template x-for="(c, i) in checks" :key="i">
            <label class="check-row"><input type="checkbox" x-model="c.done"><span x-text="c.label"></span></label>
        </template>
    </div>
</div>
@endsection
