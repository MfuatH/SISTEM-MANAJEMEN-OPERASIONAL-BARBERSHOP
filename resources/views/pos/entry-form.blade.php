@extends('auth.owner-layout')

@section('title', 'Pratinjau pencatatan')

@section('content')
    <p><a href="{{ route('barber.dashboard') }}">Kembali ke dashboard</a></p>
    <h1>Pratinjau pencatatan</h1>

    <p>{{ session('barber_name') }}</p>

    @if ($services->isEmpty())
        <p>Belum ada layanan aktif.</p>
    @else
        <div class="field">
            <label for="service">Layanan</label>
            <select id="service">
                <option value="">Pilih layanan</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" data-price="Rp{{ number_format((int) $service->current_price, 0, ',', '.') }}">
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <p>Harga <output id="service-price" for="service" aria-live="polite">—</output></p>

        <script>
            const service = document.getElementById('service');
            const price = document.getElementById('service-price');

            function showPrice() {
                price.textContent = service.selectedOptions[0]?.dataset.price ?? '—';
            }

            service.addEventListener('change', showPrice);
            showPrice();
        </script>
    @endif
@endsection
