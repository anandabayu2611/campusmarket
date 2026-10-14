@extends('layouts.app')

@section('title', 'Tambah Produk — CampusMarket')

@section('content')
<div class="card">
    <h1>Tambah Produk</h1>
    <p class="muted">Masukkan informasi produk yang ingin kamu tawarkan.</p>

    @if ($errors->any())
        <div class="alert warn">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('products.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <label for="name">Nama produk</label>
        <input
            id="name"
            name="name"
            value="{{ old('name') }}"
            required
        >

        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            required
        >{{ old('description') }}</textarea>

        <label for="price">Harga (Rp)</label>
        <input
            id="price"
            type="number"
            name="price"
            min="0"
            step="100"
            value="{{ old('price') }}"
            required
        >

        <label for="condition">Kondisi</label>
        <select id="condition" name="condition" required>
            <option value="">Pilih kondisi</option>
            @foreach (['Baru', 'Bekas'] as $item)
                <option value="{{ $item }}"
                    @selected(old('condition') === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="location">Lokasi</label>
        <input
            id="location"
            name="location"
            value="{{ old('location') }}"
            placeholder="Contoh: Semarang"
            required
        >

        <label for="transaction_type">Jenis transaksi</label>
        <select id="transaction_type" name="transaction_type" required>
            <option value="">Pilih jenis transaksi</option>
            @foreach (['Jual', 'Sewa', 'Barter'] as $item)
                <option value="{{ $item }}"
                    @selected(old('transaction_type') === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="category">Kategori produk</label>
        <select id="category" name="category" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $item)
                <option value="{{ $item }}"
                    @selected(old('category') === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="photo">Foto produk</label>
        <input
            id="photo"
            type="file"
            name="photo"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >
        <p class="hint">JPG, JPEG, PNG, atau WEBP. Maksimal 4 MB.</p>

        <button class="btn" type="submit">Simpan Produk</button>

        <a class="btn secondary" href="{{ route('products.index') }}">
            Batal
        </a>
    </form>
</div>
@endsection