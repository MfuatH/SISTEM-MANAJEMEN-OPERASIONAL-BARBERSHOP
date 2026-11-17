@extends('auth.owner-layout')

@section('title', 'Tambah pencatatan')

@section('content')
    <p><a href="{{ route('barber.dashboard') }}">Kembali ke dashboard</a></p>
    <h1>Tambah pencatatan</h1>

    <p>Kapster {{ session('barber_name') }}</p>
    @if ($errors->any())
        <ul class="error" role="alert">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif

    @if ($services->isEmpty())
        <p>Belum ada layanan aktif.</p>
    @else
        <form method="post" action="{{ route('barber.entries.store') }}">
            @csrf
            <input type="hidden" name="request_key" value="{{ $requestKey }}">
            <input type="hidden" name="request_signature" value="{{ $requestSignature }}">
            <input type="hidden" id="quoted-price" name="quoted_price">

            <div class="field">
                <label for="customer-name">Nama pelanggan (opsional)</label>
                <input id="customer-name" name="customer_name" maxlength="120"
                    value="{{ is_string(old('customer_name')) ? old('customer_name') : '' }}">
            </div>
            <div class="field">
                <label for="service">Layanan</label>
                <select id="service" name="service_id" required>
                    <option value="">Pilih layanan</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" data-price="Rp{{ number_format((int) $service->current_price, 0, ',', '.') }}"
                            data-amount="{{ $service->current_price }}" @selected((string) $service->id === (is_scalar(old('service_id')) ? (string) old('service_id') : ''))>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <p>Harga <output id="service-price" for="service" aria-live="polite">—</output></p>
            <noscript><p>Aktifkan JavaScript untuk melihat harga sebelum menyimpan.</p></noscript>
            <button id="save-entry" type="submit" disabled>Simpan</button>
        </form>

        <script>
            const service = document.getElementById('service');
            const price = document.getElementById('service-price');
            const quote = document.getElementById('quoted-price');
            const save = document.getElementById('save-entry');

            function showPrice() {
                const option = service.selectedOptions[0];
                price.textContent = option?.dataset.price ?? '—';
                quote.value = option?.dataset.amount ?? '';
                save.disabled = quote.value === '';
            }

            service.addEventListener('change', showPrice);
            showPrice();
        </script>
    @endif
@endsection
