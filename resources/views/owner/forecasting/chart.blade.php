@php
    $maximum = max(array_column($points, 'amount'));
    $scale = max(1, (float) $maximum);
@endphp

<fieldset class="dashboard-chart-types" data-chart-controls hidden>
    <legend>Jenis grafik</legend>
    <label><input type="radio" name="forecast-chart-type" value="bar" checked> Batang</label>
    <label><input type="radio" name="forecast-chart-type" value="line"> Garis (Line Chart)</label>
</fieldset>

<ul class="dashboard-legend" data-chart-legend>
    <li><span class="dashboard-swatch dashboard-income" aria-hidden="true"></span>Historis</li>
    @if ($prediction !== null)
        <li><span class="dashboard-swatch dashboard-expenses" aria-hidden="true"></span>Prediksi</li>
    @endif
</ul>

<div class="dashboard-chart-area" data-chart-area>
    <div class="dashboard-axis" aria-hidden="true">
        <span>{{ (string) $maximum === '0' ? '' : $money($maximum) }}</span>
        <span>Rp0</span>
    </div>
    <div class="dashboard-chart-scroll" role="region" aria-label="Grafik bulanan, data tersedia pada tabel di bawah" tabindex="0">
        <div class="dashboard-chart" data-chart-plot data-scale="{{ $scale }}" data-fixed-series="income" aria-hidden="true">
            <canvas class="dashboard-line" hidden></canvas>
            @foreach ($points as $point)
                @php
                    $isProjection = ! isset($point['complete']);
                    $label = $monthLabel($point['month']);
                    $kind = $isProjection ? 'Prediksi' : 'Historis';
                @endphp
                <div class="dashboard-day" data-chart-day data-month="{{ $point['month'] }}" data-income="{{ $point['amount'] }}"
                    @if ($isProjection) data-projection @endif>
                    <div class="dashboard-bars">
                        <span @class(['dashboard-bar', 'dashboard-income' => ! $isProjection, 'dashboard-expenses' => $isProjection])
                            style="height: {{ round((float) $point['amount'] / $scale * 100, 4) }}%; min-height: {{ $point['amount'] === '0' ? '0' : '2px' }}"
                            title="{{ $label }} · {{ $kind }} {{ $money($point['amount']) }}"></span>
                    </div>
                    <time datetime="{{ $point['month'] }}">{{ $label }}</time>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/owner-dashboard.js') }}" defer></script>
@endpush
