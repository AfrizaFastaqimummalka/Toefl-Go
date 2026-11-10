# Keputusan teknis

- Laravel 13 (stabil terbaru saat dibangun), PHP 8.3+. Prompt menyebut 8.2+, tetapi Laravel 13 mensyaratkan 8.3.
- Tanpa Livewire dan tanpa Breeze: sandbox pembangun tidak bisa menjangkau Packagist, jadi kode tidak bisa diuji bersama paket tersebut. UI memakai Blade + Alpine.js 3 (di-bundle lewat Vite) + fetch ke endpoint JSON, auth ditulis sendiri (register, login, logout dengan Form Request dan throttle). Hasilnya lebih sedikit dependensi. Livewire/Breeze bisa ditambah nanti tanpa mengubah model dan database.
- Endpoint JSON (`/api/attempts`, `/api/sync`, `/api/tutor`) dideklarasikan di `routes/web.php` agar memakai sesi dan CSRF yang sama dengan halaman; tidak ada `routes/api.php` dan tidak ada token.
- Session, cache, dan queue memakai `file`/`sync`, bukan `database`, supaya tiap request tidak menambah query ke Neon.
- Field `xray` pada soal berbentuk markup string (`P{..} M{..} S{..} V{..} C{..} R{..}`), bukan indeks segmen. Satu kalimat bisa dianotasi per kata dan gampang diedit di JSON. R = relative/noun connector yang sekaligus subject.
- Jawaban `error-find` punya tiga bentuk: `segment` (bagian salah), `missing` (subject/verb hilang), `correct` (kalimat sudah benar).
- Kunci dicek ulang di server (`ProgressService::isCorrect`); client hanya memakai kunci untuk umpan balik instan.
- Memakai hint 3 atau menekan "Cek" di X-ray sebelum menjawab dihitung sebagai hint lapis 3 (XP berkurang).
- Gemini: default `gemini-3.8-flash` (GA per dokumentasi Google, Sept 2026). Parameter `temperature/top_p/top_k` tidak dikirim karena sudah dihapus untuk seri 3.x; `thinkingLevel` diset `low` (`minimal` tidak didukung 3.8).
- Soal Diagnostic completion no. 10 dan seluruh kunci diturunkan dari analisis, bukan dari buku (buku tanpa kunci). Semua soal Fase 1 dicocokkan ke gambar halaman PDF dan kunci [OK] blueprint, jadi `verified: true`. Soal [CEK] akan `verified: false` di fase berikutnya.
- `node_modules` tidak ikut paket; jalankan `npm install`.
