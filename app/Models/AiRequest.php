<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRequest extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'question_code', 'status', 'latency_ms'];
}
