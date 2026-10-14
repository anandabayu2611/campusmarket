@extends('layouts.app')

@section('title', $product['name'] . ' — CampusMarket')

@section('content')
<div class="card">
    @if (!empty($product['photo_url']))
        <img
            src="{{ $product['photo_url'] }}"
            alt="{{ $product['name'] }}"
            style="width:100%;max-height:320px;object-fit:cover;border-radius:10px"
        >
    @endif

    <h1>{{ $product['name'] }}</h1>

    <h2>
        Rp {{ number_format((float) $product['price'], 0, ',', '.') }}
    </h2>

    <p>{{ $product['description'] }}</p>

    <dl>
        <dt>Kategori</dt>
        <dd>{{ $product['category'] }}</dd>

        <dt>Kondisi</dt>
        <dd>{{ $product['condition'] }}</dd>

        <dt>Lokasi</dt>
        <dd>{{ $product['location'] }}</dd>

        <dt>Transaksi</dt>
        <dd>{{ $product['transaction_type'] }}</dd>

        @if (!empty($product['semester']))
            <dt>Semester</dt>
            <dd>{{ $product['semester'] }}</dd>
        @endif

        <dt>Penjual</dt>
        <dd>
            @if ($seller)
                <a href="{{ route('students.show', $seller['id']) }}">{{ $product['seller_name'] }}</a>
            @else
                {{ $product['seller_name'] }}
            @endif
        </dd>

        @php($uniName = $product['university_name'] ?? null)
        @if ($uniName)
            <dt>Universitas penjual</dt>
            <dd>{{ $uniName }}</dd>
        @endif

        @if (!empty($seller['campus_location']))
            <dt>Lokasi kampus penjual</dt>
            <dd>{{ $seller['campus_location'] }}</dd>
        @endif

        <dt>Kontak</dt>
        <dd>
            @if (!empty($seller['phone_number']))
                <a href="https://wa.me/{{ ltrim($seller['phone_number'], '+') }}" target="_blank" rel="noopener">
                    {{ $seller['phone_number'] }} (WhatsApp)
                </a>
            @else
                <span class="muted">Penjual belum mencantumkan nomor HP.</span>
            @endif
        </dd>
    </dl>

    @if (($product['seller_id'] ?? null) === (string) auth()->user()->getAuthIdentifier())
        <a class="btn" href="{{ route('products.edit', $product['id']) }}">
            Ubah Produk
        </a>

        <form
            method="POST"
            action="{{ route('products.destroy', $product['id']) }}"
            onsubmit="return confirm('Hapus produk ini? Tindakan tidak dapat dibatalkan.')"
        >
            @csrf
            @method('DELETE')
            <button class="btn" type="submit" style="background:var(--danger)">
                Hapus Produk
            </button>
        </form>
    @endif

    <a class="btn secondary" href="{{ route('products.index') }}">
        Kembali ke Katalog
    </a>
</div>
@endsection