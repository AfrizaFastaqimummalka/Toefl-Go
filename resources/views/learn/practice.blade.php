@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="practice" x-data="practice(@js($config))">

    {{-- Kosong (mis. antrean ulang belum ada yang jatuh tempo) --}}
    <template x-if="total === 0">
        <div>
            <a href="{{ $backUrl }}" class="back">← Kembali</a>
            <div class="card" style="margin-top:12px">
                <h2 style="font-size:1.4rem">Belum ada soal</h2>
                <p class="muted" style="margin-top:6px">Tidak ada soal yang jatuh tempo untuk diulang sekarang. Kerjakan latihan dulu, soal yang salah akan masuk antrean di sini.</p>
                <a href="{{ route('practice') }}" class="btn btn-primary btn-block" style="margin-top:14px">Ke daftar latihan</a>
            </div>
        </div>
    </template>

    {{-- Soal --}}
    <template x-if="total > 0 && !finished">
        <div>
            <div class="practice-top">
                <a href="{{ $backUrl }}" class="back" aria-label="Keluar dari latihan">✕</a>
                <div class="bar" role="progressbar" :aria-valuenow="pct" aria-valuemin="0" aria-valuemax="100"><i :style="'width:' + pct + '%'"></i></div>
                <div class="counter"><span x-text="i + 1"></span>/<span x-text="total"></span></div>
            </div>

            <div class="card q-card" :class="shakeKey && answered && !correct ? 'shake' : ''">

                {{-- Meta: testing point + status verifikasi --}}
                <div class="q-meta">
                    <span x-text="q.testingPoint.replaceAll('-', ' ')"></span>
                    <span class="unverified-icon" x-show="!q.verified" aria-label="Kunci belum diverifikasi">⚠ belum diverifikasi</span>
                </div>

                {{-- Completion --}}
                <template x-if="q.type === 'completion'">
                    <div>
                        <p class="prompt">Lengkapi kalimat</p>
                        <p class="q-text">
                            <span x-text="parts[0]"></span><span class="blank" :class="answered ? (correct ? 'is-ok' : 'is-bad') : ''" x-text="chosenText || '\u00a0\u00a0\u00a0\u00a0'"></span><span x-text="parts[1]"></span>
                        </p>
                        <div class="options" role="group" aria-label="Pilihan jawaban">
                            <template x-for="o in q.options" :key="o.id">
                                <button type="button" class="option"
                                        :class="answered ? (keyIs({option: o.id}) ? 'is-ok' : (isSel({option: o.id}) ? 'is-bad' : '')) : ''"
                                        :aria-pressed="isSel({option: o.id})" :disabled="answered" @click="pickOption(o.id)">
                                    <span class="key" x-text="o.id"></span>
                                    <span x-text="o.text"></span>
                                    <span class="mark" x-show="answered && keyIs({option: o.id})" aria-label="jawaban benar">✓</span>
                                    <span class="mark" x-show="answered && isSel({option: o.id}) && !keyIs({option: o.id})" aria-label="jawaban salah">✗</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Error find --}}
                <template x-if="q.type === 'error-find'">
                    <div>
                        <p class="prompt">Tap bagian yang salah, atau pilih opsi di bawah.</p>
                        <div class="segment-flow" role="group" aria-label="Bagian kalimat">
                            <template x-for="(s, idx) in q.segments" :key="idx">
                                <button type="button" class="seg-inline"
                                        :class="answered ? (keyIs({kind: 'segment', index: idx}) ? 'is-ok' : (isSel({kind: 'segment', index: idx}) ? 'is-bad' : '')) : ''"
                                        :aria-pressed="isSel({kind: 'segment', index: idx})" :disabled="answered" @click="pickSegment(idx)" x-text="s"></button>
                            </template>
                        </div>
                        <div class="alt-ctrl" role="group" aria-label="Opsi alternatif">
                            <button type="button"
                                    :class="answered ? (keyIs({kind: 'missing', what: 'subject'}) ? 'is-ok' : (isSel({kind: 'missing', what: 'subject'}) ? 'is-bad' : '')) : ''"
                                    :aria-pressed="isSel({kind: 'missing', what: 'subject'})" :disabled="answered" @click="pickKind('missing', 'subject')">Subject<br>hilang</button>
                            <button type="button"
                                    :class="answered ? (keyIs({kind: 'missing', what: 'verb'}) ? 'is-ok' : (isSel({kind: 'missing', what: 'verb'}) ? 'is-bad' : '')) : ''"
                                    :aria-pressed="isSel({kind: 'missing', what: 'verb'})" :disabled="answered" @click="pickKind('missing', 'verb')">Verb<br>hilang</button>
                            <button type="button"
                                    :class="answered ? (keyIs({kind: 'correct'}) ? 'is-ok' : (isSel({kind: 'correct'}) ? 'is-bad' : '')) : ''"
                                    :aria-pressed="isSel({kind: 'correct'})" :disabled="answered" @click="pickKind('correct')">Sudah<br>benar</button>
                        </div>
                    </div>
                </template>

                {{-- Panel bantuan (menggantikan 3 tombol terpisah) --}}
                <div x-data="{ showHelp: false }" x-show="!answered">
                    <div style="display:flex;align-items:center;gap:10px;margin-top:16px">
                        <button type="button" class="help-trigger" @click="showHelp = !showHelp" :aria-expanded="showHelp">
                            <span x-text="showHelp ? '✕ Tutup' : '? Bantuan'"></span>
                        </button>
                        <div class="hint-dots" aria-label="Hint dipakai" role="img">
                            <span class="hint-dot" :class="hints >= 1 ? 'used' : ''"></span>
                            <span class="hint-dot" :class="hints >= 2 ? 'used' : ''"></span>
                            <span class="hint-dot" :class="hints >= 3 ? 'used' : ''"></span>
                        </div>
                    </div>

                    <div class="help-panel" x-show="showHelp" x-cloak>

                        {{-- Hint bertingkat --}}
                        <div class="help-section">
                            <div class="help-title">Hint bertingkat</div>
                            <ol class="hint-list" x-show="hints > 0" aria-live="polite" style="margin:0 0 8px;padding-left:1.2rem">
                                <template x-for="(h, idx) in hintText" :key="idx">
                                    <li class="hint-item" style="list-style:none;padding-left:0;margin-bottom:6px">
                                        <b x-text="'Hint ' + (idx + 1) + ': '"></b><span x-text="h"></span>
                                    </li>
                                </template>
                            </ol>
                            <button type="button" class="btn btn-ghost btn-sm" :disabled="hints >= 3" @click="revealHint()">
                                <span x-text="hints === 0 ? 'Tampilkan hint (1/3)' : (hints >= 3 ? 'Hint habis' : 'Hint berikutnya (' + (hints + 1) + '/3)')"></span>
                            </button>
                        </div>

                        {{-- Kata rekomendasi --}}
                        <div class="help-section" x-show="hints > 0 && cards.length">
                            <div class="help-title">Kata rekomendasi</div>
                            <button type="button" class="btn btn-ghost btn-sm" @click="showReco = !showReco" x-text="showReco ? 'Sembunyikan' : 'Tampilkan kata'"></button>
                            <div class="reco" x-show="showReco && cards.length" x-cloak>
                                <div class="reco-tabs" role="tablist">
                                    <template x-for="c in recoCats" :key="c">
                                        <button type="button" :aria-pressed="recoCat === c" @click="recoCat = c" x-text="recoLabel(c)"></button>
                                    </template>
                                </div>
                                <div class="reco-list">
                                    <template x-for="(c, idx) in recoCards" :key="idx">
                                        <div class="reco-item">
                                            <b x-text="c.term"></b> <span class="muted small" x-text="'· ' + c.group"></span>
                                            <div class="small" x-text="c.definition"></div>
                                            <div class="small muted" x-text="c.example"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- AI hint --}}
                        <div class="help-section">
                            <div class="help-title">AI</div>
                            <button type="button" class="ai-link" @click="askHint()">AI: hint tanpa bocor jawaban</button>
                        </div>

                    </div>
                </div>

                {{-- Sentence X-ray --}}
                <div style="margin-top:12px">
                    <button type="button" class="help-trigger" x-show="canXray && !showXray" @click="showXray = true">
                        🔍 Bedah kalimat (X-ray)
                    </button>
                </div>
                <div @xray-checked="onXrayChecked()">
                    <template x-if="showXray">
                        @include('partials.xray')
                    </template>
                </div>

                {{-- Umpan balik setelah menjawab --}}
                <div class="feedback" :class="correct ? 'ok' : 'bad'" x-show="answered" role="status" aria-live="polite">
                    <div class="feedback-head">
                        <span x-text="correct ? '✓ Tepat' : '✗ Belum tepat'"></span>
                    </div>
                    <p style="margin-top:6px;font-size:0.95rem" x-text="q.explanation"></p>
                    <p class="small muted" style="margin-top:8px" x-show="!correct" x-text="'Kunci: ' + keyText(q)"></p>
                    <p class="small muted" style="margin-top:4px" x-show="q.note" x-text="q.note"></p>
                    <p class="small muted" style="margin-top:4px" x-show="q.sourcePage" x-text="'Sumber: hal. ' + q.sourcePage"></p>
                    <p class="small" style="margin-top:4px;color:var(--bad)" x-show="saveError" x-text="saveError"></p>
                    <div class="ai-links">
                        <button type="button" class="ai-link" x-show="!correct" @click="askExplain()">AI: jelaskan kenapa salah</button>
                        <button type="button" class="ai-link" @click="askSimilar()">AI: buat 3 soal serupa</button>
                    </div>
                </div>
            </div>

            <div class="action-bar" :class="answered ? 'answered' : ''">
                <button type="button" class="btn btn-primary btn-block" x-show="!answered" :disabled="!sel" @click="submit()">Periksa</button>
                <button type="button" class="btn btn-primary btn-block" x-show="answered" :disabled="saving" @click="next()" x-text="i + 1 >= total ? 'Selesai' : 'Lanjut →'"></button>
            </div>
        </div>
    </template>

    {{-- Layar selesai --}}
    <template x-if="finished">
        <div>
            <div class="card" style="text-align:center">
                <div class="eyebrow">Selesai</div>
                <div class="score-ring" :style="'--p:' + Math.round(score / results.length * 100)"><div x-text="score + '/' + results.length"></div></div>
                <h2 style="font-size:1.5rem" x-text="wrong.length === 0 ? 'Bersih, semuanya benar' : (wrong.length + ' soal perlu diulang')"></h2>
                <p class="muted" style="margin-top:6px" x-show="window.TOGO.auth" x-text="'+' + xpEarned + ' XP'"></p>
                <p class="muted small" style="margin-top:6px" x-show="!window.TOGO.auth">Kamu belum masuk, jawaban tersimpan di perangkat ini. Masuk supaya progres tersimpan di akun dan digabung otomatis.</p>
            </div>

            <div class="card" x-show="wrong.length" style="margin-top:12px">
                <h3 style="font-size:1.1rem;margin-bottom:10px">Yang perlu dicek lagi</h3>
                <template x-for="r in wrong" :key="r.code">
                    <div style="padding:10px 0;border-top:1px solid var(--line)">
                        <p class="small" x-text="r.q.type === 'completion' ? r.q.text : r.q.text"></p>
                        <p class="small" style="color:var(--bad);margin-top:4px" x-text="'Jawabanmu: ' + answerText(r)"></p>
                        <p class="small" style="color:var(--ok)" x-text="'Kunci: ' + keyText(r.q)"></p>
                    </div>
                </template>
            </div>

            <div class="stack" style="margin-top:14px">
                <button type="button" class="btn btn-block" x-show="wrong.length" @click="retryWrong()">Ulangi yang salah</button>
                <a :href="nextUrl" class="btn btn-primary btn-block">Lanjut</a>
            </div>
        </div>
    </template>
</div>
@endsection
