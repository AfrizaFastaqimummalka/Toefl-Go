<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress';

    protected $fillable = ['user_id', 'section', 'testing_point', 'attempted', 'correct', 'last_attempt_at'];

    protected function casts(): array
    {
        return ['last_attempt_at' => 'datetime'];
    }
}
