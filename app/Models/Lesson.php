<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['chapter_id', 'slug', 'kind', 'title', 'position', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }
}
