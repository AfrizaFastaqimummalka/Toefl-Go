@extends('layouts.app')
@section('title', 'Belajar')
@section('content')
    <h1 class="page-title">Belajar</h1>
    <p class="page-sub">Ringkas, bertahap, dan ada latihannya. Mulai dari yang paling bikin bingung.</p>

    <a href="{{ route('lesson', ['chapter' => 'structure-subject-verb', 'lessonSlug' => 'ringkasan']) }}" class="card card-hero card-link">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <span class="eyebrow">Mulai di sini</span>
            <span style="font-size:1.2rem;font-weight:800">↗</span>
        </div>
        <h2 style="font-size:1.45rem;line-height:1.25;margin-bottom:8px">Kapan -ing, -ed, dan to + verb benar-benar jadi verb?</h2>
        <p class="muted small" style="line-height:1.5">Subject & Verb dengan Sentence X-ray: bedah struktur kalimat, tandai komponennya, dan kuasai polanya.</p>
    </a>

    @foreach ($sections as $key => $label)
        @php $list = $chapters->get($key, collect()); @endphp
        @continue($list->isEmpty())
        <div class="section-head">
            <h2><span class="skill-dot" style="background:var(--{{ $key }})"></span>{{ $label }}</h2>
        </div>
        <div class="stack">
            @foreach ($list as $c)
                @if ($c->available)
                    <a class="card card-link" href="{{ route('chapter', $c) }}" style="--accent:var(--{{ $key }})">
                        <div class="chapter-row">
                            <div class="num">{{ $loop->iteration }}</div>
                            <div><h3>{{ $c->title }}</h3><p class="small muted">{{ $c->subtitle }}</p></div>
                        </div>
                    </a>
                @else
                    <div class="card is-locked" style="--accent:var(--{{ $key }})">
                        <div class="chapter-row">
                            <div class="num">{{ $loop->iteration }}</div>
                            <div style="flex:1"><h3>{{ $c->title }}</h3><p class="small muted">{{ $c->subtitle }}</p></div>
                            <span class="badge badge-lock">Fase {{ $c->phase }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
@endsection
