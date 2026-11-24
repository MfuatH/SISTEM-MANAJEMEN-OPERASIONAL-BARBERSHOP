@extends('auth.owner-layout')

@section('title', 'Beranda')
@section('body-class', 'theme-dark')
@section('main-class', 'login-shell')

@section('content')
    @include('partials.brand-panel')

    <div class="login-form">
        <h1>Selamat Datang</h1>

        @auth
            <p class="login-sub">Kamu sudah masuk sebagai owner.</p>
            <p><a class="button btn-block" href="{{ route('owner.home') }}">Area Owner</a></p>
        @else
            <p class="login-sub">Masukkan nama untuk memulai sesi kapster</p>

            @if (session()->has('barber_name'))
                <a class="login-continue" href="{{ route('barber.dashboard') }}">
                    <span>Lanjutkan sesi</span>
                    <strong>{{ session('barber_name') }}</strong>
                </a>
            @endif

            <form method="post" action="{{ route('barber.session.store') }}">
                @csrf

                <div class="field">
                    <label for="name">Nama Kapster</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')
                        <span class="error" id="name-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit">{{ session()->has('barber_name') ? 'Mulai sesi baru' : 'Mulai sesi Kapster' }}</button>
            </form>

            <div class="login-divider"><span>atau</span></div>

            <a class="btn-outline" href="{{ route('login') }}">Masuk Owner</a>
        @endauth
    </div>
@endsection