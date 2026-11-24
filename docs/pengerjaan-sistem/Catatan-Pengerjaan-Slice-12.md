# Catatan Pengerjaan Slice 12 — Forecasting Owner

**Status** Implementasi, pengujian, dan penggabungan desain selesai di `feat/slice-12-forecasting`. Siap review sebelum masuk `main`.

**Fondasi** Slice 12 dibuat dari komit Slice 11 `877891b` karena [PR #17](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/17) masih dalam review. Proyek memakai PHP 8.5.12, Laravel 13.42.0, MySQL 8.4.3 LTS, Python 3.14.0, dan scikit-learn 1.9.0. [Dependensi Python](../../forecasting/requirements.txt)

## Cakupan dan perilaku

1. Model `LinearRegression` menerima tiga bulan historis lengkap yang berurutan. Targetnya pemasukan kotor dari layanan dan produk pada bulan berikutnya dalam kalender WIB. Indeks latih adalah 0–2 dan indeks prediksi adalah 3.
2. Saat empat bulan lengkap tersedia, uji retrospektif membandingkan hasil model dengan bulan keempat. Bulan pembanding dipisahkan dari pelatihan uji. MAE, aktual, prediksi uji, jumlah bulan uji, dan galat pembanding sederhana diberi label sumber data.
3. Laravel menjalankan Python dengan daftar argumen tetap dan stdin JSON, membatasi proses hingga 20 detik, lalu memeriksa keluaran. Hasil dibulatkan ke rupiah utuh dengan kebijakan setengah menjauhi nol. Nilai negatif tersimpan sebagai metadata privat dan ditandai perlu ditinjau.
4. Snapshot privat dipublikasikan secara atomik di `storage/app/private/forecasting/latest.json`. Snapshot menyimpan versi model, sidik jari histori, metadata batch, input, hasil, dan evaluasi. Penguncian cache mencegah proses ganda. Refresh dengan tahun, bulan, dan sumber yang sama memakai hasil siap yang sudah ada.
5. Halaman dan JSON `/owner/forecasting` serta `/owner/forecasting/data` hanya dapat dibuka oleh Owner aktif, memakai respons kontrak v1 dan header privat tanpa cache. Feature flag awal adalah `FORECAST_ENABLED=false`. [Kontrak JSON](../../forecasting/contract/v1/README.md)

Scheduler memeriksa setiap jam. Saat bulan kalender berganti, proyeksi menunggu tiga bulan lengkap dan pilihan sumber. Konfirmasi sumber bulan terdahulu memakai proses Slice 11. Selama menunggu, halaman dapat menampilkan hasil terakhir dengan label kedaluwarsa. [Cara meninjau histori Slice 11](Catatan-Pengerjaan-Slice-11.md#cara-meninjau)

## Integrasi desain

Branch `feature/ui-redesign` ditinjau pada komit `356e19a`. Perubahannya mencakup 17 file Blade, termasuk layout Owner, halaman laporan, katalog, Kapster, dan beranda. Desain Modern Classic cocok karena mempertahankan rute serta data yang dipakai halaman dan sudah menyediakan sidebar, kartu statistik, serta panel grafik yang konsisten.

Merge `c7faf44` memasukkan `feature/ui-redesign` ke `feat/slice-12-forecasting`. Halaman Forecasting memakai layout Owner yang sama, dengan kartu ringkasan, batang emas untuk histori, batang burgundy bergaris untuk prediksi, dan tabel nominal. Menu Forecasting muncul saat feature flag aktif. [Branch redesign](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feature/ui-redesign) · [Commit merge](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/c7faf44)

## Cara meninjau

Gunakan database pengembangan dan selesaikan migrasi serta impor paket dari panduan Slice 11. Perintah berikut mengaktifkan Forecasting untuk sesi PowerShell yang sama.

```powershell
python -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r forecasting/requirements.txt
$env:FORECAST_ENABLED='true'
$env:FORECAST_PYTHON=(Resolve-Path -LiteralPath .\.venv\Scripts\python.exe).Path
php artisan config:clear
php artisan forecast:refresh
php artisan serve --host=127.0.0.1 --port=8000
```

Masuk sebagai Owner, buka **Forecasting**, dan cocokkan angka layar dengan `/owner/forecasting/data`. Hasil contoh November 2026 berlaku saat target adalah November dan sumber Agustus–Oktober telah dipilih. Untuk terminal scheduler terpisah, letakkan pengaturan di `.env` lokal dan jalankan `php artisan schedule:work`. Cek jadwal melalui `php artisan schedule:list`.

## Bukti pengujian

| Pengujian | Hasil |
| --- | --- |
| Model nyata melalui Laravel | Agustus–Oktober 2026 menghasilkan proyeksi November **Rp8.573.667** pada `barbershop_test`. Refresh kedua memakai snapshot yang sama. |
| Uji retrospektif satu bulan | Prediksi Oktober dari Juli–September **Rp8.502.000** terhadap aktual sintetis **Rp8.792.000**. MAE **Rp290.000**. Pembanding memakai Rp8.573.000 dari September dan menghasilkan galat **Rp219.000**. |
| Tes regresi branch gabungan | **71 tes dan 973 assertion** lulus di MySQL 8.4.3. Tes mencakup akses Owner aktif, keenam status kontrak, jendela latih dan uji, output Python rusak, timeout, prediksi negatif, penguncian, sidebar aktif, dan stylesheet Forecasting. |
| Jalur Python Windows | Laravel berhasil menjalankan Python dari jalur berspasi. Keluaran untuk fixture adalah Rp5.750.000. |
| Jadwal dan format | `schedule:list` mencantumkan refresh per jam, Pint dan `git diff --check` lulus. |

Bukti sintetis menunjukkan alur model dan evaluasi berfungsi. Ketepatan di lapangan dan ambang MAE bisnis masih perlu ditetapkan dari data aktual dan beberapa periode uji. [Rencana backend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap) · [Desain penyimpanan hasil](Desain-Database-MySQL-Barbershop.md#penyimpanan-hasil-slice-12)
