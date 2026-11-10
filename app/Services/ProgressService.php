<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Progress;
use App\Models\Question;
use App\Models\ReviewQueue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    /** Cek jawaban di server; client tidak dipercaya. */
    public function isCorrect(Question $q, array $given): bool
    {
        $key = $q->answer;

        if ($q->type === 'completion') {
            return ($given['option'] ?? null) === ($key['option'] ?? null);
        }

        if ($q->type === 'error-find') {
            $kind = $given['kind'] ?? null;
            if ($kind !== ($key['kind'] ?? null)) {
                return false;
            }

            return match ($kind) {
                'segment' => (int) ($given['index'] ?? -1) === (int) $key['index'],
                'missing' => ($given['what'] ?? null) === ($key['what'] ?? null),
                default => true,
            };
        }

        return false;
    }

    /**
     * @return array{correct: bool, xp: int}
     */
    public function record(User $user, Question $q, array $answer, int $hintsUsed, ?int $timeMs, string $mode = 'practice', ?Carbon $at = null, bool $touchStreak = true): array
    {
        $at ??= now();
        $correct = $this->isCorrect($q, $answer);
        $xp = $correct ? ($hintsUsed === 0 ? 10 : 5) : 1;

        DB::beginTransaction();
        try {
            Attempt::create([
                'user_id' => $user->id,
                'question_id' => $q->id,
                'answer' => $answer,
                'correct' => $correct,
                'hints_used' => min($hintsUsed, 3),
                'time_ms' => $timeMs,
                'mode' => $mode,
                'answered_at' => $at,
            ]);

            $p = Progress::where('user_id', $user->id)
                ->where('testing_point', $q->testing_point)
                ->first();
            if (! $p) {
                $p = new Progress([
                    'user_id' => $user->id,
                    'testing_point' => $q->testing_point,
                    'section' => $q->section,
                    'attempted' => 0,
                    'correct' => 0,
                ]);
            }
            $p->attempted += 1;
            $p->correct += $correct ? 1 : 0;
            if (! $p->last_attempt_at || $at->gt($p->last_attempt_at)) {
                $p->last_attempt_at = $at;
            }
            $p->save();

            $this->updateQueue($user, $q, $correct, $at);

            $user->xp += $xp;
            if ($touchStreak) {
                $this->touchStreak($user);
            }
            $user->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return ['correct' => $correct, 'xp' => $xp];
    }

    /** Dipanggil sekali setelah sinkronisasi data tamu. */
    public function touchToday(User $user): void
    {
        $this->touchStreak($user);
        $user->save();
    }

    private function updateQueue(User $user, Question $q, bool $correct, Carbon $at): void
    {
        $entry = ReviewQueue::where('user_id', $user->id)->where('question_id', $q->id)->first();

        if (! $correct) {
            // Hindari updateOrCreate (memakai savepoint, tidak kompatibel dengan Neon pooler).
            if ($entry) {
                $entry->update(['box' => 0, 'due_at' => $at->copy()->addMinutes(10)]);
            } else {
                ReviewQueue::create([
                    'user_id' => $user->id,
                    'question_id' => $q->id,
                    'box' => 0,
                    'due_at' => $at->copy()->addMinutes(10),
                ]);
            }

            return;
        }

        if (! $entry) {
            return;
        }

        $box = $entry->box + 1;
        $intervals = config('toefl.review_intervals');
        if ($box > count($intervals)) {
            $entry->delete();

            return;
        }

        $entry->update(['box' => $box, 'due_at' => $at->copy()->addDays($intervals[$box - 1])]);
    }

    private function touchStreak(User $user): void
    {
        $today = now()->startOfDay();
        $last = $user->last_study_on?->copy()->startOfDay();

        if ($last && $last->equalTo($today)) {
            return;
        }

        $user->streak = ($last && $last->equalTo($today->copy()->subDay())) ? $user->streak + 1 : 1;
        $user->best_streak = max($user->best_streak, $user->streak);
        $user->last_study_on = $today;
    }

    /** Ringkasan untuk halaman progres. */
    public function summary(User $user): array
    {
        $labels = config('toefl.testing_points');
        $rows = Progress::where('user_id', $user->id)->get()->keyBy('testing_point');

        $points = [];
        foreach ($labels as $key => $meta) {
            $r = $rows->get($key);
            $attempted = $r?->attempted ?? 0;
            $points[] = [
                'key' => $key,
                'label' => $meta['label'],
                'chapter' => $meta['chapter'] ?? null,
                'attempted' => $attempted,
                'correct' => $r?->correct ?? 0,
                'pct' => $attempted ? (int) round($r->correct / $attempted * 100) : null,
            ];
        }

        $weak = collect($points)->filter(fn ($p) => $p['attempted'] >= 3)->sortBy('pct')->first();
        if ($weak && $weak['pct'] >= 85) {
            $weak = null;
        }

        return [
            'points' => $points,
            'weakest' => $weak,
            'due' => ReviewQueue::where('user_id', $user->id)->where('due_at', '<=', now())->count(),
            'queued' => ReviewQueue::where('user_id', $user->id)->count(),
            'total' => (int) Attempt::where('user_id', $user->id)->count(),
        ];
    }
}
