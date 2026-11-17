@extends('auth.owner-layout')

@section('title', 'Dashboard Kapster')

@section('content')
    <p><a href="{{ route('home') }}">Kembali ke beranda</a></p>
    <h1>Dashboard Kapster</h1>
    <p>{{ session('barber_name') }}</p>
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <p><a class="button" href="{{ route('barber.entry-form') }}">Tambah</a></p>

    <div class="table-scroll">
        <table>
            <caption>Pencatatan sesi ini</caption>
            <thead>
                <tr>
                    <th scope="col">Waktu (WIB)</th>
                    <th scope="col">Nama pelanggan</th>
                    <th scope="col">Layanan</th>
                    <th scope="col">Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td>{{ $entry->recorded_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                        <td>{{ $entry->customer_name ?? '—' }}</td>
                        <td>{{ $entry->service_name }}</td>
                        <td>Rp{{ number_format((int) $entry->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada pencatatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($entries->hasPages())
        <nav aria-label="Halaman pencatatan">
            @if ($entries->previousPageUrl())
                <a href="{{ $entries->previousPageUrl() }}">Sebelumnya</a>
            @endif
            @if ($entries->nextPageUrl())
                <a href="{{ $entries->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif

    <form method="post" action="{{ route('barber.session.destroy') }}">
        @csrf
        <p><button class="secondary" type="submit">Akhiri sesi</button></p>
    </form>
@endsection
