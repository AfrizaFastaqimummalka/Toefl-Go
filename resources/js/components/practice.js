import { postJson } from '../lib/http';
import { guest } from '../lib/guest';

function sameAnswer(q, a) {
    const k = q.answer;
    if (q.type === 'completion') return a.option === k.option;
    if (a.kind !== k.kind) return false;
    if (k.kind === 'segment') return a.index === k.index;
    if (k.kind === 'missing') return a.what === k.what;
    return true;
}

function describe(q, a) {
    if (q.type === 'completion') {
        const o = q.options.find((x) => x.id === a.option);
        return o ? `${o.id}. ${o.text}` : '';
    }
    if (a.kind === 'segment') return `bagian "${q.segments[a.index]}" yang salah`;
    if (a.kind === 'missing') return `kalimat kekurangan ${a.what}`;
    return 'kalimat sudah benar';
}

export default (config) => ({
    mode: config.mode,
    all: config.questions,
    queue: [...config.questions],
    cards: config.cards ?? [],
    nextUrl: config.nextUrl,
    i: 0,
    sel: null,
    answered: false,
    correct: false,
    hints: 0,
    showXray: false,
    showReco: false,
    recoCat: null,
    results: [],
    xpEarned: 0,
    finished: false,
    started: 0,
    saving: false,
    saveError: '',
    shakeKey: 0,

    init() {
        this.started = Date.now();
        const cats = this.recoCats;
        this.recoCat = cats[0] ?? null;
    },

    get q() {
        return this.queue[this.i];
    },
    get total() {
        return this.queue.length;
    },
    get pct() {
        return this.total ? Math.round((this.i / this.total) * 100) : 0;
    },
    get parts() {
        return this.q.type === 'completion' ? this.q.text.split('_____') : [this.q.text];
    },
    get chosenText() {
        if (!this.sel || this.q.type !== 'completion') return '';
        return this.q.options.find((o) => o.id === this.sel.option)?.text ?? '';
    },
    get recoCats() {
        return [...new Set(this.cards.map((c) => c.category))];
    },
    recoLabel(cat) {
        return (
            { 'verb-forms': 'Bentuk verb', 'connector-time': 'Time', 'connector-cause': 'Cause', 'connector-noun': 'Noun clause', 'connector-adjective': 'Adjective clause' }[cat] ?? cat
        );
    },
    get recoCards() {
        return this.cards.filter((c) => c.category === this.recoCat);
    },
    get score() {
        return this.results.filter((r) => r.correct).length;
    },
    get wrong() {
        return this.results.filter((r) => !r.correct);
    },
    get hintText() {
        return this.q.hints.slice(0, this.hints);
    },
    get canXray() {
        return !!this.q.xray && (this.q.type === 'error-find' || this.answered);
    },
    get xrayCfg() {
        return this.q.xray;
    },
    get verifiedBadge() {
        return !this.q.verified;
    },

    pickOption(id) {
        if (this.answered) return;
        this.sel = { option: id };
    },
    pickSegment(index) {
        if (this.answered) return;
        this.sel = { kind: 'segment', index };
    },
    pickKind(kind, what = null) {
        if (this.answered) return;
        this.sel = kind === 'missing' ? { kind, what } : { kind };
    },
    isSel(test) {
        if (!this.sel) return false;
        return Object.entries(test).every(([k, v]) => this.sel[k] === v);
    },
    keyIs(test) {
        return Object.entries(test).every(([k, v]) => this.q.answer[k] === v);
    },

    revealHint() {
        if (this.answered || this.hints >= 3) return;
        this.hints += 1;
        this.showReco = this.showReco || this.hints >= 1;
    },

    onXrayChecked() {
        if (!this.answered) this.hints = Math.max(this.hints, 3);
    },

    async submit() {
        if (!this.sel || this.answered) return;
        const q = this.q;
        const ms = Date.now() - this.started;
        this.correct = sameAnswer(q, this.sel);
        this.answered = true;
        if (!this.correct) this.shakeKey += 1;
        this.results.push({ code: q.code, text: q.text, correct: this.correct, hints: this.hints, q, given: { ...this.sel } });

        const payload = { code: q.code, answer: this.sel, hints_used: this.hints, time_ms: Math.min(ms, 3600000), mode: this.mode };
        if (window.TOGO.auth) {
            this.saving = true;
            this.saveError = '';
            const { ok, data } = await postJson(window.TOGO.urls.attempt, payload);
            this.saving = false;
            if (ok) {
                this.xpEarned += data.xp;
                window.dispatchEvent(new CustomEvent('togo:stats', { detail: { xp: data.xp_total, streak: data.streak } }));
            } else {
                this.saveError = data.message || 'Jawaban belum tersimpan.';
            }
        } else {
            guest.add({ code: q.code, answer: this.sel, hints_used: this.hints, time_ms: payload.time_ms, at: new Date().toISOString(), tp: q.testingPoint, correct: this.correct });
        }
    },

    next() {
        if (!this.answered) return;
        if (this.i + 1 >= this.total) {
            this.finished = true;
            window.scrollTo({ top: 0 });
            return;
        }
        this.i += 1;
        this.resetQuestion();
    },

    resetQuestion() {
        this.sel = null;
        this.answered = false;
        this.correct = false;
        this.hints = 0;
        this.showXray = false;
        this.showReco = false;
        this.saveError = '';
        this.started = Date.now();
        window.scrollTo({ top: 0 });
    },

    retryWrong() {
        const codes = new Set(this.wrong.map((r) => r.code));
        this.queue = this.all.filter((q) => codes.has(q.code));
        this.results = [];
        this.i = 0;
        this.finished = false;
        this.resetQuestion();
    },

    // --- tutor AI ---
    askHint() {
        this.$store.tutor.ask({ action: 'hint', question_code: this.q.code }, 'Beri hint tanpa bocor');
    },
    askExplain() {
        this.$store.tutor.ask({ action: 'explain', question_code: this.q.code, user_answer: describe(this.q, this.sel) }, 'Jelaskan kenapa salah');
    },
    askSimilar() {
        this.$store.tutor.ask({ action: 'similar', question_code: this.q.code }, 'Buat 3 soal serupa');
    },
    answerText(r) {
        return describe(r.q, r.given);
    },
    keyText(q) {
        if (q.type === 'completion') {
            const o = q.options.find((x) => x.id === q.answer.option);
            return `${o.id}. ${o.text}`;
        }
        const a = q.answer;
        if (a.kind === 'segment') return `ganti "${q.segments[a.index]}": ${a.fix}`;
        if (a.kind === 'missing') return a.fix;
        return 'kalimat sudah benar';
    },
});
