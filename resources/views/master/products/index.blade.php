@extends('auth.owner-layout')

@section('title', 'Katalog produk')
@section('main-class', 'wide')

@section('content')
    <p><a href="{{ route('owner.home') }}">Kembali ke area Owner</a></p>
    <h1>Katalog produk</h1>
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif
    <p><a class="button" href="{{ route('owner.products.create') }}">Tambah produk</a></p>
    <div class="table-scroll">
        <table>
            <thead>
                <tr><th scope="col">Produk</th><th scope="col">Harga jual</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sale_price === null ? 'Khusus pemakaian' : 'Rp'.number_format((int) $product->sale_price, 0, ',', '.') }}</td>
                        <td>{{ $product->archived_at ? 'Diarsipkan' : 'Aktif' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('owner.products.edit', $product) }}">Ubah</a>
                                @unless ($product->archived_at)
                                    <form method="post" action="{{ route('owner.products.archive', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="secondary">Arsipkan</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($products->hasPages())
        <nav class="actions" aria-label="Halaman katalog produk">
            @if ($products->previousPageUrl())
                <a href="{{ $products->previousPageUrl() }}">Sebelumnya</a>
            @endif
            @if ($products->nextPageUrl())
                <a href="{{ $products->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif
@endsection
