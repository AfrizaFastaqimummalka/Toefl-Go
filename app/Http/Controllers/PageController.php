<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;

class PageController extends Controller
{
    public function test()
    {
        return view('pages.test');
    }

    public function ai(GeminiService $gemini)
    {
        return view('pages.ai', ['aiReady' => $gemini->configured()]);
    }

    public function about()
    {
        return view('pages.about');
    }
}
