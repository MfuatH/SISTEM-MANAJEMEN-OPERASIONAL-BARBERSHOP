# Catatan Pengerjaan Slice 12 — Forecasting Owner

**Status** Backend Forecasting dan halaman acuan telah diuji. Tim frontend mengerjakan desain akhir pada branch `feat/integrasi-backend-frontend`.

**Fondasi** Slice 12 memakai histori Slice 11 terbaru `877891b`. Kandidat backend untuk `main` memakai komit implementasi Forecasting, beserta halaman acuan. Dependensi yang diuji adalah PHP 8.5.12, Laravel 13.42.0, MySQL 8.4.3 LTS, Python 3.14.0, dan scikit-learn 1.9.0. [Dependensi Python](../../forecasting/requirements.txt)

## Cakupan dan perilaku

1. Model `LinearRegression` menerima tiga bulan historis lengkap yang berurutan. Targetnya pemasukan kotor dari layanan dan produk pada bulan berikutnya dalam kalender WIB. Indeks latih adalah 0–2 dan indeks prediksi adalah 3.
2. Saat empat bulan lengkap tersedia, uji retrospektif membandingkan hasil model dengan bulan keempat. Bulan pembanding dipisahkan dari pelatihan uji. MAE, aktual, prediksi uji, jumlah bulan uji, dan galat pembanding sederhana diberi label sumber data.
3. Laravel menjalankan Python dengan daftar argumen tetap dan stdin JSON, membatasi proses hingga 20 detik, lalu memeriksa keluaran. Hasil dibulatkan ke rupiah utuh dengan kebijakan setengah menjauhi nol. Nilai negatif tersimpan sebagai metadata privat dan ditandai perlu ditinjau.
4. Snapshot privat dipublikasikan secara atomik di `storage/app/private/forecasting/latest.json`. Snapshot menyimpan versi model, sidik jari histori, metadata batch, input, hasil, dan evaluasi. Penguncian cache mencegah proses ganda. Refresh dengan tahun, bulan, dan sumber yang sama memakai hasil siap yang sudah ada.
5. Halaman dan JSON `/owner/forecasting` serta `/owner/forecasting/data` hanya dapat dibuka oleh Owner aktif, memakai respons kontrak v1 dan header privat tanpa cache. Forecasting aktif secara bawaan. Tombol **Perbarui prediksi** memakai `POST /owner/forecasting/refresh` dengan CSRF dan pembatas permintaan. [Kontrak JSON](../../forecasting/contract/v1/README.md)

Scheduler memeriksa setiap jam melalui `forecast:refresh --monthly` dan menerbitkan satu hasil siap secara otomatis per bulan target. Bulan WIB yang sudah selesai dengan transaksi aplikasi dibaca otomatis. Pilihan eksplisit masih berlaku untuk batch impor atau konfirmasi Rp0. Jika data berubah setelah terbit, Owner dapat klik **Perbarui prediksi** untuk menghitung kembali pada bulan yang sama. [Cara meninjau histori Slice 11](Catatan-Pengerjaan-Slice-11.md#cara-meninjau)

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

### Demo dengan data sintetis

Jalankan migrasi dan `php artisan forecast:demo-import slice11-synthetic-20261124` setelah membuat paket dari generator. Gunakan database lokal khusus demo. Verifikasi transaksi **(Demo)** di Rekap Juli–Oktober 2026 dan pastikan sumber Forecasting berlabel sintetis. Proyeksi **Rp8.573.667** berlaku untuk target demo November 2026. [Langkah demo di README](../../README.md#1a-forecasting-demo-dengan-data-sintetis)

### Penggunaan dengan data operasional

Gunakan catatan transaksi asli pada database operasional. Cek tiga bulan WIB terakhir yang sudah tutup melalui Rekap dan `php artisan forecast:history-window`. Pastikan `data_kind` bernilai `actual` sebelum memakai hasil untuk operasional. Histori lama bisa memakai batch CSV aktual tervalidasi jika diperlukan. [Langkah operasional di README](../../README.md#1b-forecasting-dengan-data-operasional)

### Menjalankan dan memeriksa kedua alur

Atur `FORECAST_PYTHON` ke Python lingkungan virtual. Di Windows, jalankan `php artisan serve --host=127.0.0.1 --port=8000 --no-reload` dan mulai ulang server setelah mengubah `.env`. Login sebagai Owner dan buka **Forecasting**. Uji tombol **Perbarui prediksi**, pilihan **Batang** dan **Garis**, serta nominal di **Lihat angka per bulan**. Cocokkan hasil dengan `/owner/forecasting/data`.

Jalankan `php artisan schedule:work` di terminal kedua jika server dijalankan terpisah. Cek jadwal dengan `php artisan schedule:list`. [Panduan lengkap](../../README.md#menjalankan-forecasting-owner)

## Bukti pengujian

| Pengujian | Hasil |
| --- | --- |
| Model nyata melalui Laravel | Agustus–Oktober 2026 menghasilkan proyeksi November **Rp8.573.667** pada `barbershop_test`. Refresh kedua memakai snapshot yang sama. |
| Uji retrospektif satu bulan | Prediksi Oktober dari Juli–September **Rp8.502.000** terhadap aktual sintetis **Rp8.792.000**. MAE **Rp290.000**. Pembanding memakai Rp8.573.000 dari September dan menghasilkan galat **Rp219.000**. |
| Tes regresi | Kandidat backend lulus **71 tes dan 975 assertion** pada MySQL 8.4.3. Rangkaian tes meliputi akses Owner, enam status, jendela latih dan uji, output Python rusak, timeout, prediksi negatif, penguncian, navigasi, serta tampilan acuan. |
| Revisi grafik bulanan | 71 tes Laravel dan 979 assertion lulus di MySQL 8.4.3. Uji JavaScript memeriksa pilihan Batang/Garis, empat titik Forecasting, segmen prediksi putus-putus, dan tombol seri Home. |
| Histori Pemasukan dan refresh | Di barbershop_dev, impor memasukkan 1.783 transaksi dan 2.085 item dengan total Rp35.273.000 di Rekap. Impor ulang menambah 0 transaksi. Forecasting membaca sumber aplikasi, berlabel sintetis, dan menghasilkan Rp8.573.667 untuk November. Pengulangan refresh otomatis bulan yang sama menghasilkan up_to_date. Regresi revisi ini lulus 75 tes dan 1.019 assertion di MySQL 8.4.3. |
| Jalur Python Windows | Laravel berhasil menjalankan Python dari jalur berspasi. Keluaran untuk fixture adalah Rp5.750.000. |
| Jadwal dan format | `schedule:list` mencantumkan refresh per jam, Pint dan `git diff --check` lulus. |

Bukti sintetis menunjukkan alur model dan evaluasi berfungsi. Ketepatan di lapangan dan ambang MAE bisnis masih perlu ditetapkan dari data aktual dan beberapa periode uji. [Rencana backend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap) · [Desain penyimpanan hasil](Desain-Database-MySQL-Barbershop.md#penyimpanan-hasil-slice-12)
