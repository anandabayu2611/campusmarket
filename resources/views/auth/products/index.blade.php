@extends('layouts.app')

@section('title', 'Katalog Produk — CampusMarket')

@section('content')
<div class="card">
    <h1>Katalog Produk</h1>
    <p class="muted">Cari produk yang kamu butuhkan di kampus.</p>

    <form method="GET" action="{{ route('products.index') }}">
        <label for="q">Pencarian produk</label>
        <input
            id="q"
            type="search"
            name="q"
            value="{{ $filters['q'] }}"
            placeholder="Nama, deskripsi, atau kategori"
        >

        <label for="condition">Kondisi</label>
        <select id="condition" name="condition">
            <option value="">Semua kondisi</option>
            @foreach (['Baru', 'Bekas'] as $item)
                <option value="{{ $item }}"
                    @selected($filters['condition'] === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="location">Lokasi</label>
        <input
            id="location"
            name="location"
            value="{{ $filters['location'] }}"
            placeholder="Contoh: Semarang"
        >

        <label for="transaction_type">Jenis transaksi</label>
        <select id="transaction_type" name="transaction_type">
            <option value="">Semua transaksi</option>
            @foreach (['Jual', 'Sewa', 'Barter'] as $item)
                <option value="{{ $item }}"
                    @selected($filters['transaction_type'] === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="category">Kategori produk</label>
        <select id="category" name="category">
            <option value="">Semua kategori</option>
            @foreach ($categories as $item)
                <option value="{{ $item }}"
                    @selected($filters['category'] === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <button class="btn" type="submit">Cari dan Filter</button>
        <a class="btn secondary" href="{{ route('products.index') }}">
            Reset Filter
        </a>
    </form>

    <a class="btn" href="{{ route('products.create') }}">
        + Tambah Produk
    </a>
</div>

@if ($products->isEmpty())
    <div class="card">
        <p>Belum ada produk yang cocok dengan pencarian.</p>
    </div>
@else
    @foreach ($products as $product)
        <div class="card">
            @if (!empty($product['photo_url']))
                <img
                    src="{{ $product['photo_url'] }}"
                    alt="{{ $product['name'] }}"
                    style="width:100%;max-height:240px;object-fit:cover;border-radius:10px"
                >
            @endif

            <h2>{{ $product['name'] }}</h2>

            <p>
                <strong>
                    Rp {{ number_format((float) $product['price'], 0, ',', '.') }}
                </strong>
            </p>

            <p class="muted">
                {{ $product['category'] }} ·
                {{ $product['condition'] }} ·
                {{ $product['location'] }}
            </p>

            <p>
                {{ \Illuminate\Support\Str::limit($product['description'], 120) }}
            </p>

            <a
                class="btn secondary"
                href="{{ route('products.show', $product['id']) }}"
            >
                Lihat Detail
            </a>
        </div>
    @endforeach
@endif
@endsection