@extends('layouts.app')

@section('title', 'Katalog Produk — CampusMarket')
@section('container_class', 'wide')

@section('content')
@php
    $base = request()->except(['page']);
    $hasAdvanced = $filters['condition'] !== '' || $filters['location'] !== '' || $filters['transaction_type'] !== ''
        || $filters['university'] !== '' || $filters['semester'] !== '' || $filters['min_price'] !== '' || $filters['max_price'] !== '';
@endphp

<div class="card">
    <div class="page-head">
        <div>
            <h1>Katalog Produk</h1>
            <p class="muted">Cari produk yang kamu butuhkan di kampus.</p>
        </div>
        <a class="btn sm" href="{{ route('products.create') }}">+ Tambah Produk</a>
    </div>

    <form method="GET" action="{{ route('products.index') }}">
        @if ($filters['mine'])<input type="hidden" name="mine" value="1">@endif
        @if ($filters['category'] !== '')<input type="hidden" name="category" value="{{ $filters['category'] }}">@endif

        <div class="searchbar">
            <input id="q" type="search" name="q" value="{{ $filters['q'] }}"
                   placeholder="Cari nama, deskripsi, atau kategori" aria-label="Pencarian produk">
            <button class="btn" type="submit">Cari</button>
        </div>

        <details class="filters" @if ($hasAdvanced) open @endif>
            <summary>Filter lanjutan</summary>
            <div class="filter-grid">
                <div>
                    <label for="condition">Kondisi</label>
                    <select id="condition" name="condition">
                        <option value="">Semua kondisi</option>
                        @foreach (['Baru', 'Bekas'] as $item)
                            <option value="{{ $item }}" @selected($filters['condition'] === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="transaction_type">Jenis transaksi</label>
                    <select id="transaction_type" name="transaction_type">
                        <option value="">Semua transaksi</option>
                        @foreach (['Jual', 'Sewa', 'Barter'] as $item)
                            <option value="{{ $item }}" @selected($filters['transaction_type'] === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="university">Universitas</label>
                    <select id="university" name="university">
                        <option value="">Semua universitas</option>
                        @foreach ($universities as $uni)
                            <option value="{{ $uni['id'] }}" @selected($filters['university'] === $uni['id'])>{{ $uni['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="location">Lokasi</label>
                    <input id="location" name="location" value="{{ $filters['location'] }}" placeholder="Contoh: Tembalang">
                </div>
                <div>
                    <label for="min_price">Harga minimum (Rp)</label>
                    <input id="min_price" type="number" name="min_price" min="0" value="{{ $filters['min_price'] }}" placeholder="0">
                </div>
                <div>
                    <label for="max_price">Harga maksimum (Rp)</label>
                    <input id="max_price" type="number" name="max_price" min="0" value="{{ $filters['max_price'] }}" placeholder="Tanpa batas">
                </div>
                <div>
                    <label for="semester">Semester (Buku per Semester)</label>
                    <select id="semester" name="semester">
                        <option value="">Semua semester</option>
                        @foreach (range(1, 8) as $sem)
                            <option value="{{ $sem }}" @selected((string) $filters['semester'] === (string) $sem)>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn" type="submit">Terapkan Filter</button>
                <a class="btn secondary" href="{{ route('products.index', $filters['mine'] ? ['mine' => 1] : []) }}">Reset</a>
            </div>
        </details>
    </form>

    <p class="chips-label">Kategori</p>
    <div class="chips">
        <a class="chip @if ($filters['category'] === '') active @endif"
           href="{{ route('products.index', array_diff_key($base, ['category' => 1])) }}">Semua</a>
        @foreach ($specialCategories as $special)
            <a class="chip @if ($filters['category'] === $special) active @endif"
               href="{{ route('products.index', array_merge($base, ['category' => $special])) }}">{{ $special }}</a>
        @endforeach
        @foreach (array_diff($categories, $specialCategories) as $other)
            <a class="chip @if ($filters['category'] === $other) active @endif"
               href="{{ route('products.index', array_merge($base, ['category' => $other])) }}">{{ $other }}</a>
        @endforeach
    </div>

    <p class="chips-label">Tampilkan</p>
    <div class="chips">
        <a class="chip @if (! $filters['mine']) active @endif"
           href="{{ route('products.index', array_diff_key($base, ['mine' => 1])) }}">Semua produk</a>
        <a class="chip @if ($filters['mine']) active @endif"
           href="{{ route('products.index', array_merge($base, ['mine' => 1])) }}">Produk saya</a>
    </div>
</div>

<p class="muted" style="margin:0 0 12px">{{ $products->total() }} produk ditemukan</p>

@if ($products->isEmpty())
    <div class="card center">
        <p>Belum ada produk yang cocok dengan pencarian.</p>
        <a class="btn secondary sm" href="{{ route('products.index') }}">Reset semua filter</a>
    </div>
@else
    <div class="product-grid">
        @foreach ($products as $product)
            <a class="product-card" href="{{ route('products.show', $product['id']) }}">
                @if (!empty($product['photo_url']))
                    <img src="{{ $product['photo_url'] }}" alt="{{ $product['name'] }}" loading="lazy">
                @else
                    <div class="noimg"></div>
                @endif
                <div class="product-body">
                    <h3>{{ $product['name'] }}</h3>
                    <div class="price">Rp {{ number_format((float) $product['price'], 0, ',', '.') }}</div>
                    <div class="tags">
                        <span class="tag cat">{{ $product['category'] }}</span>
                        <span class="tag">{{ $product['condition'] }}</span>
                        <span class="tag">{{ $product['transaction_type'] ?? 'Jual' }}</span>
                        @if (!empty($product['semester']))
                            <span class="tag">Semester {{ $product['semester'] }}</span>
                        @endif
                    </div>
                    <div class="loc">
                        📍 {{ $product['location'] }}@if (!empty($product['university_name'])) · {{ $product['university_name'] }}@endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if ($products->hasPages())
        <div class="pager">
            @if (! $products->onFirstPage())
                <a class="btn secondary" href="{{ $products->previousPageUrl() }}">« Sebelumnya</a>
            @endif
            <span class="muted">Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</span>
            @if ($products->hasMorePages())
                <a class="btn secondary" href="{{ $products->nextPageUrl() }}">Berikutnya »</a>
            @endif
        </div>
    @endif
@endif
@endsection
