@extends('auth.owner-catalog')

@section('title', 'Katalog produk')
@section('main-class', 'wide')

@section('catalog-content')
    @unless (request()->header('Sec-Fetch-Dest') === 'iframe')
        <p><a href="{{ route('owner.income.index') }}">Kembali ke Pemasukan</a></p>
    @endunless
    <h1>Katalog produk</h1>
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="error" role="alert">{{ session('error') }}</p>
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
                        <td>{{ $product->sale_price === null ? 'Belum ada harga jual' : 'Rp'.number_format((int) $product->sale_price, 0, ',', '.') }}</td>
                        <td>{{ $product->archived_at ? 'Diarsipkan' : 'Aktif' }}</td>
                        <td>
                            @include('master.catalog.actions', [
                                'resource' => 'owner.products',
                                'item' => $product,
                                'hasHistory' => $product->sales_exists || $product->historical_usage_exists,
                            ])
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
            <span>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</span>
            @if ($products->nextPageUrl())
                <a href="{{ $products->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif
@endsection
