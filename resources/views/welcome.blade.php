@extends('auth.owner-layout')

@section('title', 'Beranda')

@section('content')
    <h1>Barbershop</h1>

    @auth
        <p><a class="button" href="{{ route('owner.home') }}">Area Owner</a></p>
    @else
        @if (session()->has('barber_name'))
            <p><a href="{{ route('barber.dashboard') }}">Lanjutkan sesi {{ session('barber_name') }}</a></p>
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

        <p><a href="{{ route('login') }}">Masuk Owner</a></p>
    @endauth
@endsection
