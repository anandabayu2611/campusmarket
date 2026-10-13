@extends('layouts.app')
@section('title', 'Verifikasi Email — CampusMarket')
@section('content')
<div class="card">
    <h1>Verifikasi email universitas</h1>
    <p class="muted">Kami mengirim kode {{ $length }} angka ke <b>{{ $maskedEmail }}</b>. Kode berlaku {{ $ttl }} menit.</p>

    <form method="POST" action="{{ route('verification.verify') }}">
        @csrf
        <label for="code">Kode verifikasi</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="{{ $length }}"
               pattern="[0-9]*" required autofocus style="letter-spacing:.4em;font-size:1.4rem;text-align:center">
        @error('code')<div class="error">{{ $message }}</div>@enderror
        <button class="btn" type="submit">Verifikasi</button>
    </form>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button class="btn secondary" type="submit">Kirim ulang kode</button>
        @if ($cooldown > 0)<div class="hint center">Tunggu sekitar {{ $cooldown }} detik sebelum kirim ulang.</div>@endif
    </form>
</div>
<p class="center muted"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
@endsection
