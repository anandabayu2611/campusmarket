@extends('layouts.app')
@section('title', 'Daftar — CampusMarket')
@section('content')
<div class="card">
    <h1>Daftar Mahasiswa</h1>
    <p class="muted">Gunakan email resmi universitas untuk memverifikasi status kemahasiswaan Anda.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <label for="name">Nama lengkap</label>
        <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
        @error('name')<div class="error">{{ $message }}</div>@enderror
        <div class="hint">Tulis sesuai data kampus agar verifikasi otomatis berhasil.</div>

        <label for="university_id">Universitas</label>
        <select id="university_id" name="university_id" required>
            <option value="">— Pilih universitas —</option>
            @foreach ($universities as $uni)
                <option value="{{ $uni['id'] }}"
                        data-domains="{{ '@'.$uni['email_domain'] }}"
                        @selected(old('university_id') === $uni['id'])>{{ $uni['name'] }}</option>
            @endforeach
        </select>
        @error('university_id')<div class="error">{{ $message }}</div>@enderror

        <label for="major">Jurusan / Program studi</label>
        <input id="major" name="major" value="{{ old('major') }}" required>
        @error('major')<div class="error">{{ $message }}</div>@enderror

        <label for="email">Email universitas</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        <div class="hint" id="domain-hint"></div>
        @error('email')<div class="error">{{ $message }}</div>@enderror

        <label for="password">Kata sandi</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        <div class="hint">Minimal 8 karakter, kombinasi huruf dan angka.</div>
        @error('password')<div class="error">{{ $message }}</div>@enderror

        <label for="password_confirmation">Ulangi kata sandi</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

        <label class="check"><input type="checkbox" name="terms" value="1" @checked(old('terms'))> Saya menyetujui syarat penggunaan &amp; kebijakan privasi.</label>
        @error('terms')<div class="error">{{ $message }}</div>@enderror

        <button class="btn" type="submit">Daftar &amp; kirim kode verifikasi</button>
    </form>
</div>
<p class="center muted">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>

<script>
    (function () {
        var sel = document.getElementById('university_id'), hint = document.getElementById('domain-hint');
        function update() {
            var d = sel.options[sel.selectedIndex].getAttribute('data-domains');
            hint.textContent = d ? 'Email harus berakhiran ' + d + ' atau subdomainnya (mis. students.' + d.substring(1) + ').' : '';
        }
        sel.addEventListener('change', update); update();
    })();
</script>
@endsection
