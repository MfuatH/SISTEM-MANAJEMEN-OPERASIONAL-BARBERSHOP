@extends('auth.owner-layout')

@section('body-class', 'bg-dark')
@section('main-class', 'wide report-page owner-area')

@section('content')
    <div class="owner-area-layout">
        <aside class="owner-sidebar">
            <div>
                <div class="owner-brand">
                    <span class="owner-brand-logo" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/><path d="M20 4 8.12 15.88"/>
                            <circle cx="6" cy="18" r="3"/><path d="M14.8 14.8 20 20"/>
                        </svg>
                    </span>
                    <span>Upin Ipin 11</span>
                </div>

                <nav aria-label="Menu Owner">
                    <ul>
                        <li>
                            <a href="{{ route('owner.home') }}" @if (request()->routeIs('owner.home')) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('owner.income.index') }}" @if (request()->routeIs('owner.income.*')) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></svg>
                                Pemasukan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('owner.expenses.index') }}" @if (request()->routeIs('owner.expenses.*')) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 17-8.5-8.5-5 5L2 7"/><path d="M16 17h6v-6"/></svg>
                                Pengeluaran
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('owner.recap.index') }}" @if (request()->routeIs('owner.recap.*')) aria-current="page" @endif>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5z"/><path d="M14 2v6h6"/><path d="M8 18v-3"/><path d="M12 18v-6"/><path d="M16 18v-1"/></svg>
                                Rekap
                            </a>
                        </li>
                        @if (config('forecasting.enabled'))
                            <li>
                                <a href="{{ route('owner.forecasting.index') }}" @if (request()->routeIs('owner.forecasting.*')) aria-current="page" @endif>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                    Forecasting
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>

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