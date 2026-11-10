import { postJson } from '../lib/http';

export default () => ({
    open: false,
    loading: false,
    msgs: [],
    input: '',
    aiReady: window.TOGO.aiReady,

    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },

    async ask(payload, label) {
        this.show();
        if (!window.TOGO.auth) {
            this.msgs.push({ role: 'err', text: 'Masuk dulu untuk memakai tutor AI.' });
            return;
        }
        if (this.loading) return;
        this.msgs.push({ role: 'user', text: label });
        await this.send(payload);
    },

    async sendChat() {
        const text = this.input.trim();
        if (!text || this.loading) return;
        if (!window.TOGO.auth) {
            this.msgs.push({ role: 'err', text: 'Masuk dulu untuk memakai tutor AI.' });
            return;
        }
        const history = this.msgs.filter((m) => m.role === 'user' || m.role === 'model').slice(-6).map((m) => ({ role: m.role, text: m.text.slice(0, 2000) }));
        this.msgs.push({ role: 'user', text });
        this.input = '';
        await this.send({ action: 'chat', message: text, history });
    },

    async send(payload) {
        this.loading = true;
        setTimeout(() => this.scroll(), 0);
        const { ok, data } = await postJson(window.TOGO.urls.tutor, payload);
        this.loading = false;
        if (ok) this.msgs.push({ role: 'model', text: data.reply });
        else {
            if (data.setup) this.aiReady = false;
            this.msgs.push({ role: 'err', text: data.message || 'Tutor tidak bisa menjawab sekarang.' });
        }
        setTimeout(() => this.scroll(), 0);
    },

    scroll() {
        const el = document.querySelector('.sheet-body');
        if (el) el.scrollTop = el.scrollHeight;
    },
});
