@extends('auth.owner-layout')

@section('title', 'Rekap')
@section('main-class', 'wide report-page')

@section('content')
    <p><a href="{{ route('owner.home') }}">Kembali ke area Owner</a></p>
    <h1>Rekap</h1>

    <form method="get" action="{{ route('owner.recap.index') }}">
        <div class="recap-filters">
            <div class="field">
                <label for="report-month">Bulan (WIB)</label>
                <input id="report-month" type="month" name="month" min="1000-01" max="9999-12" value="{{ $report->period }}">
                @error('month')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="report-type">Jenis rincian</label>
                <select id="report-type" name="type">
                    <option value="">Semua jenis</option>
                    @foreach ($report::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected($report->type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="report-search">Cari rincian</label>
                <input id="report-search" name="q" type="search" maxlength="255" value="{{ $report->search }}"
                    placeholder="Item, deskripsi, nama, atau ID">
                @error('q')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="actions">
            <button type="submit">Terapkan filter</button>
            @if ($report->filters() !== [])
                <a href="{{ route('owner.recap.index') }}">Hapus filter</a>
            @endif
            <a class="button secondary" href="{{ route('owner.recap.export', $report->filters()) }}">Ekspor Excel</a>
        </div>
    </form>

    <section aria-labelledby="recap-summary">
        <h2 id="recap-summary">Ringkasan · {{ $report->periodLabel() }}</h2>
        <dl class="recap-totals">
            <div>
                <dt>Total pemasukan</dt>
                <dd>{{ $report::rupiah($summary['income']) }}</dd>
            </div>
            <div>
                <dt>Total pengeluaran</dt>
                <dd>{{ $report::rupiah($summary['expenses']) }}</dd>
            </div>
            <div>
                <dt>Hasil bersih tercatat</dt>
                <dd>{{ $report::rupiah($summary['net']) }}</dd>
            </div>
        </dl>
    </section>

    <div class="table-scroll">
        <table>
            <caption>Rincian transaksi</caption>
            <thead>
                <tr>
                    <th scope="col">Tanggal (WIB)</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">ID referensi</th>
                    <th scope="col">Kapster</th>
                    <th scope="col">Pelanggan</th>
                    <th scope="col">Rincian</th>
                    <th scope="col" class="number">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td class="report-time">{{ Carbon\CarbonImmutable::parse($item->business_date)->format('d/m/Y') }}</td>
                        <td>{{ $report::TYPES[$item->entry_type] }}</td>
                        <td>{{ $report::category($item->item_type) }}</td>
                        <td>{{ $report::reference($item) }}</td>
                        <td>{{ $item->barber_name ?? '—' }}</td>
                        <td>{{ $item->customer_name ?? '—' }}</td>
                        <td>{{ $item->label }}</td>
                        <td class="number">{{ $report::rupiah($item->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada transaksi yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th scope="row" colspan="7">Total nominal rincian</th>
                    <td class="number">{{ $report::rupiah($detailTotal) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($items->hasPages())
        <nav class="actions" aria-label="Halaman rekap">
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
