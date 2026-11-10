@extends('layouts.app')
@section('title', $lesson->title)
@section('skill', $chapter->section)
@section('content')
    <a href="{{ route('chapter', $chapter) }}" class="back">← {{ $chapter->title }}</a>
    <h1 class="page-title" style="margin-top:4px">{{ $lesson->title }}</h1>

    <div class="prose card" style="margin-top:12px">
        @foreach ($lesson->content as $b)
            @switch($b['t'])
                @case('h')
                    <h3>{{ $b['text'] }}</h3>
                    @break
                @case('p')
                    <p>{{ $b['text'] }}</p>
                    @break
                @case('list')
                    <ul>@foreach ($b['items'] as $it)<li>{{ $it }}</li>@endforeach</ul>
                    @break
                @case('xray')
                    <div class="xray-demo" x-data="xray(@js(['marked' => $b['marked'], 'mode' => 'static']))">
                        <div class="sentence" style="line-height:2.1">
                            <template x-for="t in tokens" :key="t.i">
                                <span class="word static" :class="'t-' + tagOf(t)"><span x-text="t.w + t.trail"></span><sup x-show="label(t)" x-text="label(t)"></sup></span>
                            </template>
                        </div>
                        <p class="caption">{{ $b['caption'] }}</p>
                    </div>
                    @break
            @endswitch
        @endforeach
        <div class="legend" aria-label="Keterangan warna">
            <span><b style="color:var(--m)">depan</b> frasa depan (dicoret)</span>
            <span><b style="color:var(--m)">pen</b> penerang</span>
            <span><b style="color:var(--s)">S</b> subject</span>
            <span><b style="color:var(--v)">V</b> verb sejati</span>
            <span><b style="color:var(--c)">C</b> connector</span>
        </div>
    </div>

    <a href="{{ $nextUrl }}" class="btn btn-primary btn-block" style="margin-top:16px">Lanjut ke latihan</a>
@endsection
