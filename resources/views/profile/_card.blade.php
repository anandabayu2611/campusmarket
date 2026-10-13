@php
    $initials = collect(preg_split('/\s+/', trim($u['name'])))->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $status = $u['status_verifikasi'] ?? 'unverified';
    $isStudent = ($u['role'] ?? 'student') === 'student';
@endphp

<div class="head">
    @if (! empty($u['avatar_url']))
        <img class="avatar" src="{{ $u['avatar_url'] }}" alt="Foto {{ $u['name'] }}">
    @else
        <div class="avatar">{{ $initials }}</div>
    @endif
    <div>
        <h1>{{ $u['name'] }}</h1>
        @if (! $isStudent)
            <span class="badge ok">{{ ucfirst(str_replace('_', ' ', $u['role'])) }}</span>
        @elseif ($status === 'verified')
            <span class="badge ok">✓ Mahasiswa terverifikasi</span>
        @elseif ($status === 'rejected')
            <span class="badge bad">Verifikasi ditolak</span>
        @else
            <span class="badge warn">Menunggu verifikasi</span>
        @endif
    </div>
</div>

<dl>
    <dt>Universitas</dt><dd>{{ $university['name'] ?? '—' }}</dd>
    <dt>Jurusan</dt><dd>{{ $u['major'] ?? '—' }}</dd>
    <dt>Lokasi kampus</dt><dd>{{ $u['campus_location'] ?? '—' }}</dd>
    @if ($own)
        <dt>Email</dt><dd>{{ $u['email'] }}</dd>
        <dt>No. HP</dt><dd>{{ $u['phone_number'] ?? '—' }}</dd>
    @endif
</dl>

<div class="ratings">
    @foreach (['seller' => 'Rating penjual', 'buyer' => 'Rating pembeli'] as $role => $label)
        @php $r = $ratings[$role]; @endphp
        <div class="rating"><span class="muted">{{ $label }}</span>
            <b>{{ $r['count'] ? '★ '.number_format($r['average'], 1) : '—' }}</b>
            <span class="muted">{{ $r['count'] ? $r['count'].' ulasan' : 'Belum ada ulasan' }}</span></div>
    @endforeach
</div>

@if ($own && $isStudent && $status !== 'verified' && ! empty($note))
    <p class="hint">Catatan verifikasi: {{ $note }}</p>
@endif
