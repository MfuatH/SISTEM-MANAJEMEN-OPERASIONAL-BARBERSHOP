@extends('auth.owner-layout')

@section('title', 'Katalog layanan')
@section('main-class', 'wide')

@section('content')
    <p><a href="{{ route('owner.home') }}">Kembali ke area Owner</a></p>
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
                            <div class="actions">
                                <a href="{{ route('owner.services.edit', $service) }}">Ubah</a>
                                @if ($service->archived_at === null)
                                    <form method="post" action="{{ route('owner.services.archive', $service) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="secondary" type="submit">Arsipkan</button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('owner.services.restore', $service) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="secondary" type="submit">Aktifkan kembali</button>
                                    </form>
                                @endif
                                @if ($service->income_entries_exists)
                                    <button class="secondary" type="button" disabled title="Sudah tercatat pada transaksi">Hapus</button>
                                @else
                                    <form method="post" action="{{ route('owner.services.destroy', $service) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="secondary" type="submit" aria-label="Hapus {{ $service->name }}">Hapus</button>
                                    </form>
                                @endif
                            </div>
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
