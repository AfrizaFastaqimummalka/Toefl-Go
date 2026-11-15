import { postJson } from '../lib/http';
import { guest } from '../lib/guest';
import { getRuleSummary } from '../lib/rules';

function same(q, a) {
    const k = q.answer;
    if (q.type === 'completion') return a.option === k.option;
    if (a.kind !== k.kind) return false;
    if (k.kind === 'segment') return a.index === k.index;
    if (k.kind === 'missing') return a.what === k.what;
    return true;
}

function keyText(q) {
    if (q.type === 'completion') {
        const o = q.options.find((x) => x.id === q.answer.option);
        return o ? `${o.id}. ${o.text}` : '';
    }
    const a = q.answer;
    if (a.kind === 'segment') return `Ganti "${q.segments[a.index]}" ➔ ${a.fix}`;
    if (a.kind === 'missing') return `Perlu ${a.what}: ${a.fix}`;
    return 'Kalimat sudah benar';
}

function givenText(q, a) {
    if (!a) return '(tidak dijawab)';
    if (q.type === 'completion') {
        const o = q.options.find((x) => x.id === a.option);
        return o ? `${o.id}. ${o.text}` : '';
    }
    if (a.kind === 'segment') return `Bagian "${q.segments[a.index]}"`;
    if (a.kind === 'missing') return `Kekurangan ${a.what}`;
    return 'Sudah benar';
}

export default (config) => ({
    qs: config.questions,
    title: config.title,
    intro: config.intro,
    timerLabel: config.timerLabel,
    phase: 'intro', // intro | run | done
    i: 0,
    answers: {},
    timerOn: true,
    left: config.seconds,
    total_seconds: config.seconds,
    tick: null,
    startedAt: 0,
    results: null,
    saving: false,
    saveError: '',

    get q() {
        return this.qs[this.i];
    },
    get parts() {
        return this.q.type === 'completion' ? this.q.text.split('_____') : [this.q.text];
    },
    get chosenText() {
        const a = this.answers[this.q.code];
        if (!a || this.q.type !== 'completion') return '';
        return this.q.options.find((o) => o.id === a.option)?.text ?? '';
    },
    get answeredCount() {
        return Object.keys(this.answers).length;
    },
    get clock() {
        const m = Math.floor(this.left / 60);
        const s = this.left % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    },
    get lowTime() {
        return this.timerOn && this.left <= 120;
    },

    start() {
        this.phase = 'run';
        this.startedAt = Date.now();
        window.scrollTo({ top: 0 });
        this.tick = setInterval(() => {
            if (!this.timerOn) return;
            this.left -= 1;
            if (this.left <= 0) this.finish(true);
        }, 1000);
    },

    isSel(test) {
        const a = this.answers[this.q.code];
        return !!a && Object.entries(test).every(([k, v]) => a[k] === v);
    },
    pickOption(id) {
        this.answers[this.q.code] = { option: id };
    },
    pickSegment(idx) {
        if ((this.q.plain || []).includes(idx)) return;
        this.answers[this.q.code] = { kind: 'segment', index: idx };
    },
    pickKind(kind, what = null) {
        this.answers[this.q.code] = kind === 'missing' ? { kind, what } : { kind };
    },
    go(d) {
        this.i = Math.min(this.qs.length - 1, Math.max(0, this.i + d));
        window.scrollTo({ top: 0 });
    },
    jump(n) {
        this.i = n;
        window.scrollTo({ top: 0 });
    },
    dotClass(n) {
        const code = this.qs[n].code;
        return [n === this.i ? 'is-current' : '', this.answers[code] ? 'is-done' : ''].join(' ');
    },

    async finish(auto = false) {
        if (!auto && this.answeredCount < this.qs.length) {
            const left = this.qs.length - this.answeredCount;
            if (!window.confirm(`${left} soal belum dijawab. Selesaikan sekarang?`)) return;
        }
        clearInterval(this.tick);
        const used = Math.round((Date.now() - this.startedAt) / 1000);

        const rows = this.qs.map((q) => {
            const given = this.answers[q.code] || null;
            return { q, given, correct: given ? same(q, given) : false };
        });
        const byTp = {};
        rows.forEach((r) => {
            const t = (byTp[r.q.testingPoint] ??= { right: 0, total: 0 });
            t.total += 1;
            if (r.correct) t.right += 1;
        });
        this.results = {
            score: rows.filter((r) => r.correct).length,
            total: rows.length,
            answered: this.answeredCount,
            seconds: used,
            auto,
            byTp: Object.entries(byTp).map(([k, v]) => ({ key: k, label: k.replaceAll('-', ' '), ...v, pct: Math.round((v.right / v.total) * 100) })),
            wrong: rows.filter((r) => !r.correct).map((r) => ({
                code: r.q.code,
                text: r.q.text,
                given: givenText(r.q, r.given),
                key: keyText(r.q),
                explanation: r.q.explanation,
                rule: getRuleSummary(r.q),
                verified: r.q.verified,
                script: r.q.script || null,
                reconstructed: r.q.scriptSource === 'reconstructed',
            })),
        };
        this.phase = 'done';
        window.scrollTo({ top: 0 });

        const answered = rows.filter((r) => r.given);
        if (window.TOGO.auth) {
            this.saving = true;
            const attempts = answered.map((r) => ({ code: r.q.code, answer: r.given, hints_used: 0, time_ms: null, mode: 'test', at: new Date().toISOString() }));
            const { ok, data } = await postJson(window.TOGO.urls.sync, { attempts });
            this.saving = false;
            if (ok) window.dispatchEvent(new CustomEvent('togo:stats', { detail: { xp: data.xp_total, streak: data.streak } }));
            else this.saveError = data.message || 'Hasil belum tersimpan.';
        } else {
            answered.forEach((r) => guest.add({ code: r.q.code, answer: r.given, hints_used: 0, at: new Date().toISOString(), tp: r.q.testingPoint, correct: r.correct }));
        }
    },
});
