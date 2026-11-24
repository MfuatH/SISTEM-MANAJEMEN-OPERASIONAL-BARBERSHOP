@extends('auth.owner-catalog')

@section('title', 'Katalog produk')
@section('main-class', 'wide')

@section('catalog-content')
    @unless (request()->header('Sec-Fetch-Dest') === 'iframe')
        <p>
            <a class="back-link" href="{{ route('owner.income.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Kembali ke Pemasukan
            </a>
        </p>
    @endunless
    <h1>Katalog produk</h1>
    @if (session('status'))
        <p class="owner-alert" role="status">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="error owner-alert owner-alert-error" role="alert">{{ session('error') }}</p>
    @endif
    <p>
        <a class="button" href="{{ route('owner.products.create') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah produk
        </a>
    </p>
    <div class="table-scroll owner-card">
        <table>
            <thead>
                <tr><th scope="col">Produk</th><th scope="col" class="number">Harga jual</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="number {{ $product->sale_price === null ? 'muted' : 'amount' }}">{{ $product->sale_price === null ? 'Belum ada harga jual' : 'Rp'.number_format((int) $product->sale_price, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $product->archived_at ? 'badge-archived' : 'badge-active' }}">{{ $product->archived_at ? 'Diarsipkan' : 'Aktif' }}</span></td>
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
        <nav class="actions owner-pager" aria-label="Halaman katalog produk">
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