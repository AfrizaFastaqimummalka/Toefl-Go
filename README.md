# toefl-go

Website belajar TOEFL PBT (modul Ade Johan 2018). Laravel 13 + Blade + Alpine.js + Tailwind 4, database NeonDB (PostgreSQL). Status: **Fase 1** (Structure: Subject & Verb, Diagnostic completion).

## Menjalankan

```bash
composer install
cp .env.example .env        # isi DB_* dari Neon, GEMINI_API_KEY opsional
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev                 # atau: npm run build
php artisan serve
```

Buka http://localhost:8000. Uji lokal tanpa Neon: di `.env` pakai `DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`, lalu `touch database/database.sqlite`.

Neon: pakai host pooled (`-pooler`) di `DB_HOST`, `DB_SSLMODE=require`. Kalau migrasi bermasalah lewat pooler, jalankan sekali dengan host direct.

## AI tutor

Isi `GEMINI_API_KEY` di `.env` (key dari aistudio.google.com/apikey), lalu `php artisan config:clear`. Model lewat `GEMINI_MODEL`. System prompt ada di `resources/prompts/tutor_system.txt`. Tanpa key, tombol AI menampilkan petunjuk setup dan app tetap jalan.

## Mengedit materi dan soal

Sumber kebenaran: `database/seeders/data/` (`chapters.json`, `vocab.json`, `questions/*.json`). Edit lalu `php artisan db:seed` (aman diulang). `php artisan test` memeriksa bentuk JSON.

Markup X-ray di field `xray.marked`: `P{frasa depan} M{penerang} S{subject} V{verb sejati} C{connector} R{who/which/what: connector sekaligus subject}`.

Lihat `DECISIONS.md` untuk keputusan teknis.
