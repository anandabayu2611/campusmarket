@extends('layouts.app')

@section('title', 'Ubah Produk — CampusMarket')

@section('content')
<div class="card">
    <h1>Ubah Produk</h1>
    <p class="muted">Perbarui informasi produkmu. Perubahan langsung tersimpan.</p>

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
        action="{{ route('products.update', $product['id']) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <label for="name">Nama produk</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $product['name']) }}"
            required
        >

        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            required
        >{{ old('description', $product['description']) }}</textarea>

        <label for="price">Harga (Rp)</label>
        <input
            id="price"
            type="number"
            name="price"
            min="0"
            step="100"
            value="{{ old('price', (int) $product['price']) }}"
            required
        >

        <label for="condition">Kondisi</label>
        <select id="condition" name="condition" required>
            <option value="">Pilih kondisi</option>
            @foreach (['Baru', 'Bekas'] as $item)
                <option value="{{ $item }}"
                    @selected(old('condition', $product['condition']) === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="location">Lokasi</label>
        <input
            id="location"
            name="location"
            value="{{ old('location', $product['location']) }}"
            placeholder="Contoh: Semarang"
            required
        >

        <label for="transaction_type">Jenis transaksi</label>
        <select id="transaction_type" name="transaction_type" required>
            <option value="">Pilih jenis transaksi</option>
            @foreach (['Jual', 'Sewa', 'Barter'] as $item)
                <option value="{{ $item }}"
                    @selected(old('transaction_type', $product['transaction_type']) === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <label for="category">Kategori produk</label>
        <select id="category" name="category" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $item)
                <option value="{{ $item }}"
                    @selected(old('category', $product['category']) === $item)>
                    {{ $item }}
                </option>
            @endforeach
        </select>

        <div id="semester-wrap" style="display:none">
            <label for="semester">Semester</label>
            <select id="semester" name="semester">
                <option value="">Pilih semester</option>
                @foreach (range(1, 8) as $sem)
                    <option value="{{ $sem }}" @selected((string) old('semester', $product['semester'] ?? null) === (string) $sem)>Semester {{ $sem }}</option>
                @endforeach
            </select>
        </div>
        <script>
            (function () {
                var cat = document.getElementById('category');
                var wrap = document.getElementById('semester-wrap');
                function sync() { wrap.style.display = cat.value === @json(config('campusmarket.semester_category')) ? 'block' : 'none'; }
                cat.addEventListener('change', sync); sync();
            })();
        </script>

        <label for="photo">
            {{ !empty($product['photo_url']) ? 'Ganti foto produk (kosongkan jika tidak ingin mengganti)' : 'Tambah foto produk (opsional)' }}
        </label>
        <img
            id="photo-preview"
            src="{{ $product['photo_url'] ?? '' }}"
            alt="{{ $product['name'] }}"
            style="{{ empty($product['photo_url']) ? 'display:none;' : '' }}width:100%;max-height:160px;object-fit:cover;border-radius:10px;margin-bottom:8px"
        >
        <input
            id="photo"
            type="file"
            name="photo"
            accept=".jpg,.jpeg,.png,.webp"
        >
        <p class="hint">JPG, JPEG, PNG, atau WEBP. Maksimal 10 MB.</p>

        @if (!empty($product['photo_url']))
            <label style="display:flex;gap:8px;align-items:center;font-weight:normal">
                <input type="checkbox" name="remove_photo" value="1" style="width:auto">
                Hapus foto (produk tampil tanpa foto)
            </label>
        @endif
        <script>
            (function () {
                var input = document.getElementById('photo');
                var preview = document.getElementById('photo-preview');
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    if (!file) { return; }
                    preview.src = URL.createObjectURL(file);
                    preview.style.display = 'block';
                });
            })();
        </script>

        <button class="btn" type="submit">Simpan Perubahan</button>

        <a class="btn secondary" href="{{ route('products.show', $product['id']) }}">
            Batal
        </a>
    </form>
</div>
@endsection