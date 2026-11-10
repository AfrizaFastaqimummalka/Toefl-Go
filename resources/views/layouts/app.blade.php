<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8ff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b061a" media="(prefers-color-scheme: dark)">
    <title>@yield('title', 'toefl-go') · toefl-go</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.TOGO = {
            auth: @json(auth()->check()),
            xp: @json(auth()->user()?->xp ?? 0),
            streak: @json(auth()->user()?->streak ?? 0),
            aiReady: @json((bool) config('services.gemini.key')),
            urls: {
                attempt: @json(route('api.attempts')),
                sync: @json(route('api.sync')),
                tutor: @json(route('api.tutor')),
            },
        };
    </script>
</head>
<body data-skill="@yield('skill', 'structure')">
<div x-data="shell">
    <div class="shell">
        <header class="topbar">
            <a href="{{ route('home') }}" class="brand">toefl<span>-go</span></a>
            <div class="stats">
                @auth
                    <span class="pill" title="Streak hari berturut-turut"><span aria-hidden="true">🔥</span> <span x-text="streak"></span> hari</span>
                    <span class="pill" title="XP"><span x-text="xp"></span> XP</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="pill" style="min-height:44px">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="pill" style="min-height:44px">Masuk</a>
                @endauth
            </div>
        </header>

        <main>
            @if (session('status'))
                <div class="flash" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>

    @include('partials.tutor-sheet')

    <div class="toast" x-show="toast" x-cloak x-text="toast" role="status"></div>

    <nav class="bottom-nav" aria-label="Navigasi utama">
        <ul>
            @php
                $nav = [
                    ['home', 'Belajar', 'M4 5.5A1.5 1.5 0 0 1 5.5 4H11v15H5.5A1.5 1.5 0 0 1 4 17.5v-12ZM13 4h5.5A1.5 1.5 0 0 1 20 5.5v12a1.5 1.5 0 0 1-1.5 1.5H13V4Z', ['home', 'chapter', 'lesson']],
                    ['practice', 'Latihan', 'M4 12.5 9 17.5 20 6.5', ['practice', 'review', 'cards']],
                    ['test', 'Tes', 'M12 7v5l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z', ['test']],
                    ['progress', 'Progres', 'M5 19V11M12 19V5M19 19v-6', ['progress']],
                    ['ai', 'AI', 'M5 5h14v10H9l-4 4V5Z', ['ai']],
                ];
                $current = request()->route()?->getName();
            @endphp
            @foreach ($nav as [$route, $label, $path, $names])
                <li>
                    <a href="{{ route($route) }}" @if (in_array($current, $names, true)) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}"/></svg>
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</div>
</body>
</html>
