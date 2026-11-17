@extends('auth.owner-layout')

@section('title', 'Dashboard Kapster')

@section('content')
    <p><a href="{{ route('home') }}">Kembali ke beranda</a></p>
    <h1>Dashboard Kapster</h1>
    <p>{{ session('barber_name') }}</p>

    <p><a class="button" href="{{ route('barber.entry-form') }}">Tambah</a></p>

    <table>
        <caption>Pencatatan sesi ini</caption>
        <thead>
            <tr>
                <th scope="col">Layanan</th>
                <th scope="col">Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2">Belum ada pencatatan.</td></tr>
        </tbody>
    </table>

    <form method="post" action="{{ route('barber.session.destroy') }}">
        @csrf
        <p><button class="secondary" type="submit">Akhiri sesi</button></p>
    </form>
@endsection
