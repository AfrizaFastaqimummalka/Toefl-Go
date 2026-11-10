<div class="xray" x-data="{ showLegend: false, ...xray(xrayCfg) }">
    <div class="xray-head">
        <h3 style="font-size:0.95rem;font-weight:700">Sentence X-ray</h3>
        <button type="button" class="legend-toggle" @click="showLegend = !showLegend" :aria-expanded="showLegend">
            <span x-text="showLegend ? 'Sembunyikan arti warna' : 'Arti warna'"></span>
        </button>
    </div>

    {{-- Legenda tersembunyi di balik toggle --}}
    <div class="legend" x-show="showLegend" x-cloak>
        <span><b style="color:var(--m)">depan</b> frasa depan</span>
        <span><b style="color:var(--m)">pen</b> penerang</span>
        <span><b style="color:var(--s)">S</b> subject</span>
        <span><b style="color:var(--v)">V</b> verb sejati</span>
        <span><b style="color:var(--c)">C</b> connector</span>
    </div>

    {{-- Stepper: tampilkan satu tahap aktif saja --}}
    <div class="xray-stepper" x-show="!shown">
        <button type="button" class="xray-step-btn"
                @click="tool = tools[Math.max(0, tools.findIndex(t => t.k === tool) - 1)].k"
                :disabled="tool === tools[0].k"
                aria-label="Tahap sebelumnya">‹</button>
        <div class="xray-step-label">
            <span x-text="'Langkah ' + (tools.findIndex(t => t.k === tool) + 1) + ' dari ' + tools.length + ': ' + tools.find(t => t.k === tool).label"></span>
            <span class="xray-step-sub">Tap kata untuk menandai</span>
        </div>
        <button type="button" class="xray-step-btn"
                @click="nextTool()"
                :disabled="tool === tools[tools.length - 1].k"
                aria-label="Tahap berikutnya">›</button>
    </div>

    <div class="sentence">
        <template x-for="t in tokens" :key="t.i">
            <button type="button" class="word" :class="['t-' + tagOf(t), miss(t) ? 'miss' : '']" :aria-label="aria(t)" @click="tap(t.i)">
                <span x-text="t.w + t.trail"></span><sup x-show="label(t)" x-text="label(t)"></sup>
            </button>
        </template>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px" x-show="!shown">
        <button type="button" class="btn btn-primary btn-sm" @click="check()">Cek tandaanku</button>
        <button type="button" class="btn btn-ghost btn-sm" @click="reveal()">Tampilkan jawaban</button>
        <button type="button" class="btn btn-ghost btn-sm" @click="reset()">Ulang</button>
    </div>

    <div class="verdict" x-show="shown" aria-live="polite">
        <p class="line" x-show="score" x-text="score ? 'Tandaanmu tepat ' + score.right + ' dari ' + score.total + ' kata. Garis putus-putus = perlu dicek.' : ''"></p>
        <p style="margin-top:6px"><b x-text="'Klausa: ' + verdict.clauses"></b> · <span x-text="verdict.subj + ' subject, ' + verdict.verb + ' verb sejati, ' + verdict.conn + ' connector'"></span></p>
        <p style="margin-top:6px" x-text="verdict.text"></p>
        <p class="small muted" style="margin-top:6px" x-show="note" x-text="note"></p>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
            <button type="button" class="btn btn-ghost btn-sm" x-show="fixed" @click="showFixed = !showFixed" x-text="showFixed ? 'Sembunyikan versi benar' : 'Lihat versi benar'"></button>
            <button type="button" class="btn btn-ghost btn-sm" @click="reset()">Coba lagi</button>
        </div>
        <div class="sentence" style="margin-top:10px" x-show="showFixed && fixed">
            <template x-for="t in (fixed ? fixed.tokens : [])" :key="t.i">
                <span class="word static" :class="'t-' + (t.tag === 'R' ? 'C' : t.tag)"><span x-text="t.w + t.trail"></span><sup x-show="t.tag" x-text="t.tag === 'P' ? 'depan' : (t.tag === 'M' ? 'pen' : (t.tag === 'R' ? 'C+S' : t.tag))"></sup></span>
            </template>
        </div>
    </div>
</div>
