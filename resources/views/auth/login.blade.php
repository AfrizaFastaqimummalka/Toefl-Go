@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
    <h1 class="page-title">Masuk</h1>
    <p class="page-sub">Progres, XP, dan antrean soal salah tersimpan per akun. Jawaban dari mode tamu ikut digabung.</p>
    <form method="POST" action="{{ route('login') }}" class="card">
        @csrf
        <label class="field"><span>Email</span>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email">
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </label>
        <label class="field"><span>Password</span>
            <input class="input" type="password" name="password" required autocomplete="current-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </label>
        <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
    <p class="muted small" style="margin-top:14px">Belum punya akun? <a href="{{ route('register') }}"><b>Daftar</b></a></p>
@endsection
