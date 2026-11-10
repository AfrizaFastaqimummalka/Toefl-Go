<div x-data x-show="$store.tutor.open" x-cloak @keydown.escape.window="$store.tutor.hide()">
    <div class="sheet-backdrop" @click="$store.tutor.hide()"></div>
    <section class="sheet" role="dialog" aria-modal="true" aria-label="Tutor AI">
        <div class="sheet-grip"></div>
        <div class="sheet-head">
            <h2 style="font-size:1.25rem">Tutor AI</h2>
            <button type="button" class="btn btn-ghost btn-sm" @click="$store.tutor.hide()">Tutup</button>
        </div>
        <div class="sheet-body">
            <template x-if="!$store.tutor.aiReady">
                <div class="msg err">
                    AI belum aktif. Isi <b>GEMINI_API_KEY</b> di file <b>.env</b> (ambil di aistudio.google.com/apikey), lalu jalankan <b>php artisan config:clear</b>. Selain itu, app tetap jalan penuh.
                </div>
            </template>
            <template x-if="$store.tutor.msgs.length === 0">
                <div class="muted small">
                    Tanya apa saja soal Structure. Contoh:
                    <div class="stack" style="margin-top:8px">
                        <button type="button" class="btn btn-ghost btn-sm btn-block" @click="$store.tutor.input='Apa beda verb sejati dan bentuk -ing yang cuma penerang?'; $store.tutor.sendChat()">Beda verb sejati dan penerang?</button>
                        <button type="button" class="btn btn-ghost btn-sm btn-block" @click="$store.tutor.input='Kapan kalimat butuh connector?'; $store.tutor.sendChat()">Kapan kalimat butuh connector?</button>
                    </div>
                </div>
            </template>
            <template x-for="(m, idx) in $store.tutor.msgs" :key="idx">
                <div class="msg" :class="m.role" x-text="m.text"></div>
            </template>
            <div class="msg model" x-show="$store.tutor.loading" aria-live="polite">Sedang mikir…</div>
        </div>
        <form class="sheet-input" @submit.prevent="$store.tutor.sendChat()">
            <input class="input" type="text" maxlength="1000" placeholder="Tanya tutor…" x-model="$store.tutor.input" aria-label="Pesan untuk tutor">
            <button type="submit" class="btn btn-primary" :disabled="$store.tutor.loading || !$store.tutor.input.trim()">Kirim</button>
        </form>
    </section>
</div>
