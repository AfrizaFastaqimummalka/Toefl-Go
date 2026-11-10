<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Chapter;
use App\Models\ReviewQueue;
use App\Models\VocabCard;
use Illuminate\Support\Facades\DB;

class PracticeController extends Controller
{
    public function index()
    {
        $chapters = Chapter::where('available', true)->orderBy('position')
            ->with(['lessons' => fn ($q) => $q->where('kind', 'practice')])->get();

        $totals = DB::table('questions')->select('lesson_id', DB::raw('count(*) as n'))
            ->groupBy('lesson_id')->pluck('n', 'lesson_id');

        $done = [];
        $due = 0;
        $queued = 0;
        if (auth()->check()) {
            $done = Attempt::query()
                ->join('questions', 'questions.id', '=', 'attempts.question_id')
                ->where('attempts.user_id', auth()->id())
                ->groupBy('questions.lesson_id')
                ->select('questions.lesson_id', DB::raw('count(distinct attempts.question_id) as n'))
                ->pluck('n', 'lesson_id')->all();
            $due = ReviewQueue::where('user_id', auth()->id())->where('due_at', '<=', now())->count();
            $queued = ReviewQueue::where('user_id', auth()->id())->count();
        }

        $decks = VocabCard::select('category', DB::raw('count(*) as n'))->groupBy('category')->pluck('n', 'category');

        return view('learn.practice-index', compact('chapters', 'totals', 'done', 'due', 'queued', 'decks'));
    }

    public function review()
    {
        $items = ReviewQueue::where('user_id', auth()->id())->where('due_at', '<=', now())
            ->with('question')->orderBy('due_at')->limit(15)->get();

        $questions = $items->pluck('question')->filter()->map->toClient()->values();

        return view('learn.practice', [
            'title' => 'Ulangi soal salah',
            'backUrl' => route('practice'),
            'config' => [
                'mode' => 'review',
                'title' => 'Ulangi soal salah',
                'questions' => $questions,
                'cards' => LearnController::cardsFor(['verb-forms', 'connector-time', 'connector-cause', 'connector-noun', 'connector-adjective']),
                'nextUrl' => route('practice'),
                'backUrl' => route('practice'),
            ],
        ]);
    }

    public function cards(?string $category = null)
    {
        $all = VocabCard::orderBy('position')->get();
        $cats = $all->pluck('category')->unique()->values();
        $category = $category && $cats->contains($category) ? $category : $cats->first();
        $cards = $all->where('category', $category)->values()->map(fn ($c) => [
            'group' => $c->group, 'term' => $c->term, 'definition' => $c->definition, 'example' => $c->example,
        ]);

        return view('learn.cards', compact('cats', 'category', 'cards'));
    }
}
