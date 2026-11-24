@extends('auth.owner-layout')

@section('main-class', 'wide report-page owner-area')

@section('content')
    <div class="owner-area-layout">
        <aside class="owner-sidebar">
            <nav aria-label="Menu Owner">
                <ul>
                    <li><a href="{{ route('owner.home') }}" @if (request()->routeIs('owner.home')) aria-current="page" @endif>Home</a></li>
                    <li><a href="{{ route('owner.income.index') }}" @if (request()->routeIs('owner.income.*')) aria-current="page" @endif>Pemasukan</a></li>
                    <li><a href="{{ route('owner.expenses.index') }}" @if (request()->routeIs('owner.expenses.*')) aria-current="page" @endif>Pengeluaran</a></li>
                    <li><a href="{{ route('owner.recap.index') }}" @if (request()->routeIs('owner.recap.*')) aria-current="page" @endif>Rekap</a></li>
                    @if (config('forecasting.enabled'))
                        <li>
                            <a href="{{ route('owner.forecasting.index') }}" @if (request()->routeIs('owner.forecasting.*')) aria-current="page" @endif>Forecasting</a>
                        </li>
                    @endif
                </ul>
            </nav>
            <form method="post" action="{{ route('owner.logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </aside>

        <div class="owner-area-content">
            @yield('owner-content')
        </div>
    </div>
@endsection
