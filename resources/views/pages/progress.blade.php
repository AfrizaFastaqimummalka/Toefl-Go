@extends('layouts.app')
@section('title', 'Progres')
@section('content')
    <h1 class="page-title">Progres</h1>

    @auth
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:14px">
            <div class="card" style="text-align:center"><div class="eyebrow">XP</div><div style="font-family:var(--font-display);font-size:1.6rem;font-weight:700">{{ auth()->user()->xp }}</div></div>
            <div class="card" style="text-align:center"><div class="eyebrow">Streak</div><div style="font-family:var(--font-display);font-size:1.6rem;font-weight:700">{{ auth()->user()->streak }}</div></div>
            <div class="card" style="text-align:center"><div class="eyebrow">Jawaban</div><div style="font-family:var(--font-display);font-size:1.6rem;font-weight:700">{{ $summary['total'] }}</div></div>
        </div>

        @if ($summary['weakest'])
            @php $w = $summary['weakest']; $ch = $w['chapter'] ? \App\Models\Chapter::where('slug', $w['chapter'])->first() : null; @endphp
            <div class="card card-hero" style="margin-top:12px">
                <div class="eyebrow">Terlemah saat ini</div>
                <h3 style="font-size:1.2rem;margin-top:4px">{{ $w['label'] }} ({{ $w['pct'] }}%)</h3>
                @if ($ch && $ch->available)
                    <a href="{{ route('chapter', $ch) }}" class="btn btn-primary btn-sm" style="margin-top:10px">Latih {{ $ch->title }}</a>
                @else
                    <p class="small muted" style="margin-top:4px">Babnya menyusul di fase berikutnya.</p>
                @endif
            </div>
        @endif

        @if ($summary['due'])
            <a href="{{ route('review') }}" class="btn btn-block" style="margin-top:12px">Ulangi {{ $summary['due'] }} soal yang jatuh tempo</a>
        @endif

        <div class="section-head"><h2>Skor per testing point</h2></div>
        <div class="card">
            @foreach ($summary['points'] as $p)
                <div class="tp-row">
                    <span style="font-weight:600">{{ $p['label'] }}</span>
                    <span class="small muted">{{ $p['attempted'] ? $p['correct'].'/'.$p['attempted'].' · '.$p['pct'].'%' : 'belum dicoba' }}</span>
                    <div class="bar"><i style="width:{{ $p['pct'] ?? 0 }}%"></i></div>
                </div>
            @endforeach
        </div>

        <div class="section-head"><h2>Data</h2></div>
        <div class="stack">
            <a href="{{ route('progress.export') }}" class="btn btn-block">Export JSON</a>
            <form method="POST" action="{{ route('progress.reset') }}" onsubmit="return confirm('Hapus semua progres, XP, dan antrean ulang? Ini tidak bisa dibatalkan.')">
                @csrf
                <button type="submit" class="btn btn-ghost btn-block" style="color:var(--bad)">Reset progres</button>
            </form>
        </div>
    @else
        <div x-data="guestProgress(@js(collect(config('toefl.testing_points'))->map(fn ($p) => $p['label'])))">
            <div class="card" style="margin-top:12px">
                <p>Kamu belum masuk, jadi progres hanya tersimpan di perangkat ini (<b x-text="total"></b> jawaban). <a href="{{ route('login') }}"><b>Masuk</b></a> atau <a href="{{ route('register') }}"><b>daftar</b></a> untuk menyimpannya ke akun. Jawaban tamu otomatis digabung.</p>
            </div>
            <div class="section-head"><h2>Skor per testing point</h2></div>
            <div class="card">
                <template x-for="r in rows" :key="r.key">
                    <div class="tp-row">
                        <span style="font-weight:600" x-text="r.label"></span>
                        <span class="small muted" x-text="r.attempted ? r.pct + '% dari ' + r.attempted + ' jawaban' : 'belum dicoba'"></span>
                        <div class="bar"><i :style="'width:' + (r.pct ?? 0) + '%'"></i></div>
                    </div>
                </template>
            </div>
        </div>
    @endauth
@endsection
