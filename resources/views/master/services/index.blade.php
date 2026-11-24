@extends('auth.owner-catalog')

@section('title', 'Katalog layanan')
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
    <h1>Katalog layanan</h1>

    @if (session('status'))
        <p class="owner-alert" role="status">{{ session('status') }}</p>
    @endif

    @if (session('error'))
        <p class="error owner-alert owner-alert-error" role="alert">{{ session('error') }}</p>
    @endif

    <p>
        <a class="button" href="{{ route('owner.services.create') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah layanan
        </a>
    </p>

    <div class="table-scroll owner-card">
        <table>
            <thead>
                <tr>
                    <th scope="col">Layanan</th>
                    <th scope="col" class="number">Harga</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td class="number amount">Rp{{ number_format((int) $service->current_price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $service->archived_at === null ? 'badge-active' : 'badge-archived' }}">{{ $service->archived_at === null ? 'Aktif' : 'Diarsipkan' }}</span>
                        </td>
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
        <nav class="actions owner-pager" aria-label="Halaman katalog">
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