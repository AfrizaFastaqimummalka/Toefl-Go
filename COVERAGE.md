# Cakupan modul (Ade Johan 2018) dan status verifikasi

Terakhir diperbarui setelah verifikasi menyeluruh (324 soal). Nomor halaman memakai nomor cetak di footer buku (PDF = cetak + 2).

| Bagian buku | Hal. cetak | Di app | Soal | Kunci |
|---|---|---|---|---|
| Structure: Subject & Verb, Lead-in 1, Review 1 | 47-51 | ada | 20 | terverifikasi (blueprint [OK]) |
| Diagnostic completion 1-15 | 39-40 | ada | 15 | terverifikasi |
| Connector: Lead-in 2, Review 2 | 53-54 | ada | 20 | Lead-in dan Review 1-5 terverifikasi, Review 6-10 belum |
| Inversion/agreement: Lead-in 3, Review 3 | 57-58 | ada | 20 | terverifikasi |
| Parallel: Lead-in 4, Review 4 | 59-60 | ada | 20 | terverifikasi kecuali Review 4 no. 8 |
| Passive/Article/Usage: Lead-in 5, Review 5 | 62-63 | ada | 20 | Lead-in dan Review 1-3 terverifikasi, Review 4-10 belum |
| Diagnostic error ID 16-40 | 42-44 | ada | 25 | belum (diturunkan dari analisis dan gambar) |
| Simulasi Structure 25 menit | 39-44 | ada | 40 | mengikuti kunci di atas |
| Listening Part A: contoh, Lead-in 1-4 | 13-25 | ada | 19 | terverifikasi (blueprint) |
| Listening Part A: Review 1-4 | 15-26 | ada | 40 | belum, script rekonstruksi |
| Listening Part B/C: Lead-in 5-6 | 29-33 | ada | 8 | terverifikasi |
| Listening Part B/C: Review 5-6 | 30-36 | ada | 30 | belum, kunci dari script buku |
| Simulasi Listening 35 menit | 15-36 | ada | 55 | campuran |
| Diagnostic Listening 50 soal | 8-13 | belum | 0 | buku hanya berisi opsi tanpa audio |
| Reading: Lead-in 5 (gajah, 8 soal vocabulary) | 82 | ada | 8 | belum (kunci dari passage) |
| Reading: Review 1 (Bigfoot, epinefrin, krisis bank 1933) | 83-85 | ada | 18 | belum |
| Reading: Review 2 (karbohidrat, termasuk sisipan kalimat) | 86-88 | ada | 12 | belum |
| Reading: Review 2 (passage MLK, 12 soal) | 86-87 | belum | 0 | rujukan baris di scan ambigu |
| Reading: Diagnostic 50 soal | 64-71 | belum | 0 | - |
| Reading: word parts | 81 | ada | 12 soal + 12 kartu | terverifikasi (tabel buku) |
| Simulasi Reading 55 menit | - | ada | 38 | belum |
| TWE: strategi, skala, checklist, editor 30 menit, koreksi AI | 89-92 | ada, 4 topik dari buku | - | - |
| Appendix 3 two/three-part verb, 4 idiom, 5 active-passive | 94-96 | ada | 37 soal + kartu | belum (kalimat dari buku, opsi disusun) |
| Appendix 2 irregular verbs | 93 | kartu 48 verb (disusun ulang, tabel scan rusak) | - | - |

## Soal ambigu (cek dengan dosen kalau ragu)
- `str-diag-e24`: "its disease" diganti "their" (kunci dari analisis, bagian D).
- `str-diag-e30`: "designed by" menjadi "was designed by".
- `str-diag-e31`: batas garis bawah no. 31 sulit dibaca di scan; kunci B.
- `str-rev2-07`, `str-rev2-09`: lebih dari satu bentuk bisa benar, tapi hanya satu yang ada di opsi.

## Listening: apa yang asli dan apa yang rekonstruksi
- Script Lead-in dan contoh Part A berasal dari buku (ditulis ulang dengan kata sendiri bila perlu).
- Review 1-4 Part A: buku hanya memuat opsi. Script ditulis untuk cocok dengan opsi dan dilabeli "script rekonstruksi".
- Part B/C: script ditulis ulang ringkas dari script buku. Detail yang tidak ada di buku sudah dibuang (hasil pemeriksaan kata per kata terhadap OCR).

## Pemeriksaan otomatis yang dilakukan
- Semua soal: 4 opsi, kunci ada di opsi, 3 hint, penjelasan terisi, kode unik.
- Markup X-ray cocok dengan kalimat soal dan vonisnya konsisten dengan kunci.
- Segmen kalimat error ID sama dengan teks soal, kunci tidak jatuh di segmen non-tap.
- Nomor halaman sumber dicocokkan ke OCR buku dan 49 yang meleset sudah dikoreksi.
- Kunci Structure dan Listening yang ada di blueprint dicocokkan satu per satu dan semuanya sama.

## Catatan Reading, Writing, Appendix
- Passage Reading ditulis ulang dengan kata sendiri dari passage buku. Nomor baris mengikuti buku, jadi rujukan 'line N' tetap benar. Soal dan opsi mengikuti buku.
- Soal tipe reference berupa pilihan ganda (belum interaksi tap kata ganti lalu tap rujukan).
- Topik esai adalah 4 topik di halaman 92 buku. Contoh esai skor 6 tidak dipakai. Skala 1-6 di halaman Writing adalah ringkasan umum.
- Koreksi esai memakai Gemini dengan skala TWE 1-6 dan menyimpan hasil di tabel `essays`.

## Pemeriksaan tambahan (putaran terakhir)
- Kompilasi Blade: 18 template dikompilasi dengan BladeCompiler Laravel 13 asli dan hasil PHP-nya lolos `php -l`.
- `Question::toClient()` dan `ProgressService::isCorrect()` dijalankan dengan kelas Eloquent asli (tanpa database): meta (script, passage, plain) ikut terkirim ke client dan penilaian benar/salah sesuai.
- Teks Lead-in 2 dan 4 Structure, Lead-in 5 Structure, serta Lead-in 1, 3, 4 Listening dicocokkan ulang ke gambar halaman: tidak ada selisih.
- Script Listening Review 5 percakapan 3 dipertegas supaya soal 'How is sulfuric acid formed?' punya satu jawaban yang jelas.
