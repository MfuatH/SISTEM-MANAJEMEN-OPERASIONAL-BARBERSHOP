@extends('auth.owner-area')

@section('title', 'Pengeluaran')

@section('owner-content')
    <h1>Pengeluaran</h1>
    <p>{{ $report->month->locale('id')->translatedFormat('F Y') }}</p>
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <p><a class="button" href="{{ route('owner.expenses.create') }}">Tambah pengeluaran</a></p>

    <form method="get" action="{{ route('owner.expenses.index') }}">
        <div class="field">
            <label for="search">Cari pengeluaran</label>
            <input id="search" name="q" type="search" maxlength="255" value="{{ $report->search }}"
                placeholder="ID pengeluaran atau deskripsi">
            @error('q')
                <span class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="actions">
            <button type="submit">Cari</button>
            @if ($report->search !== '')
                <a href="{{ route('owner.expenses.index') }}">Hapus pencarian</a>
            @endif
            <a class="button secondary" href="{{ route('owner.expenses.export', $report->search !== '' ? ['q' => $report->search] : []) }}">
                Ekspor Excel
            </a>
        </div>
    </form>

    <p><strong>Total {{ $report->search !== '' ? 'hasil pencarian' : 'bulan berjalan' }} {{ $report::rupiah($total) }}</strong></p>

    <div class="table-scroll">
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
                        <td class="number">{{ $report::rupiah($item->amount) }}</td>
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
        <nav class="actions" aria-label="Halaman pengeluaran">
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
