<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('tutor', function (Request $request) {
            $by = (string) ($request->user()?->id ?? $request->ip());

            return [
                Limit::perMinute(config('services.gemini.per_minute'))->by("tutor-m:$by"),
                Limit::perDay(config('services.gemini.per_day'))->by("tutor-d:$by"),
            ];
        });
    }
}
