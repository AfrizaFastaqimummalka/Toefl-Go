<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\GeminiService;

class PageController extends Controller
{
    public function test()
    {
        return view('pages.test');
    }

    public function structureTest()
    {
        $questions = collect()
            ->concat($this->lessonQuestions('structure-diagnostic-completion', 'diagnostic-completion'))
            ->concat($this->lessonQuestions('structure-diagnostic-error', 'diagnostic-error'));

        return view('pages.test-structure', ['config' => [
            'title' => 'Simulasi Structure',
            'intro' => '15 soal lengkapi kalimat dan 25 soal cari bagian yang salah, total 40 soal. Waktu 25 menit seperti tes aslinya. Tidak ada hint dan jawaban benar baru ditunjukkan setelah selesai. Kamu bisa pindah antarsoal dan mengubah jawaban.',
            'timerLabel' => 'Pakai timer 25 menit',
            'seconds' => 25 * 60,
            'retryUrl' => route('test.structure'),
            'questions' => $questions->values(),
        ]]);
    }

    public function listeningTest()
    {
        $a = 'listening-part-a';
        $bc = 'listening-part-bc';
        $questions = collect()
            ->concat($this->lessonQuestions($a, 'review-1'))
            ->concat($this->lessonQuestions($a, 'review-2'))
            ->concat($this->lessonQuestions($a, 'review-3'))
            ->concat($this->lessonQuestions($bc, 'review-5', ['lst-r5-c1', 'lst-r5-c2']))
            ->concat($this->lessonQuestions($bc, 'review-6'));

        return view('pages.test-structure', ['config' => [
            'title' => 'Simulasi Listening',
            'intro' => 'Part A (30 soal percakapan pendek), Part B (10 soal dua percakapan), dan Part C (15 soal tiga talk). Total 55 soal dalam 35 menit. Audio memakai suara browser dan boleh diputar ulang. Script ditampilkan setelah selesai. Sebagian besar kunci Listening belum diverifikasi karena buku tidak menyertakan audio dan kunci.',
            'timerLabel' => 'Pakai timer 35 menit',
            'seconds' => 35 * 60,
            'retryUrl' => route('test.listening'),
            'questions' => $questions->values(),
        ]]);
    }

    public function readingTest()
    {
        $questions = collect()
            ->concat($this->lessonQuestions('reading-passages', 'lead-in-vocab'))
            ->concat($this->lessonQuestions('reading-passages', 'review-1'))
            ->concat($this->lessonQuestions('reading-passages', 'review-2'));

        return view('pages.test-structure', ['config' => [
            'title' => 'Simulasi Reading',
            'intro' => 'Lima passage dengan 38 soal dari Lead-in 5, Review 1, dan Review 2 modul (main idea, stated, NOT/EXCEPT, inference, reference, vocabulary, sisipan kalimat). Waktu 55 menit. Passage ditulis ulang dengan kata sendiri, soal dan opsi mengikuti buku. Jumlahnya lebih sedikit dari tes asli.',
            'timerLabel' => 'Pakai timer 55 menit',
            'seconds' => 55 * 60,
            'retryUrl' => route('test.reading'),
            'questions' => $questions->values(),
        ]]);
    }

    public function write(GeminiService $gemini)
    {
        return view('pages.write', [
            'aiReady' => $gemini->configured(),
            'topics' => [
                'Do you agree or disagree with the following statement? Children should be strongly pushed to achieve their maximum. Use specific reasons and details to support your answer.',
                'People are watching more and more television, and as a result television is having an increasing effect on society. Discuss the beneficial and harmful effects of television on society. Then indicate what you believe the role of television should be and why.',
                'A practical career is not always something that you love to do. Is it better to pursue a practical career, or to follow your dreams, even though they may be rather impractical? Discuss each position. Then indicate which you agree with and why.',
                'Do you agree or disagree with the following statement? It is better to marry for practical reasons than for love. Use specific reasons and details to support your answer.',
            ],
        ]);
    }

    /** @param  array<int, string>  $codePrefixes  kalau diisi, hanya soal dengan kode berawalan ini */
    private function lessonQuestions(string $chapterSlug, string $lessonSlug, array $codePrefixes = [])
    {
        $lesson = Lesson::where('slug', $lessonSlug)
            ->whereHas('chapter', fn ($q) => $q->where('slug', $chapterSlug))
            ->first();

        if (! $lesson) {
            return collect();
        }

        return $lesson->questions
            ->filter(fn ($q) => ! $codePrefixes || collect($codePrefixes)->contains(fn ($p) => str_starts_with($q->code, $p)))
            ->map->toClient()->values();
    }

    public function ai(GeminiService $gemini)
    {
        return view('pages.ai', ['aiReady' => $gemini->configured()]);
    }

    public function about()
    {
        return view('pages.about');
    }
}
