// Penyimpanan tamu di localStorage. Digabung ke akun saat login.
const KEY = 'togo.guest.v1';

function read() {
    try {
        const v = JSON.parse(localStorage.getItem(KEY) || '{}');
        return Array.isArray(v.attempts) ? v : { attempts: [] };
    } catch {
        return { attempts: [] };
    }
}

function write(data) {
    try {
        localStorage.setItem(KEY, JSON.stringify(data));
    } catch {
        /* penyimpanan penuh atau diblokir: abaikan */
    }
}

export const guest = {
    add(entry) {
        const d = read();
        d.attempts.push(entry);
        if (d.attempts.length > 500) d.attempts = d.attempts.slice(-500);
        write(d);
    },
    all() {
        return read().attempts;
    },
    clear() {
        try {
            localStorage.removeItem(KEY);
        } catch {
            /* abaikan */
        }
    },
};
