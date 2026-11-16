@extends('auth.owner-layout')

@section('title', 'Masuk sebagai Owner')

@section('content')
    <h1>Masuk sebagai Owner</h1>

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

        <button type="submit">Masuk</button>
    </form>
    <p><a href="{{ url('/') }}">Kembali ke beranda</a></p>
@endsection
