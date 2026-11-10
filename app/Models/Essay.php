<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Essay extends Model
{
    protected $fillable = ['user_id', 'topic', 'body', 'word_count', 'score', 'feedback'];

    protected function casts(): array
    {
        return ['feedback' => 'array'];
    }
}
