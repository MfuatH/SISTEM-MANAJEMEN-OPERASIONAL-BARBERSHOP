@extends('auth.owner-area')

@section('title', 'Pengeluaran')

@section('owner-content')
    <header class="owner-page-header">
        <h1>Pengeluaran</h1>
        <p>{{ $report->month->locale('id')->translatedFormat('F Y') }}</p>
    </header>
    @if (session('status'))
        <p class="owner-alert" role="status">{{ session('status') }}</p>
    @endif

    <p>
        <a class="button owner-add" href="{{ route('owner.expenses.create') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah pengeluaran
        </a>
    </p>

    <form class="owner-toolbar" method="get" action="{{ route('owner.expenses.index') }}">
        <div class="field">
            <label for="search">Cari pengeluaran</label>
            <input id="search" name="q" type="search" maxlength="255" value="{{ $report->search }}"
                placeholder="ID pengeluaran atau deskripsi">
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
                <a href="{{ route('owner.expenses.index') }}">Hapus pencarian</a>
            @endif
            <a class="button secondary" href="{{ route('owner.expenses.export', $report->search !== '' ? ['q' => $report->search] : []) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                Ekspor Excel
            </a>
        </div>
    </form>

    <p class="owner-total owner-total--expenses"><strong>Total {{ $report->search !== '' ? 'hasil pencarian' : 'bulan berjalan' }} <span class="owner-total-value">{{ $report::rupiah($total) }}</span></strong></p>

    <div class="table-scroll owner-card">
        <table aria-label="Riwayat pengeluaran">
            <thead>
                <tr>
                    <th scope="col">ID pengeluaran</th>
                    <th scope="col">Tanggal (WIB)</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col" class="number">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ App\Models\Expense::reference($item->id) }}</td>
                        <td class="report-time">{{ Carbon\CarbonImmutable::parse($item->incurred_on, $report::TIMEZONE)->format('d/m/Y') }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="number amount">{{ $report::rupiah($item->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ $report->search !== '' ? 'Tidak ada pengeluaran yang sesuai dengan pencarian.' : 'Belum ada pengeluaran bulan ini.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <nav class="actions owner-pager" aria-label="Halaman pengeluaran">
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