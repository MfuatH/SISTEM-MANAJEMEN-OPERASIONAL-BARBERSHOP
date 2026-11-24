@extends('auth.owner-area')

@section('title', 'Forecasting')

@section('owner-content')
    <header>
        <h1>Forecasting</h1>
        <p>Proyeksi pemasukan kotor layanan dan penjualan produk.</p>
    </header>

    @php
        $prediction = $forecast['predictions'][0] ?? null;
        $evaluation = $forecast['evaluation'];
        $points = array_merge($forecast['actual'], $forecast['predictions']);
        $hasHistory = count($forecast['actual']) === 3;
        $monthLabel = fn ($month) => \Carbon\CarbonImmutable::parse($month.'-01', 'Asia/Jakarta')
            ->locale('id')->translatedFormat('F Y');
        $money = fn ($amount) => \App\Services\OwnerReport::rupiah($amount);
        $message = match ($forecast['status']) {
            'disabled' => 'Forecasting belum diaktifkan.',
            'processing' => 'Prediksi sedang diproses. Muat ulang halaman untuk melihat hasil.',
            'insufficient_data' => 'Prediksi memerlukan tiga bulan data lengkap yang berurutan.',
            'stale' => 'Hasil terakhir ditampilkan sambil menunggu pemutakhiran periode atau sumber.',
            'unavailable' => match ($forecast['reason_code']) {
                'negative_prediction' => 'Prediksi bernilai negatif dan perlu ditinjau.',
                'forecast_not_generated' => 'Prediksi belum dijalankan untuk periode ini.',
                default => 'Prediksi belum tersedia. Coba lagi setelah pemutakhiran berikutnya.',
            },
            default => null,
        };
    @endphp

    @if ($forecast['status'] !== 'disabled')
        <form method="post" action="{{ route('owner.forecasting.refresh') }}">
            @csrf
            <button type="submit">Perbarui prediksi</button>
        </form>
    @endif

    @if (session('forecast_refresh'))
        <p role="status">{{ session('forecast_refresh') }}</p>
    @endif

    @if ($message !== null)
        <p role="status">{{ $message }}</p>
    @endif
    @foreach ($forecast['warnings'] as $warning)
        <p>{{ $warning }}</p>
    @endforeach

    @if ($prediction !== null)
        <p><strong>Prediksi {{ $monthLabel($prediction['month']) }}:</strong> {{ $money($prediction['amount']) }}</p>
    @endif
    @if ($forecast['generated_at'] !== null)
        <p>
            Diperbarui {{ \Carbon\CarbonImmutable::parse($forecast['generated_at'])->locale('id')->translatedFormat('d M Y, H:i') }} WIB
        </p>
    @endif

    @if ($points !== [])
        <section class="dashboard-chart-section" aria-labelledby="forecast-series" @if ($hasHistory) data-dashboard-chart @endif>
            <h2 id="forecast-series">Pemasukan bulanan</h2>

            @if ($hasHistory)
                @include('owner.forecasting.chart')
            @endif

            <details @if (! $hasHistory) open @endif>
                <summary>Lihat angka per bulan</summary>
                <div class="table-scroll">
                    <table>
                        <caption>Histori dan prediksi pemasukan</caption>
                        <thead>
                            <tr>
                                <th scope="col">Bulan WIB</th>
                                <th scope="col">Jenis</th>
                                <th scope="col" class="number">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($points as $point)
                                @php($kind = isset($point['complete']) ? 'Historis' : 'Prediksi')
                                <tr>
                                    <th scope="row">{{ $monthLabel($point['month']) }}</th>
                                    <td>{{ $kind }}</td>
                                    <td class="number">{{ $money($point['amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>
    @endif

    @if ($evaluation !== null)
        <section aria-labelledby="forecast-evaluation">
            <h2 id="forecast-evaluation">Evaluasi prediksi</h2>
            @if (($evaluation['test_months'] ?? 0) > 0)
                <dl>
                    <dt>Bulan uji</dt>
                    <dd>{{ $monthLabel($evaluation['test_month']) }} ({{ $evaluation['test_months'] }} bulan uji)</dd>
                    <dt>MAE model</dt>
                    <dd>{{ $money($evaluation['mae_idr']) }}</dd>
                    <dt>Galat pembanding bulan terakhir</dt>
                    <dd>{{ $money($evaluation['baseline_mae_idr']) }}</dd>
                </dl>
                @if ($evaluation['data_kind'] === 'synthetic')
                    <p>Evaluasi menggunakan data sintetis. Akurasi lapangan belum terverifikasi.</p>
                @elseif ($evaluation['data_kind'] === 'mixed')
                    <p>Evaluasi menggunakan gabungan data aktual dan sintetis.</p>
                @endif
            @else
                <p>Evaluasi memerlukan satu bulan pembanding dari histori lengkap.</p>
            @endif
        </section>
    @endif
@endsection
