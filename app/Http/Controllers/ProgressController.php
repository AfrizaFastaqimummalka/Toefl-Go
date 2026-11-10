<?php

namespace App\Http\Controllers;

use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function show(ProgressService $service)
    {
        $summary = auth()->check() ? $service->summary(auth()->user()) : null;

        return view('pages.progress', ['summary' => $summary]);
    }

    public function export(Request $request)
    {
        $user = $request->user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'user' => ['name' => $user->name, 'xp' => $user->xp, 'streak' => $user->streak, 'best_streak' => $user->best_streak],
            'progress' => $user->progress()->get(['section', 'testing_point', 'attempted', 'correct', 'last_attempt_at']),
            'attempts' => $user->attempts()->with('question:id,code')->orderBy('answered_at')->get()
                ->map(fn ($a) => [
                    'question' => $a->question?->code,
                    'answer' => $a->answer,
                    'correct' => $a->correct,
                    'hints_used' => $a->hints_used,
                    'time_ms' => $a->time_ms,
                    'mode' => $a->mode,
                    'answered_at' => $a->answered_at?->toIso8601String(),
                ]),
            'review_queue' => $user->reviewQueue()->with('question:id,code')->get()
                ->map(fn ($r) => ['question' => $r->question?->code, 'box' => $r->box, 'due_at' => $r->due_at?->toIso8601String()]),
        ];

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="toefl-go-export.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->attempts()->delete();
            $user->progress()->delete();
            $user->reviewQueue()->delete();
            $user->forceFill(['xp' => 0, 'streak' => 0, 'best_streak' => 0, 'last_study_on' => null])->save();
        });

        return redirect()->route('progress')->with('status', 'Progres direset.');
    }
}
