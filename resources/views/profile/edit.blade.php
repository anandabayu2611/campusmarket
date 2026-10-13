@extends('layouts.app')
@section('title', 'Edit Profil — CampusMarket')
@section('content')
@php $locked = ($u['status_verifikasi'] ?? '') === 'verified' && ($u['role'] ?? 'student') === 'student'; @endphp

<div class="card">
    <h1>Edit profil</h1>
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <label for="name">Nama lengkap</label>
        <input id="name" name="name" value="{{ old('name', $u['name']) }}" required @disabled($locked)>
        @if ($locked)<div class="hint">Nama terkunci karena sudah diverifikasi universitas. Hubungi admin jika perlu koreksi.</div>@endif
        @error('name')<div class="error">{{ $message }}</div>@enderror

        <label>Universitas</label>
        <input value="{{ $university['name'] ?? '—' }}" disabled>
        <div class="hint">Universitas mengikuti email kampus dan tidak dapat diubah.</div>

        <label for="major">Jurusan / Program studi</label>
        <input id="major" name="major" value="{{ old('major', $u['major'] ?? '') }}" required>
        @error('major')<div class="error">{{ $message }}</div>@enderror

        <label for="campus_location">Lokasi kampus</label>
        <input id="campus_location" name="campus_location" value="{{ old('campus_location', $u['campus_location'] ?? '') }}" placeholder="mis. Kampus Tembalang, Fakultas Teknik">
        @error('campus_location')<div class="error">{{ $message }}</div>@enderror

        <label for="phone_number">No. HP / WhatsApp</label>
        <input id="phone_number" name="phone_number" value="{{ old('phone_number', $u['phone_number'] ?? '') }}" inputmode="tel" placeholder="081234567890">
        @error('phone_number')<div class="error">{{ $message }}</div>@enderror

        <label for="avatar">Foto profil</label>
        <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
        <div class="hint">JPG/PNG/WEBP, maksimal 10 MB.</div>
        @error('avatar')<div class="error">{{ $message }}</div>@enderror
        @if (! empty($u['avatar_url']))
            <label class="check"><input type="checkbox" name="remove_avatar" value="1"> Hapus foto saat ini</label>
        @endif

        <button class="btn" type="submit">Simpan perubahan</button>
        <a class="btn secondary" href="{{ route('profile.show') }}">Batal</a>
    </form>
</div>

<div class="card">
    <h2>Ubah kata sandi</h2>
    <form method="POST" action="{{ route('profile.password') }}">
        @csrf @method('PUT')

        <label for="current_password">Kata sandi saat ini</label>
        <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        @error('current_password')<div class="error">{{ $message }}</div>@enderror

        <label for="new_password">Kata sandi baru</label>
        <input id="new_password" type="password" name="password" required autocomplete="new-password">
        @error('password')<div class="error">{{ $message }}</div>@enderror

        <label for="new_password_confirmation">Ulangi kata sandi baru</label>
        <input id="new_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

        <button class="btn" type="submit">Ubah kata sandi</button>
    </form>
</div>
@endsection
