@extends('auth.owner-area')

@section('title', 'Forecasting')

@section('owner-content')
    <header class="owner-page-header">
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
        <form class="forecast-refresh" method="post" action="{{ route('owner.forecasting.refresh') }}">
            @csrf
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                Perbarui prediksi
            </button>
        </form>
    @endif

    @if (session('forecast_refresh'))
        <p class="owner-alert" role="status">{{ session('forecast_refresh') }}</p>
    @endif

    @if ($message !== null)
        <p class="owner-alert owner-alert-warn" role="status">{{ $message }}</p>
    @endif
    @foreach ($forecast['warnings'] as $warning)
        <p class="owner-alert owner-alert-warn">{{ $warning }}</p>
    @endforeach

    @if ($prediction !== null)
        <p class="owner-total"><strong>Prediksi {{ $monthLabel($prediction['month']) }}: <span class="owner-total-value">{{ $money($prediction['amount']) }}</span></strong></p>
    @endif
    @if ($forecast['generated_at'] !== null)
        <p class="owner-meta">
            Diperbarui {{ \Carbon\CarbonImmutable::parse($forecast['generated_at'])->locale('id')->translatedFormat('d M Y, H:i') }} WIB
        </p>
    @endif

    @if ($points !== [])
        <section class="dashboard-chart-section forecast-chart" aria-labelledby="forecast-series" @if ($hasHistory) data-dashboard-chart @endif>
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
                                    <td><span class="badge {{ $kind === 'Historis' ? 'badge-income' : 'badge-expense' }}">{{ $kind }}</span></td>
                                    <td class="number amount">{{ $money($point['amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>
    @endif

    @if ($evaluation !== null)
        <section class="forecast-evaluation" aria-labelledby="forecast-evaluation">
            <h2 id="forecast-evaluation">Evaluasi prediksi</h2>
            @if (($evaluation['test_months'] ?? 0) > 0)
                <dl class="dashboard-totals">
                    <div>
                        <dt>Bulan uji</dt>
                        <dd>{{ $monthLabel($evaluation['test_month']) }} ({{ $evaluation['test_months'] }} bulan uji)</dd>
                    </div>
                    <div class="stat-income">
                        <dt>MAE model</dt>
                        <dd>{{ $money($evaluation['mae_idr']) }}</dd>
                    </div>
                    <div class="stat-expenses">
                        <dt>Galat pembanding bulan terakhir</dt>
                        <dd>{{ $money($evaluation['baseline_mae_idr']) }}</dd>
                    </div>
                </dl>
                @if ($evaluation['data_kind'] === 'synthetic')
                    <p class="owner-alert owner-alert-warn">Evaluasi menggunakan data sintetis. Akurasi lapangan belum terverifikasi.</p>
                @elseif ($evaluation['data_kind'] === 'mixed')
                    <p class="owner-alert owner-alert-warn">Evaluasi menggunakan gabungan data aktual dan sintetis.</p>
                @endif
            @else
                <p class="owner-meta">Evaluasi memerlukan satu bulan pembanding dari histori lengkap.</p>
            @endif
        </section>
    @endif
@endsection