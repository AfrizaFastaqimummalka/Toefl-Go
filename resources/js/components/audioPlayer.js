import { ttsSupported, speak, stopSpeaking } from '../lib/audio';

export default () => ({
    supported: ttsSupported(),
    playing: false,
    rate: 1,

    init() {
        if (this.supported) {
            // Daftar suara kadang baru tersedia setelah event ini
            window.speechSynthesis.onvoiceschanged = () => {};
            window.speechSynthesis.getVoices();
        }
    },

    play(lines) {
        if (!this.supported || !lines) return;
        speak(lines, this.rate, { onStart: () => (this.playing = true), onEnd: () => (this.playing = false) });
    },

    stop() {
        stopSpeaking();
        this.playing = false;
    },

    speakerLabel(s) {
        return { M: 'Pria', W: 'Wanita', N: 'Narator' }[s] ?? s;
    },
});
