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

        <dt>Penjual</dt>
        <dd>{{ $product['seller_name'] }}</dd>
    </dl>

    <a class="btn secondary" href="{{ route('products.index') }}">
        Kembali ke Katalog
    </a>
</div>
@endsection