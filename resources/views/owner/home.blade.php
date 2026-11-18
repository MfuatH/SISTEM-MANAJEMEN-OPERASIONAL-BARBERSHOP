@extends('auth.owner-layout')

@section('title', 'Area Owner')
@section('main-class', 'wide report-page owner-dashboard')

@section('content')
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            <nav aria-label="Menu Owner">
                <ul>
                    <li><a href="{{ route('owner.home') }}" aria-current="page">Home</a></li>
                    <li><a href="{{ route('owner.income.index') }}">Pemasukan</a></li>
                    <li><a href="{{ route('owner.expenses.index') }}">Pengeluaran</a></li>
                    <li><a href="{{ route('owner.recap.index') }}">Rekap</a></li>
                    <li><a href="{{ route('owner.services.index') }}">Katalog layanan</a></li>
                    <li><a href="{{ route('owner.products.index') }}">Katalog produk</a></li>
                </ul>
            </nav>
            <form method="post" action="{{ route('owner.logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </aside>

        <div class="dashboard-content">
            <header>
                <h1>Area Owner</h1>
                <p>Masuk sebagai {{ auth()->user()->name }}.</p>
            </header>

            <section aria-labelledby="dashboard-period">
                <h2 id="dashboard-period">{{ $month->locale('id')->translatedFormat('F Y') }}</h2>
                <dl class="dashboard-totals">
                    <div>
                        <dt>Total pemasukan bulan berjalan</dt>
                        <dd>{{ App\Services\OwnerReport::rupiah($totals['income']) }}</dd>
                    </div>
                    <div>
                        <dt>Total pengeluaran bulan berjalan</dt>
                        <dd>{{ App\Services\OwnerReport::rupiah($totals['expenses']) }}</dd>
                    </div>
                    <div>
                        <dt>Total hasil bersih bulan berjalan</dt>
                        <dd>{{ App\Services\OwnerReport::rupiah($totals['net']) }}</dd>
                    </div>
                </dl>
            </section>

            @include('owner.dashboard-chart')
        </div>
    </div>
@endsection
