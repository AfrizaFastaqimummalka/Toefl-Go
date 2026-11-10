<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttemptRequest;
use App\Http\Requests\SyncAttemptsRequest;
use App\Models\Question;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AttemptController extends Controller
{
    public function store(StoreAttemptRequest $request, ProgressService $service): JsonResponse
    {
        $q = Question::where('code', $request->validated('code'))->firstOrFail();
        $user = $request->user();

        $result = $service->record(
            $user, $q,
            $request->validated('answer'),
            (int) $request->validated('hints_used'),
            $request->validated('time_ms'),
            $request->validated('mode') ?? 'practice',
        );

        return response()->json($result + [
            'xp_total' => $user->xp,
            'streak' => $user->streak,
        ]);
    }

    public function sync(SyncAttemptsRequest $request, ProgressService $service): JsonResponse
    {
        $user = $request->user();
        $items = collect($request->validated('attempts'))->sortBy('at')->values();
        $questions = Question::whereIn('code', $items->pluck('code')->unique())->get()->keyBy('code');

        foreach ($items as $it) {
            $at = ! empty($it['at']) ? Carbon::parse($it['at']) : now();
            if ($at->isFuture()) {
                $at = now();
            }
            $service->record($user, $questions[$it['code']], $it['answer'], (int) $it['hints_used'], $it['time_ms'] ?? null, 'practice', $at, false);
        }
        $service->touchToday($user);

        return response()->json(['merged' => $items->count(), 'xp_total' => $user->xp, 'streak' => $user->streak]);
    }
}
