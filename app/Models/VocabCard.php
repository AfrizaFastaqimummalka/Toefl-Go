<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabCard extends Model
{
    protected $fillable = ['category', 'group', 'term', 'definition', 'example', 'position'];
}
