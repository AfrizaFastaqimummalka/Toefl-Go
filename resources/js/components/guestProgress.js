import { guest } from '../lib/guest';

export default (labels) => ({
    rows: [],
    total: 0,
    init() {
        const att = guest.all();
        this.total = att.length;
        this.rows = Object.entries(labels).map(([key, label]) => {
            const mine = att.filter((a) => a.tp === key);
            const right = mine.filter((a) => a.correct).length;
            return { key, label, attempted: mine.length, pct: mine.length ? Math.round((right / mine.length) * 100) : null };
        });
    },
});
