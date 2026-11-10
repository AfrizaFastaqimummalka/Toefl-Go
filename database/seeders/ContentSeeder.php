<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\VocabCard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Membaca JSON di database/seeders/data/ (sumber kebenaran materi dan soal).
 * Aman dijalankan berulang: data dicocokkan lewat slug/code.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $dir = __DIR__.'/data';

        DB::transaction(function () use ($dir) {
            foreach ($this->json("$dir/chapters.json") as $c) {
                $chapter = Chapter::updateOrCreate(['slug' => $c['slug']], [
                    'section' => $c['section'],
                    'title' => $c['title'],
                    'subtitle' => $c['subtitle'] ?? null,
                    'position' => $c['position'],
                    'available' => $c['available'],
                    'phase' => $c['phase'],
                    'recommend' => $c['recommend'] ?? [],
                ]);

                foreach ($c['lessons'] ?? [] as $l) {
                    Lesson::updateOrCreate(['chapter_id' => $chapter->id, 'slug' => $l['slug']], [
                        'kind' => $l['kind'],
                        'title' => $l['title'],
                        'position' => $l['position'],
                        'content' => $l['content'] ?? null,
                    ]);
                }
            }

            foreach (glob("$dir/questions/*.json") as $file) {
                $set = $this->json($file);
                [$chapterSlug, $lessonSlug] = explode('/', $set['lesson']);
                $lesson = Lesson::whereHas('chapter', fn ($q) => $q->where('slug', $chapterSlug))
                    ->where('slug', $lessonSlug)->firstOrFail();

                foreach ($set['questions'] as $q) {
                    Question::updateOrCreate(['code' => $q['id']], [
                        'lesson_id' => $lesson->id,
                        'position' => $q['position'],
                        'section' => $q['section'],
                        'testing_point' => $q['testingPoint'],
                        'type' => $q['type'],
                        'text' => $q['text'],
                        'options' => $q['options'] ?? null,
                        'segments' => $q['segments'] ?? null,
                        'answer' => $q['answer'],
                        'explanation' => $q['explanation'],
                        'hints' => $q['hints'],
                        'xray' => $q['xray'] ?? null,
                        'verified' => $q['verified'],
                        'note' => $q['note'] ?? null,
                        'source_page' => $q['sourcePage'] ?? null,
                    ]);
                }
            }

            VocabCard::query()->delete();
            foreach ($this->json("$dir/vocab.json") as $v) {
                VocabCard::create($v);
            }
        });
    }

    private function json(string $path): array
    {
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
