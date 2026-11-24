@extends('auth.owner-area')

@section('title', 'Pemasukan')

@section('owner-content')
    <header class="owner-page-header">
        <h1>Pemasukan</h1>
        <p>{{ $report->month->locale('id')->translatedFormat('F Y') }}</p>
    </header>
    @include('owner.income.catalog-dialog')

    <form class="owner-toolbar" method="get" action="{{ route('owner.income.index') }}">
        <div class="field">
            <label for="search">Cari pemasukan</label>
            <input id="search" name="q" type="search" maxlength="120" value="{{ $report->search }}"
                placeholder="Item, Kapster, pelanggan, atau ID transaksi">
            @error('q')
                <span class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="actions">
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                Cari
            </button>
            @if ($report->search !== '')
                <a href="{{ route('owner.income.index') }}">Hapus pencarian</a>
            @endif
            <a class="button secondary" href="{{ route('owner.income.export', $report->search !== '' ? ['q' => $report->search] : []) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                Ekspor Excel
            </a>
        </div>
    </form>

    <p class="owner-total"><strong>Total {{ $report->search !== '' ? 'hasil pencarian' : 'bulan berjalan' }} <span class="owner-total-value">{{ $report::rupiah($total) }}</span></strong></p>

    <div class="table-scroll owner-card">
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
                        <td><span class="badge badge-{{ $item->item_type }}">{{ $report::itemType($item->item_type) }}</span></td>
                        <td>
                            {{ $item->item_name }}
                            @if ($item->used_product_name !== null)
                                <br><small>Produk dipakai {{ $item->used_product_name }}</small>
                            @endif
                        </td>
                        <td class="number">{{ $item->quantity }}</td>
                        <td class="number">{{ $report::rupiah($item->unit_price) }}</td>
                        <td class="number amount">{{ $report::rupiah($item->amount) }}</td>
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
        <nav class="actions owner-pager" aria-label="Halaman pemasukan">
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