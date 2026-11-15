const RULES = {
    'problem-of-subject':
        'Setiap klausa mandiri wajib memiliki tepat satu Subject nyata. Frasa berawalan preposisi (seperti on, in, at, with, of) dan keterangan waktu hanyalah penjelas, bukan Subject kalimat.',
    'problem-of-verb':
        'Setiap klausa wajib memiliki tepat satu Verb sejati (membawa tense lampau/sekarang atau didampingi be/have/modal). Bentuk -ing tanpa to be dan bentuk -ed tanpa to be/have hanya penjelas (participle), bukan verb utama.',
    'connector':
        'Rumus klausa: Jumlah connector = (Jumlah klausa) - 1. Dua klausa mandiri wajib dihubungkan oleh 1 connector. Tanpa connector, dua klausa tidak boleh digabungkan langsung (hindari comma splice / run-on).',
    'inversion':
        'Inversi membalik susunan menjadi [Auxiliary / Verb bantu + Subject]. Pola ini wajib terjadi saat kalimat diawali kata keterangan negatif (never, rarely, hardly, scarcely, only), frasa tempat tertentu, atau penghilangan if pada conditional clause.',
    'parallel-structure':
        'Elemen kalimat yang dihubungkan oleh koordinatif (and, but, or) maupun paired conjunction (both...and, either...or, neither...nor, not only...but also) wajib memiliki kelas kata dan struktur gramatikal yang setara/paralel.',
    'passive-article-usage':
        'Passive voice wajib berpola [be + Verb-3/Past Participle]. Perhatikan juga ketepatan artikel (a/an/the) serta pemakaian kata yang mirip seperti like vs alike, another vs other.',
    'restatement':
        'Kunci utama Listening Part A: pusatkan fokus pada pembicara kedua. Jawaban yang tepat hampir selalu berupa parafrase makna (restatement) dengan kata-kata berbeda. Hindari pilihan yang bunyinya terdengar mirip.',
    'who-what-where':
        'Tarik kesimpulan tentang siapa pembicara (who), tindakan selanjutnya (what), atau lokasi percakapan (where) dari petunjuk profesi dan konteks situasi.',
    'functional-expression':
        'Perhatikan ungkapan fungsional seperti persetujuan (You can say that again, So do I), keraguan/kejutan, dan saran (Why not..., Let\'s...). Pahami makna idiomatik di baliknya.',
    'idiom':
        'Idiom bahasa Inggris memiliki makna kiasan utuh yang tidak bisa diterjemahkan harfiah kata per kata. Pahami pesan konteks percakapan secara menyeluruh.',
    'appendix-idiom':
        'Idiom memiliki makna kiasan baku yang tidak bisa diartikan kata demi kata. Pahami pesan konteks kalimat secara menyeluruh.',
    'listening-main-idea':
        'Ide pokok (main idea/topic) mewakili inti keseluruhan percakapan/ceramah. Perhatikan 1-2 kalimat awal dan hindari opsi yang terlalu sempit atau terlalu luas.',
    'reading-main-idea':
        'Gagasan utama (main idea) umumnya berada di awal paragraf (topic sentence). Pilih jawaban yang mencakup keseluruhan bacaan, bukan hanya detail satu paragraf.',
    'listening-detail':
        'Untuk soal detail faktual, dengarkan kata kunci pertanyaan. Jawaban benar biasanya berupa fakta langsung yang dinyatakan dalam percakapan.',
    'reading-stated':
        'Untuk soal stated detail atau NOT/EXCEPT, temukan kata kunci soal di teks, baca kalimat sebelum dan sesudahnya. Jawaban benar adalah fakta yang dinyatakan langsung (sering kali diparafrase).',
    'listening-inference':
        'Soal kesimpulan (inference/imply) meminta Anda menyimpulkan informasi tersirat yang logis berdasarkan petunjuk percakapan, bukan yang diucapkan secara gamblang.',
    'reading-inference':
        'Soal kesimpulan (inference/imply) menguji kemampuan menyimpulkan makna tersirat yang logis berdasarkan bukti-bukti teks, bukan fakta yang tertulis eksplisit.',
    'reading-reference':
        'Kata ganti (pronoun: it, they, them) selalu merujuk pada kata benda (noun) terdekat yang disebutkan sebelumnya. Cocokkan jumlah (tunggal/jamak) dan konteks maknanya.',
    'reading-vocabulary':
        'Tentukan arti kosakata dari petunjuk konteks kalimat di sekitarnya (definisi, sinonim, antonim, atau contoh), bukan hanya dari hafalan kamus biasa.',
    'word-parts':
        'Identifikasi awalan (prefix), akar kata (root), dan akhiran (suffix) untuk memecah dan menebak arti kata-kata akademis secara logis.',
    'appendix-phrasal':
        'Phrasal verb adalah gabungan kata kerja + partikel/preposisi yang membentuk satu arti baru yang khas (misal: put off = tunda, give up = menyerah).',
    'appendix-passive':
        'Saat mengubah aktif ke pasif: subjek dan objek bertukar posisi, tenses asli dipertahankan dengan to be yang sesuai, lalu diikuti bentuk Past Participle (V3).',
};

const SECTION_FALLBACKS = {
    structure: 'Pastikan kalimat memiliki Subject dan Verb sejati yang lengkap, kesesuaian agreement, serta connector yang tepat antar-klausa.',
    listening: 'Fokus pada informasi utama dari pembicara dan cermati konteks serta kata kunci situasi percakapan.',
    reading: 'Gunakan teknik skimming dan scanning untuk menemukan kata kunci pertanyaan di dalam teks bacaan.',
    writing: 'Perhatikan kaidah struktur gramatikal baku, tenses, dan ketepatan pemilihan kata.',
};

export function getRuleSummary(q) {
    if (!q) return '';
    const tp = q.testingPoint || q.testing_point;
    if (tp && RULES[tp]) {
        return RULES[tp];
    }
    const sec = q.section;
    return SECTION_FALLBACKS[sec] || 'Pahami pola soal dan teliti komponen kalimat sebelum menentukan pilihan.';
}
