<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Services\ProgressService;
use PHPUnit\Framework\TestCase;

class ProgressServiceTest extends TestCase
{
    public function test_completion_answer_is_checked_by_option(): void
    {
        $q = new Question(['type' => 'completion', 'answer' => ['option' => 'B']]);
        $s = new ProgressService;

        $this->assertTrue($s->isCorrect($q, ['option' => 'B']));
        $this->assertFalse($s->isCorrect($q, ['option' => 'A']));
    }

    public function test_error_find_checks_kind_and_target(): void
    {
        $s = new ProgressService;

        $segment = new Question(['type' => 'error-find', 'answer' => ['kind' => 'segment', 'index' => 1]]);
        $this->assertTrue($s->isCorrect($segment, ['kind' => 'segment', 'index' => 1]));
        $this->assertFalse($s->isCorrect($segment, ['kind' => 'segment', 'index' => 2]));
        $this->assertFalse($s->isCorrect($segment, ['kind' => 'correct']));

        $missing = new Question(['type' => 'error-find', 'answer' => ['kind' => 'missing', 'what' => 'subject']]);
        $this->assertTrue($s->isCorrect($missing, ['kind' => 'missing', 'what' => 'subject']));
        $this->assertFalse($s->isCorrect($missing, ['kind' => 'missing', 'what' => 'verb']));

        $ok = new Question(['type' => 'error-find', 'answer' => ['kind' => 'correct']]);
        $this->assertTrue($s->isCorrect($ok, ['kind' => 'correct']));
    }
}
