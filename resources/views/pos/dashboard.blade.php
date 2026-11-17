@extends('auth.owner-layout')

@section('title', 'Dashboard Kapster')
@section('main-class', 'wide')

@section('content')
    <p><a href="{{ route('home') }}">Kembali ke beranda</a></p>
    <h1>Dashboard Kapster</h1>
    <p>{{ session('barber_name') }}</p>
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <div class="actions">
        <a class="button" href="{{ route('barber.entry-form') }}">Layanan</a>
        <a class="button" href="{{ route('barber.product-form') }}">Produk</a>
    </div>

    <div class="table-scroll">
        <table>
            <caption>Pencatatan sesi ini</caption>
            <thead>
                <tr>
                    <th scope="col">Transaksi</th>
                    <th scope="col">Waktu (WIB)</th>
                    <th scope="col">Nama pelanggan</th>
                    <th scope="col">Item</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td>{{ $entry->transaction_id }}</td>
                        <td>{{ Illuminate\Support\Carbon::parse($entry->recorded_at, 'UTC')->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                        <td>{{ $entry->customer_name ?? '—' }}</td>
                        <td>
                            {{ $entry->item_name }}
                            @if ($entry->item_type === 'product')
                                <br><small>Produk dijual</small>
                            @elseif ($entry->used_product_name !== null)
                                <br><small>Produk dipakai {{ $entry->used_product_name }}</small>
                            @endif
                        </td>
                        <td>{{ $entry->quantity }}</td>
                        <td>Rp{{ preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $entry->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada pencatatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($entries->hasPages())
        <nav class="actions" aria-label="Halaman pencatatan">
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
