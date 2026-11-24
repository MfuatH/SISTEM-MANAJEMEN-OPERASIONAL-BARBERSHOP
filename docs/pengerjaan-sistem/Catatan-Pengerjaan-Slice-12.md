# Catatan Pengerjaan Slice 12 — Forecasting Owner

**Status** Backend Forecasting dan halaman acuan telah diuji. Tim frontend mengerjakan desain akhir pada branch `feat/integrasi-backend-frontend`.

**Fondasi** Slice 12 memakai histori Slice 11 terbaru `877891b`. Kandidat backend untuk `main` memakai komit implementasi Forecasting, beserta halaman acuan. Dependensi yang diuji adalah PHP 8.5.12, Laravel 13.42.0, MySQL 8.4.3 LTS, Python 3.14.0, dan scikit-learn 1.9.0. [Dependensi Python](../../forecasting/requirements.txt)

## Cakupan dan perilaku

1. Model `LinearRegression` menerima tiga bulan historis lengkap yang berurutan. Targetnya pemasukan kotor dari layanan dan produk pada bulan berikutnya dalam kalender WIB. Indeks latih adalah 0–2 dan indeks prediksi adalah 3.
2. Saat empat bulan lengkap tersedia, uji retrospektif membandingkan hasil model dengan bulan keempat. Bulan pembanding dipisahkan dari pelatihan uji. MAE, aktual, prediksi uji, jumlah bulan uji, dan galat pembanding sederhana diberi label sumber data.
3. Laravel menjalankan Python dengan daftar argumen tetap dan stdin JSON, membatasi proses hingga 20 detik, lalu memeriksa keluaran. Hasil dibulatkan ke rupiah utuh dengan kebijakan setengah menjauhi nol. Nilai negatif tersimpan sebagai metadata privat dan ditandai perlu ditinjau.
4. Snapshot privat dipublikasikan secara atomik di `storage/app/private/forecasting/latest.json`. Snapshot menyimpan versi model, sidik jari histori, metadata batch, input, hasil, dan evaluasi. Penguncian cache mencegah proses ganda. Refresh dengan tahun, bulan, dan sumber yang sama memakai hasil siap yang sudah ada.
5. Halaman dan JSON `/owner/forecasting` serta `/owner/forecasting/data` hanya dapat dibuka oleh Owner aktif, memakai respons kontrak v1 dan header privat tanpa cache. Feature flag awal adalah `FORECAST_ENABLED=false`. [Kontrak JSON](../../forecasting/contract/v1/README.md)

Scheduler memeriksa setiap jam. Saat bulan kalender berganti, proyeksi menunggu tiga bulan lengkap dan pilihan sumber. Konfirmasi sumber bulan terdahulu memakai proses Slice 11. Selama menunggu, halaman dapat menampilkan hasil terakhir dengan label kedaluwarsa. [Cara meninjau histori Slice 11](Catatan-Pengerjaan-Slice-11.md#cara-meninjau)

## Handoff frontend

Pemeriksaan `feature/ui-redesign` pada komit `356e19a` mencakup layout Owner, laporan, katalog, Kapster, dan beranda. Tema Modern Classic dan sidebar bisa dipakai sebagai acuan bersama. Gabungan backend dan desain tersedia di branch khusus. [Branch redesign](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feature/ui-redesign) · [Branch integrasi](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feat/integrasi-backend-frontend)

Halaman `resources/views/owner/forecasting/index.blade.php` adalah acuan fungsional. Alurnya dimulai dari judul dan status, lalu proyeksi, waktu pembaruan, grafik Batang atau Garis dan rincian, serta hasil evaluasi. Tim frontend menentukan tipografi, kartu, dan grafik akhir dengan mempertahankan perilaku berikut.

| Bagian | Acuan integrasi |
| --- | --- |
| Periode dan nilai | Saat `ready`, grafik menampilkan tiga bulan lengkap dan satu bulan prediksi. Pilih Batang atau Garis. Prediksi memakai warna berbeda dan segmen garis putus-putus. Untuk nominal penuh, buka **Lihat angka per bulan**. |
| Status | Sediakan `disabled`, `processing`, `insufficient_data`, `ready`, `stale`, dan `unavailable`. Status `stale` mempertahankan periode hasil lama. |
| Sumber dan kesegaran | Tampilkan penanda sintetis atau campuran, bulan target, serta waktu pembaruan dari respons. |
| Evaluasi | Tampilkan MAE, galat pembanding bulan terakhir, dan jumlah bulan uji jika tersedia. Jelaskan kebutuhan pembanding bila evaluasi kosong. |
| Akses | Tautan sidebar muncul saat fitur aktif. Pertahankan autentikasi Owner pada halaman dan JSON serta pembacaan kontrak versi 1. |

Gunakan enam fixture sebagai bahan pengembangan sebelum memakai data lokal. [Kontrak dan fixture](../../forecasting/contract/v1/README.md)

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

Masuk sebagai Owner, buka **Forecasting**, dan cocokkan angka layar dengan `/owner/forecasting/data`. Uji pilihan **Batang** dan **Garis**, urutan tiga bulan historis lalu satu prediksi, dan nominal pada **Lihat angka per bulan**. Jika prediksi belum tersedia, grafik hanya menampilkan histori ketika tiga bulan lengkap tersedia. Hasil contoh November 2026 berlaku saat target adalah November dan sumber Agustus–Oktober telah dipilih. Untuk terminal scheduler terpisah, letakkan pengaturan di `.env` lokal dan jalankan `php artisan schedule:work`. Cek jadwal melalui `php artisan schedule:list`.

## Bukti pengujian

| Pengujian | Hasil |
| --- | --- |
| Model nyata melalui Laravel | Agustus–Oktober 2026 menghasilkan proyeksi November **Rp8.573.667** pada `barbershop_test`. Refresh kedua memakai snapshot yang sama. |
| Uji retrospektif satu bulan | Prediksi Oktober dari Juli–September **Rp8.502.000** terhadap aktual sintetis **Rp8.792.000**. MAE **Rp290.000**. Pembanding memakai Rp8.573.000 dari September dan menghasilkan galat **Rp219.000**. |
| Tes regresi | Kandidat backend lulus **71 tes dan 975 assertion** pada MySQL 8.4.3. Rangkaian tes meliputi akses Owner, enam status, jendela latih dan uji, output Python rusak, timeout, prediksi negatif, penguncian, navigasi, serta tampilan acuan. |
| Revisi grafik bulanan | 71 tes Laravel dan 979 assertion lulus di MySQL 8.4.3. Uji JavaScript memeriksa pilihan Batang/Garis, empat titik Forecasting, segmen prediksi putus-putus, dan tombol seri Home. |
| Jalur Python Windows | Laravel berhasil menjalankan Python dari jalur berspasi. Keluaran untuk fixture adalah Rp5.750.000. |
| Jadwal dan format | `schedule:list` mencantumkan refresh per jam, Pint dan `git diff --check` lulus. |

Bukti sintetis menunjukkan alur model dan evaluasi berfungsi. Ketepatan di lapangan dan ambang MAE bisnis masih perlu ditetapkan dari data aktual dan beberapa periode uji. [Rencana backend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap) · [Desain penyimpanan hasil](Desain-Database-MySQL-Barbershop.md#penyimpanan-hasil-slice-12)
