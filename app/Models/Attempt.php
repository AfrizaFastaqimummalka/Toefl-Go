<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $fillable = ['user_id', 'question_id', 'answer', 'correct', 'hints_used', 'time_ms', 'mode', 'answered_at'];

    protected function casts(): array
    {
        return ['answer' => 'array', 'correct' => 'boolean', 'answered_at' => 'datetime'];
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
