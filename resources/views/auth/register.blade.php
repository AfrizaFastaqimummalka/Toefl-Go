@extends('layouts.app')
@section('title', 'Daftar')
@section('content')
    <h1 class="page-title">Daftar</h1>
    <p class="page-sub">Gratis, cuma butuh email dan password.</p>
    <form method="POST" action="{{ route('register') }}" class="card">
        @csrf
        <label class="field"><span>Nama</span>
            <input class="input" type="text" name="name" value="{{ old('name') }}" required maxlength="60" autocomplete="name">
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </label>
        <label class="field"><span>Email</span>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" inputmode="email">
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </label>
        <label class="field"><span>Password (min. 8 karakter)</span>
            <input class="input" type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </label>
        <label class="field"><span>Ulangi password</span>
            <input class="input" type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <button class="btn btn-primary btn-block" type="submit">Buat akun</button>
    </form>
    <p class="muted small" style="margin-top:14px">Sudah punya akun? <a href="{{ route('login') }}"><b>Masuk</b></a></p>
@endsection
