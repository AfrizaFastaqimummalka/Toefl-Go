<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_study_on' => 'date',
        ];
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function reviewQueue()
    {
        return $this->hasMany(ReviewQueue::class);
    }
}
