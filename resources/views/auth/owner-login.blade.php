@extends('auth.owner-layout')

@section('title', 'Masuk sebagai Owner')
@section('body-class', 'theme-dark')
@section('main-class', 'login-shell')

@section('content')
    @include('partials.brand-panel')

    {{-- Panel kanan: form login --}}
    <div class="login-form">
        <h1>Masuk sebagai Owner</h1>
        <p class="login-sub">Kelola operasional pangkas rambut</p>

        <form method="post" action="{{ route('owner.login.store') }}">
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" maxlength="80" required
                    @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
                @error('username')
                    <span class="error" id="username-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="current-password" maxlength="72" required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                    <span class="error" id="password-error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit">
                Masuk
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>
                </svg>
            </button>
        </form>

        <p class="login-back"><a href="{{ url('/') }}">Kembali ke beranda</a></p>
    </div>
@endsection