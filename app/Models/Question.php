<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'code', 'lesson_id', 'position', 'section', 'testing_point', 'type', 'text', 'options',
        'segments', 'answer', 'explanation', 'hints', 'xray', 'verified', 'note', 'source_page',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'segments' => 'array',
            'answer' => 'array',
            'hints' => 'array',
            'xray' => 'array',
            'verified' => 'boolean',
        ];
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /** Bentuk yang dikirim ke browser (kunci ikut, supaya umpan balik instan). */
    public function toClient(): array
    {
        return [
            'code' => $this->code,
            'section' => $this->section,
            'testingPoint' => $this->testing_point,
            'type' => $this->type,
            'text' => $this->text,
            'options' => $this->options,
            'segments' => $this->segments,
            'answer' => $this->answer,
            'explanation' => $this->explanation,
            'hints' => $this->hints,
            'xray' => $this->xray,
            'verified' => $this->verified,
            'note' => $this->note,
            'sourcePage' => $this->source_page,
        ];
    }
}
