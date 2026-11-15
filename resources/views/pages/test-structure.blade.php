@extends('layouts.app')
@section('title', $config['title'])
@section('content')
<div class="practice" x-data="simulasi(@js($config))" @beforeunload.window="phase === 'run' ? $event.preventDefault() : null">

    {{-- Intro --}}
    <template x-if="phase === 'intro'">
        <div>
            <a href="{{ route('test') }}" class="back">← Tes</a>
            <div class="card" style="margin-top:12px">
                <div class="eyebrow">Simulasi</div>
                <h1 style="font-size:1.6rem;margin:4px 0 8px" x-text="title"></h1>
                <p class="muted" x-text="intro"></p>
                <label style="display:flex;align-items:center;gap:10px;margin-top:14px;min-height:44px">
                    <input type="checkbox" x-model="timerOn" style="width:22px;height:22px">
                    <span x-text="timerLabel"></span>
                </label>
                <button type="button" class="btn btn-primary btn-block" style="margin-top:12px" @click="start()">Mulai</button>
                <p class="small muted" style="margin-top:10px">Soal bertanda "kunci belum diverifikasi" ikut dihitung, jadi anggap skornya perkiraan.</p>
            </div>
        </div>
    </template>

    {{-- Mengerjakan --}}
    <template x-if="phase === 'run'">
        <div>
            <div class="practice-top">
                <div class="bar"><i :style="'width:' + Math.round(answeredCount / qs.length * 100) + '%'"></i></div>
                <div class="counter"><span x-text="i + 1"></span>/<span x-text="qs.length"></span></div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                <span class="small muted" x-text="answeredCount + ' dijawab'"></span>
                <button type="button" class="btn btn-ghost btn-sm" @click="timerOn = !timerOn" :aria-pressed="timerOn">
                    <span x-show="timerOn" :style="lowTime ? 'color:var(--bad);font-weight:800' : ''" x-text="'⏱ ' + clock"></span>
                    <span x-show="!timerOn">Timer mati</span>
                </button>
            </div>

            <div class="card q-card">
                <template x-if="q.type === 'completion'">
                    <div>
                        @include('partials.passage')
                        <p class="prompt" x-text="q.script ? 'Dengarkan, lalu jawab' : (q.passage ? 'Baca passage, lalu jawab' : 'Lengkapi kalimat')"></p>
                        <div class="audio-box" x-data="audioPlayer" x-show="q.script" x-effect="q; stop()" x-cloak>
                            <div x-show="supported" class="audio-controls">
                                <button type="button" class="btn btn-primary btn-sm" x-show="!playing" @click="play(q.script)">▶ Putar audio</button>
                                <button type="button" class="btn btn-sm" x-show="playing" @click="stop()">■ Berhenti</button>
                                <label class="audio-rate"><span class="small muted">Kecepatan</span>
                                    <select x-model.number="rate" class="input" aria-label="Kecepatan audio">
                                        <option value="0.8">0.8x</option><option value="1">1x</option><option value="1.2">1.2x</option>
                                    </select>
                                </label>
                            </div>
                            <p class="small muted" x-show="!supported">Browser ini tidak punya suara bawaan. Coba Chrome atau Edge terbaru.</p>
                        </div>
                        <p class="q-text"><span x-text="parts[0]"></span><span class="blank" x-show="!q.noBlank" x-text="chosenText || '\u00a0\u00a0\u00a0\u00a0'"></span><span x-text="parts[1]"></span></p>
                        <div class="options" role="group" aria-label="Pilihan jawaban">
                            <template x-for="o in q.options" :key="o.id">
                                <button type="button" class="option" :aria-pressed="isSel({option: o.id})" @click="pickOption(o.id)">
                                    <span class="key" x-text="o.id"></span><span x-text="o.text"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="q.type === 'error-find'">
                    <div>
                        <p class="prompt">Tap bagian yang salah</p>
                        <div class="segment-flow" role="group" aria-label="Bagian kalimat">
                            <template x-for="(s, idx) in q.segments" :key="idx">
                                <button type="button" class="seg-inline" :aria-pressed="isSel({kind: 'segment', index: idx})" :disabled="(q.plain || []).includes(idx)" :data-plain="(q.plain || []).includes(idx)" @click="pickSegment(idx)" x-text="s"></button>
                            </template>
                        </div>
                        <div class="alt-ctrl" x-show="!q.noAlt" style="margin-top:14px">
                            <button type="button" :aria-pressed="isSel({kind: 'correct'})" @click="pickKind('correct')">Sudah benar</button>
                        </div>
                    </div>
                </template>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:14px" aria-label="Nomor soal">
                <template x-for="(x, n) in qs" :key="x.code">
                    <button type="button" class="pill" style="min-width:40px;justify-content:center" :class="dotClass(n)" :style="(n === i ? 'background:var(--accent);color:var(--accent-ink);' : (answers[x.code] ? 'border-color:var(--accent);' : ''))" @click="jump(n)" x-text="n + 1"></button>
                </template>
            </div>

            <div class="action-bar" style="grid-template-columns:auto 1fr 1fr">
                <button type="button" class="btn btn-ghost" :disabled="i === 0" @click="go(-1)">←</button>
                <button type="button" class="btn" :disabled="i === qs.length - 1" @click="go(1)">Berikutnya</button>
                <button type="button" class="btn btn-primary" @click="finish()">Selesai</button>
            </div>
        </div>
    </template>

    {{-- Hasil --}}
    <template x-if="phase === 'done'">
        <div>
            <div class="card" style="text-align:center">
                <div class="eyebrow" x-text="results.auto ? 'Waktu habis' : 'Selesai'"></div>
                <div class="score-ring" :style="'--p:' + Math.round(results.score / results.total * 100)"><div x-text="results.score + '/' + results.total"></div></div>
                <p class="muted" x-text="results.answered + ' soal dijawab dalam ' + Math.floor(results.seconds / 60) + ' menit ' + (results.seconds % 60) + ' detik'"></p>
                <p class="small" style="color:var(--bad);margin-top:6px" x-show="saveError" x-text="saveError"></p>
                <p class="small muted" style="margin-top:6px" x-show="!window.TOGO.auth">Kamu belum masuk, hasil tersimpan di perangkat ini saja.</p>
            </div>

            <div class="card" style="margin-top:12px">
                <h3 style="font-size:1.1rem;margin-bottom:10px">Per testing point</h3>
                <template x-for="t in results.byTp" :key="t.key">
                    <div class="tp-row">
                        <span style="font-weight:600;text-transform:capitalize" x-text="t.label"></span>
                        <span class="small muted" x-text="t.right + '/' + t.total + ' · ' + t.pct + '%'"></span>
                        <div class="bar"><i :style="'width:' + t.pct + '%'"></i></div>
                    </div>
                </template>
            </div>

            <div class="card" style="margin-top:12px" x-show="results.wrong.length">
                <h3 style="font-size:1.1rem;margin-bottom:10px" x-text="results.wrong.length + ' soal salah atau kosong'"></h3>
                <template x-for="w in results.wrong" :key="w.code">
                    <div style="padding:14px 0;border-top:1px solid var(--line)">
                        <p class="small font-semibold" style="line-height:1.5" x-text="w.text"></p>
                        <div class="feedback-compare" style="margin:8px 0">
                            <div class="compare-item is-wrong">
                                <span class="compare-label">📌 Jawabanmu:</span>
                                <span class="compare-val" x-text="w.given"></span>
                            </div>
                            <div class="compare-item is-key">
                                <span class="compare-label">🔑 Kunci:</span>
                                <span class="compare-val" x-text="w.key"></span>
                            </div>
                        </div>
                        <div class="feedback-section" style="margin-top:8px" x-show="w.explanation">
                            <div class="section-title">📖 Penjelasan:</div>
                            <p class="section-text small" x-text="w.explanation"></p>
                        </div>
                        <div class="feedback-conclusion" style="margin-top:8px" x-show="w.rule">
                            <div class="conclusion-title">💡 Kesimpulan & Pola:</div>
                            <p class="conclusion-text small" x-text="w.rule"></p>
                        </div>
                        <p class="small muted" style="margin-top:6px" x-show="!w.verified">Kunci belum diverifikasi.</p>
                        <template x-if="w.script">
                            <div class="audio-script" style="margin-top:8px">
                                <p class="small" x-show="w.reconstructed" style="font-weight:700">Script rekonstruksi (audio di buku tidak tersedia).</p>
                                <template x-for="(l, idx) in w.script" :key="idx"><p class="small"><b x-text="({M: 'Pria', W: 'Wanita', N: 'Narator'}[l[0]] || l[0]) + ': '"></b><span x-text="l[1]"></span></p></template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="stack" style="margin-top:14px">
                <a href="{{ $config['retryUrl'] }}" class="btn btn-block">Ulangi simulasi</a>
                <a href="{{ route('progress') }}" class="btn btn-primary btn-block">Lihat progres</a>
            </div>
        </div>
    </template>
</div>
@endsection
