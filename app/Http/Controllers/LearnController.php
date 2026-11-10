<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\VocabCard;
use Illuminate\Support\Facades\DB;

class LearnController extends Controller
{
    public function home()
    {
        $chapters = Chapter::orderBy('position')->get()->groupBy('section');

        return view('learn.home', [
            'chapters' => $chapters,
            'sections' => config('toefl.sections'),
        ]);
    }

    public function chapter(Chapter $chapter)
    {
        abort_unless($chapter->available, 404);

        $chapter->load('lessons');
        $done = $this->answeredPerLesson();
        $totals = DB::table('questions')
            ->whereIn('lesson_id', $chapter->lessons->pluck('id'))
            ->select('lesson_id', DB::raw('count(*) as n'))
            ->groupBy('lesson_id')->pluck('n', 'lesson_id');

        return view('learn.chapter', compact('chapter', 'done', 'totals'));
    }

    public function lesson(Chapter $chapter, string $lessonSlug)
    {
        abort_unless($chapter->available, 404);

        $lesson = Lesson::where('chapter_id', $chapter->id)->where('slug', $lessonSlug)->firstOrFail();
        $siblings = $chapter->lessons()->get();
        $idx = $siblings->search(fn ($l) => $l->id === $lesson->id);
        $next = $siblings->get($idx + 1);
        $nextUrl = $next
            ? route('lesson', [$chapter, $next->slug])
            : route('chapter', $chapter);

        if ($lesson->kind === 'summary') {
            return view('learn.summary', compact('chapter', 'lesson', 'nextUrl'));
        }

        $questions = $lesson->questions->map->toClient()->values();
        $cards = $this->cardsFor($chapter->recommend ?? []);

        return view('learn.practice', [
            'title' => $lesson->title,
            'backUrl' => route('chapter', $chapter),
            'config' => [
                'mode' => 'practice',
                'title' => $lesson->title,
                'questions' => $questions,
                'cards' => $cards,
                'nextUrl' => $nextUrl,
                'backUrl' => route('chapter', $chapter),
            ],
        ]);
    }

    /** @return array<string, int> lesson_id => jumlah soal yang pernah dijawab */
    private function answeredPerLesson(): array
    {
        if (! auth()->check()) {
            return [];
        }

        return Attempt::query()
            ->join('questions', 'questions.id', '=', 'attempts.question_id')
            ->where('attempts.user_id', auth()->id())
            ->groupBy('questions.lesson_id')
            ->select('questions.lesson_id', DB::raw('count(distinct attempts.question_id) as n'))
            ->pluck('n', 'lesson_id')->all();
    }

    public static function cardsFor(array $categories)
    {
        if (! $categories) {
            return [];
        }

        return VocabCard::whereIn('category', $categories)->orderBy('position')->get()
            ->map(fn ($c) => [
                'category' => $c->category,
                'group' => $c->group,
                'term' => $c->term,
                'definition' => $c->definition,
                'example' => $c->example,
            ])->values()->all();
    }
}
