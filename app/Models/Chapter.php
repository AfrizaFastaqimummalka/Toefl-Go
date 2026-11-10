<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['slug', 'section', 'title', 'subtitle', 'position', 'available', 'phase', 'recommend'];

    protected function casts(): array
    {
        return ['available' => 'boolean', 'recommend' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }
}
