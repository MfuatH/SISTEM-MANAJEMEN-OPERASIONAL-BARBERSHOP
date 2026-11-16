@extends('auth.owner-layout')

@section('title', 'Area Owner')

@section('content')
    <h1>Area Owner</h1>
    <p>Masuk sebagai {{ auth()->user()->name }}.</p>
    <p><a href="{{ route('owner.services.index') }}">Katalog layanan</a></p>

    <form method="post" action="{{ route('owner.logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>
@endsection
