<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TutorRequest;
use App\Models\AiRequest;
use App\Models\Essay;
use App\Models\Question;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class TutorController extends Controller
{
    public function __invoke(TutorRequest $request, GeminiService $gemini): JsonResponse
    {
        if (! $gemini->configured()) {
            return response()->json([
                'setup' => true,
                'message' => 'AI tutor belum aktif. Isi GEMINI_API_KEY di file .env (ambil di aistudio.google.com/apikey), lalu jalankan php artisan config:clear.',
            ], 503);
        }

        $data = $request->validated();
        $question = ! empty($data['question_code'])
            ? Question::where('code', $data['question_code'])->first()
            : null;

        $turns = collect($data['history'] ?? [])->map(fn ($h) => ['role' => $h['role'], 'text' => $h['text']])->all();
        $turns[] = ['role' => 'user', 'text' => $this->buildPrompt($data, $question)];

        $started = microtime(true);
        $status = 200;

        try {
            $reply = $gemini->generate($turns);

            if ($data['action'] === 'essay') {
                preg_match('/(?:skor|score)\D{0,6}([1-6])\s*\/\s*6/i', $reply, $m);
                Essay::create([
                    'user_id' => $request->user()->id,
                    'topic' => mb_substr($data['topic'] ?? '-', 0, 250),
                    'body' => $data['essay'],
                    'word_count' => str_word_count($data['essay']),
                    'score' => isset($m[1]) ? (int) $m[1] : null,
                    'feedback' => ['text' => $reply],
                ]);
            }

            return response()->json(['reply' => $reply]);
        } catch (RuntimeException $e) {
            $status = 502;

            return response()->json(['message' => $e->getMessage()], 502);
        } finally {
            AiRequest::create([
                'user_id' => $request->user()->id,
                'action' => $data['action'],
                'question_code' => $question?->code,
                'status' => $status,
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
            ]);
        }
    }

    private function buildPrompt(array $data, ?Question $q): string
    {
        $ctx = $q ? $this->context($q, $data['action'] !== 'hint') : '';
        $answer = trim($data['user_answer'] ?? '');

        return match ($data['action']) {
            'explain' => "{$ctx}\nJawaban siswa: ".($answer ?: '(tidak dikirim)')."\nJelaskan kenapa jawaban siswa benar atau salah dengan metode buku. Singkat.",
            'hint' => "{$ctx}\nSiswa BELUM menjawab. Beri satu hint singkat yang mengarahkan cara menganalisis, tanpa menyebut jawaban atau opsi yang benar.",
            'essay' => "Topik esai: ".trim($data['topic'] ?? '-')."\n\nEsai siswa:\n".trim($data['essay'] ?? '')."\n\nNilai esai ini dengan skala TWE 1-6 (organisasi, dukungan gagasan, struktur kalimat, grammar). Mulai jawaban dengan satu baris persis 'Skor: N/6'. Lalu sebut 2 kekuatan dan 2 perbaikan prioritas dengan contoh singkat dari esai. Maksimal 180 kata.",
            'similar' => "{$ctx}\nBuat 3 soal serupa (testing point sama), format pilihan ganda A-D, soal baru dengan kalimat berbeda. Tulis kunci di bagian akhir setelah garis '---'.",
            default => ($ctx ? "{$ctx}\n" : '').'Pertanyaan siswa: '.trim($data['message'] ?? ''),
        };
    }

    private function context(Question $q, bool $withScript = false): string
    {
        $lines = ["Konteks soal (id {$q->code}, testing point {$q->testing_point}, verified=".($q->verified ? 'true' : 'false').'):', $q->text];

        if ($q->type === 'completion') {
            foreach ($q->options as $o) {
                $lines[] = "{$o['id']}. {$o['text']}";
            }
            $key = collect($q->options)->firstWhere('id', $q->answer['option']);
            $lines[] = "Kunci (RAHASIA untuk hint): {$q->answer['option']}. ".($key['text'] ?? '');
        } else {
            foreach ($q->segments as $i => $s) {
                $lines[] = ($i + 1).". {$s}";
            }
            $k = $q->answer;
            $lines[] = 'Kunci (RAHASIA untuk hint): '.match ($k['kind']) {
                'segment' => 'bagian nomor '.($k['index'] + 1).', perbaikan: '.($k['fix'] ?? '-'),
                'missing' => 'kalimat kekurangan '.($k['what'] ?? '-').'. '.($k['fix'] ?? ''),
                default => 'kalimat sudah benar',
            };
        }
        if ($withScript && ! empty($q->meta['script'])) {
            $names = ['M' => 'Pria', 'W' => 'Wanita', 'N' => 'Narator'];
            $source = ($q->meta['scriptSource'] ?? 'book') === 'reconstructed' ? 'rekonstruksi, bukan dari buku' : 'dari buku';
            $lines[] = "Transkrip audio ({$source}):";
            foreach ($q->meta['script'] as [$who, $say]) {
                $lines[] = ($names[$who] ?? $who).': '.$say;
            }
        }
        if (! empty($q->meta['passage'])) {
            $lines[] = 'Passage:';
            foreach ($q->meta['passage']['lines'] as $i => $l) {
                $lines[] = ($i + 1).'  '.$l;
            }
        }
        $lines[] = 'Penjelasan buku/analisis: '.$q->explanation;

        return implode("\n", $lines);
    }
}
