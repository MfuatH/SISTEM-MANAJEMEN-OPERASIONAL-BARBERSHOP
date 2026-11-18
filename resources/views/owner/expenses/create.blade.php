@extends('auth.owner-area')

@section('title', 'Tambah pengeluaran')

@section('owner-content')
    <p><a href="{{ route('owner.expenses.index') }}">Kembali ke Pengeluaran</a></p>
    <h1>Tambah pengeluaran</h1>

    <form method="post" action="{{ route('owner.expenses.store') }}">
        @csrf
        <div class="field">
            <label for="incurred-on">Tanggal pengeluaran</label>
            <input id="incurred-on" type="date" name="incurred_on" min="1000-01-01" max="9999-12-31" required
                value="{{ is_string(old('incurred_on', $today)) ? old('incurred_on', $today) : $today }}"
                @error('incurred_on') aria-invalid="true" aria-describedby="incurred-date-error" @enderror>
            @error('incurred_on')
                <span id="incurred-date-error" class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="3" maxlength="255" required
                @error('description') aria-invalid="true" aria-describedby="expense-description-error" @enderror>{{ is_string(old('description')) ? old('description') : '' }}</textarea>
            @error('description')
                <span id="expense-description-error" class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="amount">Jumlah (Rp)</label>
            <input id="amount" type="number" name="amount" min="1" max="999999999999999" step="1" required
                value="{{ is_scalar(old('amount')) ? old('amount') : '' }}"
                @error('amount') aria-invalid="true" aria-describedby="expense-amount-error" @enderror>
            @error('amount')
                <span id="expense-amount-error" class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        @error('request_key')
            <p class="error" role="alert">{{ $message }}</p>
        @enderror
        <input type="hidden" name="request_key" value="{{ $requestKey }}">
        <button type="submit">Simpan</button>
    </form>
@endsection
