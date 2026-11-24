@extends('auth.owner-area')

@section('title', 'Area Owner')

@section('owner-content')
    <header class="owner-page-header">
        <h1>Area Owner</h1>
        <p>Masuk sebagai {{ auth()->user()->name }}.</p>
    </header>

    <section aria-labelledby="dashboard-period">
        <h2 id="dashboard-period">{{ $month->locale('id')->translatedFormat('F Y') }}</h2>
        <dl class="dashboard-totals">
            <div class="stat-income">
                <dt>Total pemasukan bulan berjalan</dt>
                <dd>{{ App\Services\OwnerReport::rupiah($totals['income']) }}</dd>
            </div>
            <div class="stat-expenses">
                <dt>Total pengeluaran bulan berjalan</dt>
                <dd>{{ App\Services\OwnerReport::rupiah($totals['expenses']) }}</dd>
            </div>
            <div class="stat-net">
                <dt>Total hasil bersih bulan berjalan</dt>
                <dd>{{ App\Services\OwnerReport::rupiah($totals['net']) }}</dd>
            </div>
        </dl>
    </section>

    @include('owner.dashboard-chart')
@endsection