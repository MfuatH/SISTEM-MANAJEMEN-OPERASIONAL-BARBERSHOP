@extends('auth.owner-catalog')

@section('title', 'Katalog layanan')
@section('main-class', 'wide')

@section('catalog-content')
    @unless (request()->header('Sec-Fetch-Dest') === 'iframe')
        <p><a href="{{ route('owner.income.index') }}">Kembali ke Pemasukan</a></p>
    @endunless
    <h1>Katalog layanan</h1>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @if (session('error'))
        <p class="error" role="alert">{{ session('error') }}</p>
    @endif

    <p><a class="button" href="{{ route('owner.services.create') }}">Tambah layanan</a></p>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th scope="col">Layanan</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td>Rp{{ number_format((int) $service->current_price, 0, ',', '.') }}</td>
                        <td>{{ $service->archived_at === null ? 'Aktif' : 'Diarsipkan' }}</td>
                        <td>
                            @include('master.catalog.actions', [
                                'resource' => 'owner.services',
                                'item' => $service,
                                'hasHistory' => $service->income_entries_exists,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Katalog masih kosong.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($services->hasPages())
        <nav class="actions" aria-label="Halaman katalog">
            @if ($services->previousPageUrl())
                <a href="{{ $services->previousPageUrl() }}">Sebelumnya</a>
            @endif
            <span>Halaman {{ $services->currentPage() }} dari {{ $services->lastPage() }}</span>
            @if ($services->nextPageUrl())
                <a href="{{ $services->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif
@endsection
