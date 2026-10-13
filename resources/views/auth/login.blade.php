@extends('layouts.app')
@section('title', 'Masuk — CampusMarket')
@section('content')
<div class="card">
    <h1>Masuk</h1>
    <p class="muted">Marketplace khusus mahasiswa.</p>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email universitas</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<div class="error">{{ $message }}</div>@enderror

        <label for="password">Kata sandi</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password')<div class="error">{{ $message }}</div>@enderror

        <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya (Remember Me)</label>

        <button class="btn" type="submit">Masuk</button>
    </form>
</div>
<p class="center muted">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
@endsection
