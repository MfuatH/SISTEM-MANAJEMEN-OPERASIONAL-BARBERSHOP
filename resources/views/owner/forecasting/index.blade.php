@extends('auth.owner-area')

@section('title', 'Forecasting')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/owner-forecasting.css') }}">
@endpush

@section('owner-content')
    <header class="owner-page-header forecast-header">
        <h1>Forecasting</h1>
        <p>Proyeksi pemasukan kotor bulanan dari layanan dan penjualan produk.</p>
    </header>

    @php
        $status = $forecast['status'];
        $predictions = $forecast['predictions'];
        $points = array_merge($forecast['actual'], $predictions);
        $maximum = max([1, ...array_map(fn ($point) => (float) $point['amount'], $points)]);
        $monthLabel = fn ($month) => \Carbon\CarbonImmutable::parse($month.'-01', 'Asia/Jakarta')
            ->locale('id')->translatedFormat('M Y');
        $money = fn ($amount) => \App\Services\OwnerReport::rupiah($amount);
        $message = match ($status) {
            'disabled' => 'Forecasting belum diaktifkan.',
            'processing' => 'Prediksi sedang diproses. Muat ulang halaman untuk melihat hasil.',
            'insufficient_data' => 'Prediksi memerlukan tiga bulan data lengkap yang berurutan.',
            'stale' => 'Ini hasil terakhir. Data atau periode terkini menunggu pemutakhiran.',
            'unavailable' => match ($forecast['reason_code']) {
                'negative_prediction' => 'Prediksi bernilai negatif dan perlu ditinjau.',
                'forecast_not_generated' => 'Prediksi belum dijalankan untuk periode ini.',
                default => 'Prediksi belum tersedia. Coba lagi setelah pemutakhiran berikutnya.',
            },
            default => null,
        };
    @endphp

    @if ($message !== null)
        <p class="forecast-message" role="status">{{ $message }}</p>
    @endif

    @foreach ($forecast['warnings'] as $warning)
        <p class="forecast-message">{{ $warning }}</p>
    @endforeach

    @if ($points !== [])
        @if ($predictions !== [])
            <dl class="dashboard-totals">
                <div class="stat-income">
                    <dt>Prediksi {{ $monthLabel($predictions[0]['month']) }}</dt>
                    <dd>{{ $money($predictions[0]['amount']) }}</dd>
                </div>
                @if (($forecast['evaluation']['mae_idr'] ?? null) !== null)
                    <div>
                        <dt>MAE uji {{ $monthLabel($forecast['evaluation']['test_month']) }}</dt>
                        <dd>{{ $money($forecast['evaluation']['mae_idr']) }}</dd>
                    </div>
                @endif
            </dl>
        @endif

        <section class="dashboard-chart-section forecast-panel" aria-labelledby="forecast-series">
            <h2 id="forecast-series">Pemasukan per bulan</h2>
            @if ($forecast['generated_at'] !== null)
                <p class="forecast-caption">
                    Diperbarui {{ \Carbon\CarbonImmutable::parse($forecast['generated_at'])->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                </p>
            @endif

            <ul class="dashboard-legend" aria-hidden="true">
                <li><span class="dashboard-swatch forecast-swatch-actual"></span>Historis</li>
                @if ($predictions !== [])
                    <li><span class="dashboard-swatch forecast-swatch-prediction"></span>Prediksi</li>
                @endif
            </ul>

            <div class="forecast-plot" aria-hidden="true">
                @foreach ($points as $point)
                    <div class="forecast-column">
                        <div class="forecast-track">
                            <span
                                @class(['forecast-bar', 'forecast-bar-prediction' => ! isset($point['complete'])])
                                style="--forecast-height: {{ 100 * (float) $point['amount'] / $maximum }}%"
                            ></span>
                        </div>
                        <span>{{ $monthLabel($point['month']) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="table-scroll">
                <table>
                    <caption>Rincian pemasukan dan proyeksi</caption>
                    <thead>
                        <tr><th scope="col">Bulan WIB</th><th scope="col">Jenis</th><th scope="col" class="number">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($points as $point)
                            <tr>
                                <th scope="row">{{ $monthLabel($point['month']) }}</th>
                                <td>{{ isset($point['complete']) ? 'Historis' : 'Prediksi' }}</td>
                                <td class="number">{{ $money($point['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (($forecast['evaluation']['test_months'] ?? 0) > 0)
                <p class="forecast-caption">
                    Evaluasi {{ $forecast['evaluation']['data_kind'] === 'synthetic' ? 'sintetis ' : '' }}memakai
                    {{ $forecast['evaluation']['test_months'] }} bulan uji. Ketepatan lapangan belum terverifikasi.
                </p>
            @endif
        </section>
    @endif
@endsection
