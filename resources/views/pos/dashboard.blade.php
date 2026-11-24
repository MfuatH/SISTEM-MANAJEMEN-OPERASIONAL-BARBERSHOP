@extends('auth.owner-layout')

@section('title', 'Dashboard Kapster')
@section('body-class', 'theme-dark pos')
@section('main-class', 'pos-page')

@section('content')
    <header class="pos-topbar">
        <div class="pos-brand">
            <span class="pos-logo" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/><path d="M20 4 8.12 15.88"/>
                    <circle cx="6" cy="18" r="3"/><path d="M14.8 14.8 20 20"/>
                </svg>
            </span>
            <span class="pos-brand-name">Pangkas Rambut Upin Ipin 11</span>
        </div>

        <div class="pos-user">
            <span class="pos-chip">
                <span class="pos-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr((string) session('barber_name'), 0, 1)) }}</span>
                {{ session('barber_name') }}
            </span>
            <form method="post" action="{{ route('barber.session.destroy') }}">
                @csrf
                <button class="secondary" type="submit">Akhiri sesi</button>
            </form>
        </div>
    </header>

    <div class="pos-heading">
        <h1>Dashboard Kapster</h1>
        <p>Pilih jenis pencatatan yang ingin kamu input.</p>
    </div>

    @if (session('status'))
        <p class="pos-alert" role="status">{{ session('status') }}</p>
    @endif

    <div class="pos-actions">
        <a class="pos-action" href="{{ route('barber.entry-form') }}">
            <span class="pos-action-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/><path d="M20 4 8.12 15.88"/>
                    <circle cx="6" cy="18" r="3"/><path d="M14.8 14.8 20 20"/>
                </svg>
            </span>
            <span class="pos-action-text">
                <strong>Layanan</strong>
                <small>Catat layanan yang dikerjakan</small>
            </span>
            <svg class="pos-action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </a>

        <a class="pos-action" href="{{ route('barber.product-form') }}">
            <span class="pos-action-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </span>
            <span class="pos-action-text">
                <strong>Produk</strong>
                <small>Catat penjualan produk</small>
            </span>
            <svg class="pos-action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </a>
    </div>

    <section class="pos-card">
        <div class="table-scroll">
            <table>
                <caption>Pencatatan hari ini</caption>
                <thead>
                    <tr>
                        <th scope="col">Transaksi</th>
                        <th scope="col">Waktu (WIB)</th>
                        <th scope="col">Nama pelanggan</th>
                        <th scope="col">Item</th>
                        <th scope="col" class="number">Jumlah</th>
                        <th scope="col" class="number">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->transaction_id }}</td>
                            <td class="report-time">{{ Illuminate\Support\Carbon::parse($entry->recorded_at, 'UTC')->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                            <td>{{ $entry->customer_name ?? '—' }}</td>
                            <td>
                                {{ $entry->item_name }}
                                @if ($entry->item_type === 'product')
                                    <br><small>Produk dijual</small>
                                @elseif ($entry->used_product_name !== null)
                                    <br><small>Produk dipakai {{ $entry->used_product_name }}</small>
                                @endif
                            </td>
                            <td class="number">{{ $entry->quantity }}</td>
                            <td class="number amount">Rp{{ preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $entry->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada pencatatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($entries->hasPages())
        <nav class="actions pos-pager" aria-label="Halaman pencatatan">
            @if ($entries->previousPageUrl())
                <a href="{{ $entries->previousPageUrl() }}">Sebelumnya</a>
            @endif
            @if ($entries->nextPageUrl())
                <a href="{{ $entries->nextPageUrl() }}">Berikutnya</a>
            @endif
        </nav>
    @endif
@endsection