import { parseMarked, expectedTag, verdictFor } from '../lib/xray';

const TOOLS = [
    { k: 'P', label: 'Frasa depan', short: 'depan' },
    { k: 'M', label: 'Penerang', short: 'pen.' },
    { k: 'S', label: 'Subject', short: 'S' },
    { k: 'V', label: 'Verb sejati', short: 'V' },
    { k: 'C', label: 'Connector', short: 'C' },
];

export default (cfg) => ({
    tools: TOOLS,
    mode: cfg.mode ?? 'practice', // practice | static
    tokens: [],
    counts: {},
    fixed: null,
    verdict: null,
    note: cfg.note ?? null,
    implicit: cfg.implicitConnectors ?? 0,
    user: {},
    tool: 'P',
    checked: false,
    revealed: false,
    showFixed: false,
    score: null,

    init() {
        const p = parseMarked(cfg.marked);
        this.tokens = p.tokens;
        this.counts = p.counts;
        this.verdict = verdictFor(p.counts, this.implicit);
        if (cfg.fixedMarked) this.fixed = parseMarked(cfg.fixedMarked);
        if (this.mode === 'static') this.revealed = true;
    },

    get shown() {
        return this.checked || this.revealed;
    },

    tap(i) {
        if (this.mode === 'static' || this.shown) return;
        if (this.user[i] === this.tool) delete this.user[i];
        else this.user[i] = this.tool;
    },

    tagOf(t) {
        return this.shown ? expectedTag(t) : this.user[t.i] || '';
    },

    miss(t) {
        return this.checked && (this.user[t.i] || '') !== expectedTag(t);
    },

    label(t) {
        const tag = this.tagOf(t);
        if (!tag) return '';
        if (this.shown && t.tag === 'R') return 'C+S';
        return { P: 'depan', M: 'pen', S: 'S', V: 'V', C: 'C' }[tag];
    },

    aria(t) {
        const l = this.label(t);
        return l ? `${t.w}, ditandai ${l}` : t.w;
    },

    check() {
        let right = 0;
        let total = 0;
        for (const t of this.tokens) {
            const exp = expectedTag(t);
            const got = this.user[t.i] || '';
            if (exp || got) {
                total += 1;
                if (exp === got) right += 1;
            }
        }
        this.score = { right, total };
        this.checked = true;
        this.$dispatch('xray-checked');
    },

    reveal() {
        this.revealed = true;
        this.$dispatch('xray-checked');
    },

    reset() {
        this.user = {};
        this.checked = false;
        this.revealed = false;
        this.score = null;
        this.tool = 'P';
    },

    nextTool() {
        const i = TOOLS.findIndex((t) => t.k === this.tool);
        this.tool = TOOLS[Math.min(i + 1, TOOLS.length - 1)].k;
    },
});
