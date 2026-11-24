@extends('auth.owner-catalog')

@section('title', $service->exists ? 'Ubah layanan' : 'Tambah layanan')

@section('catalog-content')
    <p>
        <a class="back-link" href="{{ route('owner.services.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke katalog
        </a>
    </p>
    <h1>{{ $service->exists ? 'Ubah layanan' : 'Tambah layanan' }}</h1>

    <form class="catalog-form" method="post" action="{{ $service->exists ? route('owner.services.update', $service) : route('owner.services.store') }}">
        @csrf
        @if ($service->exists)
            @method('PUT')
        @endif

        <div class="field">
            <label for="name">Nama layanan</label>
            <input id="name" name="name" maxlength="120" required value="{{ old('name', $service->name) }}"
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')
                <span class="error" id="name-error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="current_price">Harga (Rp)</label>
            <input id="current_price" name="current_price" type="number" min="1" max="999999999999999" step="1" required
                value="{{ old('current_price', $service->current_price) }}"
                @error('current_price') aria-invalid="true" aria-describedby="price-error" @enderror>
            @error('current_price')
                <span class="error" id="price-error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Simpan</button>
    </form>
@endsection