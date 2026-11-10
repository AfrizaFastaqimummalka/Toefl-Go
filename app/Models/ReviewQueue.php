<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewQueue extends Model
{
    protected $table = 'review_queue';

    protected $fillable = ['user_id', 'question_id', 'box', 'due_at'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
