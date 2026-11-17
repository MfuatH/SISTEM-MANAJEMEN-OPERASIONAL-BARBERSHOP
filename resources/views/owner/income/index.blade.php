@extends('auth.owner-layout')

@section('title', 'Pemasukan')
@section('main-class', 'wide report-page')

@section('content')
    <p><a href="{{ route('owner.home') }}">Kembali ke area Owner</a></p>
    <h1>Pemasukan</h1>
    <p>{{ $report->month->locale('id')->translatedFormat('F Y') }}</p>

    <form method="get" action="{{ route('owner.income.index') }}">
        <div class="field">
            <label for="search">Cari pemasukan</label>
            <input id="search" name="q" type="search" maxlength="120" value="{{ $report->search }}"
                placeholder="Item, Kapster, pelanggan, atau ID transaksi">
            @error('q')
                <span class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="actions">
            <button type="submit">Cari</button>
            @if ($report->search !== '')
                <a href="{{ route('owner.income.index') }}">Hapus pencarian</a>
            @endif
            <a class="button secondary" href="{{ route('owner.income.export', $report->search !== '' ? ['q' => $report->search] : []) }}">
                Ekspor Excel
            </a>
        </div>
    </form>

    <p><strong>Total {{ $report->search !== '' ? 'hasil pencarian' : 'bulan berjalan' }} {{ $report::rupiah($total) }}</strong></p>

    <div class="table-scroll">
        <table aria-label="Rincian pemasukan">
            <thead>
                <tr>
                    <th scope="col">ID transaksi</th>
                    <th scope="col">Waktu (WIB)</th>
                    <th scope="col">Kapster</th>
                    <th scope="col">Pelanggan</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Item</th>
                    <th scope="col" class="number">Jumlah</th>
                    <th scope="col" class="number">Harga satuan</th>
                    <th scope="col" class="number">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->transaction_id }}</td>
                        <td class="report-time">{{ $report::localTime($item->recorded_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $item->barber_name }}</td>
                        <td>{{ $item->customer_name ?? '—' }}</td>
                        <td>{{ $report::itemType($item->item_type) }}</td>
                        <td>
                            {{ $item->item_name }}
                            @if ($item->used_product_name !== null)
                                <br><small>Produk dipakai {{ $item->used_product_name }}</small>
                            @endif
                        </td>
                        <td class="number">{{ $item->quantity }}</td>
                        <td class="number">{{ $report::rupiah($item->unit_price) }}</td>
                        <td class="number">{{ $report::rupiah($item->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">{{ $report->search !== '' ? 'Tidak ada item yang sesuai dengan pencarian.' : 'Belum ada pemasukan bulan ini.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <nav class="actions" aria-label="Halaman pemasukan">
            @if ($items->previousPageUrl())
                <a href="{{ $items->previousPageUrl() }}">Sebelumnya</a>
            @endif
            <span>Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>
            @if ($items->nextPageUrl())
                <a href="{{ $items->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif
@endsection
