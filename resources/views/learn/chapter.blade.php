@extends('layouts.app')
@section('title', $chapter->title)
@section('skill', $chapter->section)
@section('content')
    <a href="{{ route('home') }}" class="back">← Belajar</a>
    <h1 class="page-title" style="margin-top:4px">{{ $chapter->title }}</h1>
    <p class="page-sub">{{ $chapter->subtitle }}</p>

    <div class="stack">
        @foreach ($chapter->lessons as $lesson)
            @php
                $total = $totals[$lesson->id] ?? 0;
                $d = $done[$lesson->id] ?? 0;
            @endphp
            <a class="card card-link" href="{{ route('lesson', [$chapter, $lesson->slug]) }}">
                <div class="chapter-row">
                    <div class="num" aria-hidden="true">{{ $lesson->kind === 'summary' ? '≡' : '✎' }}</div>
                    <div style="flex:1">
                        <h3>{{ $lesson->title }}</h3>
                        <p class="small muted">{{ $lesson->kind === 'summary' ? 'Rangkuman dan contoh' : $total.' soal' }}</p>
                    </div>
                    @if ($lesson->kind === 'practice' && $total)
                        <span class="badge">{{ $d }}/{{ $total }}</span>
                    @endif
                </div>
                @if ($lesson->kind === 'practice' && $total)
                    <div class="bar" style="margin-top:12px"><i style="width:{{ min(100, round($d / $total * 100)) }}%"></i></div>
                @endif
            </a>
        @endforeach
    </div>
@endsection
