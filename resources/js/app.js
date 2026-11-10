import '../css/app.css';
import Alpine from 'alpinejs';
import practice from './components/practice';
import xray from './components/xray';
import tutor from './components/tutor';
import cards from './components/cards';
import guestProgress from './components/guestProgress';
import { postJson } from './lib/http';
import { guest } from './lib/guest';

window.Alpine = Alpine;

Alpine.data('practice', practice);
Alpine.data('xray', xray);
Alpine.data('cards', cards);
Alpine.data('guestProgress', guestProgress);
Alpine.data('tutorSheet', () => ({}));
Alpine.store('tutor', tutor());

Alpine.data('shell', () => ({
    xp: window.TOGO.xp,
    streak: window.TOGO.streak,
    toast: '',
    async init() {
        window.addEventListener('togo:stats', (e) => {
            this.xp = e.detail.xp;
            this.streak = e.detail.streak;
        });
        // Gabungkan progres tamu ke akun setelah login
        if (window.TOGO.auth) {
            const attempts = guest.all();
            if (attempts.length) {
                const items = attempts.slice(0, 500).map((a) => ({ code: a.code, answer: a.answer, hints_used: a.hints_used, time_ms: a.time_ms ?? null, at: a.at }));
                const { ok, data } = await postJson(window.TOGO.urls.sync, { attempts: items });
                if (ok) {
                    guest.clear();
                    this.xp = data.xp_total;
                    this.streak = data.streak;
                    this.toast = `${data.merged} jawaban dari mode tamu digabung ke akunmu.`;
                    setTimeout(() => (this.toast = ''), 4000);
                }
            }
        }
    },
}));

Alpine.start();
