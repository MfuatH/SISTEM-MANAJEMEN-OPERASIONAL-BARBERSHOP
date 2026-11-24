{{-- Panel brand bersama: dipakai oleh beranda (welcome) dan login owner --}}
<aside class="login-brand">
    {{-- Gunting besar sebagai watermark --}}
    <svg class="login-watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/><path d="M20 4 8.12 15.88"/>
        <circle cx="6" cy="18" r="3"/><path d="M14.8 14.8 20 20"/>
    </svg>

    {{-- Ganti isi div ini dengan <img src="{{ asset('images/logo.png') }}" alt=""> jika logo Figma sudah diekspor --}}
    <div class="login-logo" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="6" cy="6" r="3"/><path d="M8.12 8.12 12 12"/><path d="M20 4 8.12 15.88"/>
            <circle cx="6" cy="18" r="3"/><path d="M14.8 14.8 20 20"/>
        </svg>
    </div>

    <p class="login-shop">Pangkas Rambut Upin Ipin 11</p>
    <p class="login-tagline">Kelola operasional pangkas rambut dengan rapi, dari kursi cukur sampai laporan.</p>

    <ul class="login-points">
        <li>Catat pemasukan dan pengeluaran harian</li>
        <li>Rekap laporan keuangan otomatis</li>
        <li>Atur katalog layanan dengan mudah</li>
    </ul>

    <span class="login-pole" aria-hidden="true"></span>
</aside>