// Pemutar audio berbasis Web Speech API (speechSynthesis). Tidak butuh file audio.
const MALE = /david|mark|guy|daniel|alex|fred|james|george|ryan|male|man|thomas|arthur|oliver/i;
const FEMALE = /zira|samantha|karen|jenny|aria|victoria|susan|hazel|libby|sonia|female|woman|moira|tessa|serena|emma|ava/i;

export const ttsSupported = () => typeof window !== 'undefined' && 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;

function englishVoices() {
    const all = window.speechSynthesis.getVoices() || [];
    const en = all.filter((v) => /^en[-_]/i.test(v.lang));
    return en.length ? en : all;
}

export function pickVoices() {
    const list = englishVoices();
    const male = list.find((v) => MALE.test(v.name) && !FEMALE.test(v.name));
    const female = list.find((v) => FEMALE.test(v.name));
    const def = list.find((v) => /en[-_]US/i.test(v.lang)) || list[0] || null;
    return { M: male || null, W: female || null, N: def, single: !(male && female) };
}

function chunk(text) {
    // Pecah kalimat panjang supaya tidak terpotong di Chrome
    return text.match(/[^.!?]+[.!?]*\s*/g) || [text];
}

let token = 0;

export function speak(lines, rate, hooks = {}) {
    stopSpeaking();
    const my = ++token;
    const voices = pickVoices();
    const queue = [];
    for (const [spk, text] of lines) {
        for (const part of chunk(text)) {
            if (part.trim()) queue.push([spk, part.trim()]);
        }
    }
    let i = 0;
    const next = () => {
        if (my !== token) return;
        if (i >= queue.length) {
            hooks.onEnd?.();
            return;
        }
        const [spk, text] = queue[i++];
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-US';
        u.rate = rate;
        const v = voices[spk] || voices.N;
        if (v) u.voice = v;
        if (voices.single) u.pitch = spk === 'M' ? 0.8 : spk === 'W' ? 1.2 : 1;
        u.onend = next;
        u.onerror = () => {
            if (my === token) hooks.onEnd?.();
        };
        window.speechSynthesis.speak(u);
    };
    hooks.onStart?.();
    next();
}

export function stopSpeaking() {
    token += 1;
    if (ttsSupported()) window.speechSynthesis.cancel();
}
