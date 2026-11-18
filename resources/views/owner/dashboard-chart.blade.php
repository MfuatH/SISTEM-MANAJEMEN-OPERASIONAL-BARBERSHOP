@php
    $maximum = max(...array_column($days, 'income'), ...array_column($days, 'expenses'));
    $scale = max(1, (float) $maximum);
    $lastDay = $today->locale('id')->translatedFormat('j F Y');
@endphp

<section aria-labelledby="daily-chart-title">
    <h2 id="daily-chart-title">Grafik harian</h2>
    <p>{{ $today->day === 1 ? $lastDay : '1–'.$lastDay }} WIB</p>

    <ul class="dashboard-legend">
        <li><span class="dashboard-swatch dashboard-income" aria-hidden="true"></span>Pemasukan</li>
        <li><span class="dashboard-swatch dashboard-expenses" aria-hidden="true"></span>Pengeluaran</li>
    </ul>

    <div class="dashboard-chart-area">
        <div class="dashboard-axis" aria-hidden="true">
            <span>{{ (string) $maximum === '0' ? '' : App\Services\OwnerReport::rupiah($maximum) }}</span>
            <span>Rp0</span>
        </div>
        <div class="dashboard-chart-scroll" role="region" aria-label="Grafik harian, data tersedia pada tabel di bawah" tabindex="0">
            <div class="dashboard-chart" aria-hidden="true">
                @foreach ($days as $day)
                    <div class="dashboard-day">
                        <div class="dashboard-bars">
                            <span class="dashboard-bar dashboard-income"
                                style="height: {{ round((float) $day['income'] / $scale * 100, 4) }}%; min-height: {{ $day['income'] === '0' ? '0' : '2px' }}"
                                title="{{ $day['date'] }} · Pemasukan {{ App\Services\OwnerReport::rupiah($day['income']) }}"></span>
                            <span class="dashboard-bar dashboard-expenses"
                                style="height: {{ round((float) $day['expenses'] / $scale * 100, 4) }}%; min-height: {{ $day['expenses'] === '0' ? '0' : '2px' }}"
                                title="{{ $day['date'] }} · Pengeluaran {{ App\Services\OwnerReport::rupiah($day['expenses']) }}"></span>
                        </div>
                        <span>{{ substr($day['date'], -2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <details>
        <summary>Lihat angka per hari</summary>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Tanggal (WIB)</th>
                        <th scope="col" class="number">Pemasukan</th>
                        <th scope="col" class="number">Pengeluaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($days as $day)
                        <tr>
                            <th scope="row">
                                <time datetime="{{ $day['date'] }}">{{ Carbon\CarbonImmutable::parse($day['date'])->format('d/m/Y') }}</time>
                            </th>
                            <td class="number">{{ App\Services\OwnerReport::rupiah($day['income']) }}</td>
                            <td class="number">{{ App\Services\OwnerReport::rupiah($day['expenses']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</section>
