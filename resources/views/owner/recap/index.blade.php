@extends('auth.owner-area')

@section('title', 'Rekap')

@section('owner-content')
    <header class="owner-page-header">
        <h1>Rekap</h1>
    </header>

    <form class="owner-toolbar owner-toolbar-stack" method="get" action="{{ route('owner.recap.index') }}">
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
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 3H2l8 9.46V19l4 2v-8.54z"/></svg>
                Terapkan filter
            </button>
            @if ($report->filters() !== [])
                <a href="{{ route('owner.recap.index') }}">Hapus filter</a>
            @endif
            <a class="button secondary" href="{{ route('owner.recap.export', $report->filters()) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                Ekspor Excel
            </a>
        </div>
    </form>

    <section aria-labelledby="recap-summary">
        <h2 id="recap-summary">Ringkasan · {{ $report->periodLabel() }}</h2>
        <dl class="recap-totals dashboard-totals">
            <div class="stat-income">
                <dt>Total pemasukan</dt>
                <dd>{{ $report::rupiah($summary['income']) }}</dd>
            </div>
            <div class="stat-expenses">
                <dt>Total pengeluaran</dt>
                <dd>{{ $report::rupiah($summary['expenses']) }}</dd>
            </div>
            <div class="stat-net">
                <dt>Hasil bersih tercatat</dt>
                <dd>{{ $report::rupiah($summary['net']) }}</dd>
            </div>
        </dl>
    </section>

    <div class="table-scroll owner-card">
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
                        <td><span class="badge badge-{{ $item->entry_type }}">{{ $report::TYPES[$item->entry_type] }}</span></td>
                        <td>{{ $report::category($item->item_type) }}</td>
                        <td>{{ $report::reference($item) }}</td>
                        <td>{{ $item->barber_name ?? '—' }}</td>
                        <td>{{ $item->customer_name ?? '—' }}</td>
                        <td>{{ $item->label }}</td>
                        <td class="number amount">{{ $report::rupiah($item->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Belum ada transaksi yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                @foreach ($detailTotals as $label => $amount)
                    <tr>
                        <th scope="row" colspan="7">{{ $label }}</th>
                        <td class="number amount">{{ $report::rupiah($amount) }}</td>
                    </tr>
                @endforeach
            </tfoot>
        </table>
    </div>

    @if ($items->hasPages())
        <nav class="actions owner-pager" aria-label="Halaman rekap">
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