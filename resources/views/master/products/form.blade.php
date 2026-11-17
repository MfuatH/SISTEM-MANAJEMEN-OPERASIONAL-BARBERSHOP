@extends('auth.owner-layout')

@section('title', $product->exists ? 'Ubah produk' : 'Tambah produk')

@section('content')
    <p><a href="{{ route('owner.products.index') }}">Kembali ke katalog produk</a></p>
    <h1>{{ $product->exists ? 'Ubah produk' : 'Tambah produk' }}</h1>

    <form method="post" action="{{ $product->exists ? route('owner.products.update', $product) : route('owner.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="field">
            <label for="name">Nama produk</label>
            <input id="name" name="name" maxlength="120" required value="{{ is_string(old('name', $product->name)) ? old('name', $product->name) : '' }}">
            @error('name')
                <p class="error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="sale_price">Harga jual (Rp)</label>
            <input id="sale_price" name="sale_price" type="number" min="1" max="999999999999999" step="1"
                aria-describedby="sale-price-help" value="{{ is_scalar(old('sale_price', $product->sale_price)) ? old('sale_price', $product->sale_price) : '' }}">
            <small id="sale-price-help">Isi harga jual agar produk tersedia di form Kapster.</small>
            @error('sale_price')
                <p class="error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Simpan</button>
    </form>
@endsection
