import { postJson } from '../lib/http';

const CHECKS = [
    'Intro: topik dan posisimu jelas',
    'Body: tiap paragraf satu gagasan',
    'Dukungan: contoh, alasan, atau fakta',
    'Transisi antarparagraf (first, however, as a result)',
    'Conclusion: ulang posisi dengan kata lain',
    'Proofread 3-5 menit terakhir',
];

export default (config) => ({
    topics: config.topics,
    topicIdx: 0,
    text: '',
    left: 30 * 60,
    running: false,
    timerOn: true,
    tick: null,
    checks: CHECKS.map((label) => ({ label, done: false })),
    loading: false,
    feedback: '',
    error: '',
    aiReady: config.aiReady,

    init() {
        this.load();
        this.$watch('text', (v) => this.save(v));
    },

    key() {
        return 'togo.essay.' + this.topicIdx;
    },
    load() {
        try {
            this.text = localStorage.getItem(this.key()) || '';
        } catch {
            this.text = '';
        }
    },
    save(v) {
        try {
            localStorage.setItem(this.key(), v);
        } catch {
            /* abaikan */
        }
    },
    pick(i) {
        if (this.running) return;
        this.topicIdx = i;
        this.feedback = '';
        this.error = '';
        this.load();
    },

    get topic() {
        return this.topics[this.topicIdx];
    },
    get words() {
        const t = this.text.trim();
        return t ? t.split(/\s+/).length : 0;
    },
    get clock() {
        const m = Math.floor(this.left / 60);
        const s = this.left % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    },
    get proofreadTime() {
        return this.running && this.timerOn && this.left <= 300 && this.left > 0;
    },
    get canAsk() {
        return this.words >= 40 && !this.loading;
    },

    start() {
        this.running = true;
        this.left = 30 * 60;
        clearInterval(this.tick);
        this.tick = setInterval(() => {
            if (!this.timerOn) return;
            this.left -= 1;
            if (this.left <= 0) this.stop();
        }, 1000);
    },
    stop() {
        clearInterval(this.tick);
        this.running = false;
    },

    async ask() {
        if (!window.TOGO.auth) {
            this.error = 'Masuk dulu untuk memakai koreksi AI.';
            return;
        }
        if (!this.canAsk) return;
        this.loading = true;
        this.error = '';
        this.feedback = '';
        const { ok, data } = await postJson(window.TOGO.urls.tutor, { action: 'essay', topic: this.topic, essay: this.text.slice(0, 6000) });
        this.loading = false;
        if (ok) this.feedback = data.reply;
        else {
            if (data.setup) this.aiReady = false;
            this.error = data.message || 'Koreksi tidak bisa dilakukan sekarang.';
        }
    },
});
