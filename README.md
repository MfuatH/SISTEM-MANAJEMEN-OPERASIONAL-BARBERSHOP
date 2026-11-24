# Sistem Manajemen Operasional Barbershop

Aplikasi untuk mencatat transaksi, pengeluaran, dan laporan keuangan barbershop satu lokasi. Dikembangkan oleh Kelompok 6 Kelas 2023C dengan Pangkas Rambut Upin Ipin 11 sebagai lokasi pengujian.

---

## Cara kerja

| Sesi | Alur utama |
| --- | --- |
| Kapster | Mulai dari beranda dengan mengisi nama. Tombol **Layanan** mencatat beberapa layanan dan penjualan produk opsional. Tombol **Produk** mencatat penjualan produk saja. Nama pelanggan opsional dan harga diambil dari katalog. |
| Owner | Owner dan Admin merujuk pada akun yang sama. Masuk dengan username dan kata sandi. Kelola katalog layanan dan produk dari Pemasukan, catat pengeluaran, lihat Home dan Rekap, serta ekspor laporan ke Excel. |

**Home**, **Pemasukan**, dan **Pengeluaran** mengikuti bulan berjalan WIB. **Rekap** menyimpan riwayat lintas bulan, mendukung filter bulan dan jenis, serta menampilkan hasil bersih dari pemasukan dikurangi pengeluaran.

**Forecasting** aktif secara bawaan. Owner bisa melihat tiga bulan histori dan satu bulan prediksi pemasukan kotor dalam grafik batang atau garis, serta memperbarui prediksi secara manual. [Rincian kebutuhan dan keputusan](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

---

## Status pengerjaan per slice

| Slice | Pekerjaan | Status |
| --- | --- | --- |
| 00 | Fondasi Laravel dan MySQL | Selesai |
| 01 | Login dan akses Owner | Selesai |
| 02 | Katalog layanan | Selesai |
| 03 | Sesi Kapster | Selesai |
| 04 | Master pelanggan | Dilewati. Nama pelanggan menjadi keterangan opsional. |
| 05 | Pencatatan layanan dan produk | Selesai |
| 06 | Pemasukan Owner | Selesai |
| 07 | Pengeluaran Owner | Selesai |
| 08 | Koreksi Owner dan jejak audit | Dilewati. Alur operasional saat ini sudah sesuai kebutuhan. |
| 09 | Rekap keuangan dan Excel | Selesai |
| 10 | Dashboard Owner | Selesai |
| 11 | Histori dan data demo Forecasting | Selesai |
| 12 | Backend, grafik acuan, dan refresh Forecasting | Selesai diuji |

---

## Status backend dan kesiapan frontend

`main` memuat backend hingga Slice 12 dan halaman acuan Forecasting. Gunakan `feat/integrasi-backend-frontend` untuk uji manual dengan tampilan Modern Classic. Desain akhir Forecasting menjadi bagian pekerjaan frontend. [Branch integrasi](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feat/integrasi-backend-frontend) · [Kesiapan tiap halaman](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

---

## Dokumen acuan

- [Desain database](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md) dan [rencana backend](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md).
- [Catatan pengerjaan per slice](docs/pengerjaan-sistem/) dan [acuan observasi, Charter, serta QMP](docs/tugas-kuliah/README.md).
- [Histori Forecasting](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-11.md), [hasil uji Forecasting](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md), dan [kontrak JSON](forecasting/contract/v1/README.md).

---

## Panduan teknis

Jalankan perintah PowerShell dari folder utama proyek.

[Setup aplikasi](#menjalankan-aplikasi-untuk-pengembangan) · [Pengujian](#menjalankan-pengujian-lokal) · [Forecasting](#menjalankan-forecasting-owner)

### Stack teknis

| Bagian | Teknologi dan versi acuan |
| --- | --- |
| Backend | PHP **8.5.12** dan Laravel **13.42.0** |
| Database | MySQL **8.4.3 LTS** |
| Pengembangan lokal | Laragon **8.8.0** dan Composer **2.10.3** |
| Tampilan | Blade dan JavaScript. Node.js **24** untuk pengembangan aset dengan Vite. |
| Ekspor Excel | OpenSpout **5.14.0** dan Brick Math **1.0.0** untuk perhitungan rupiah |
| Pengujian | PHPUnit **12.5.44** |
| Forecasting | Python **3.14.0** dan scikit-learn **1.9.0** untuk Regresi Linier |

Rujuk [versi dependensi PHP](composer.lock), [dependensi Python](forecasting/requirements.txt), dan [catatan fondasi](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-00.md).

### Struktur folder

Folder berikut dipakai di `main` dan branch integrasi.

```text
.
├── app/
│   ├── Console/Commands/         # Buat akun Owner, impor histori, dan perbarui prediksi
│   ├── Http/                     # Controller, middleware, dan validasi request
│   ├── Models/                   # Model Eloquent
│   └── Services/                 # Logika pencatatan, laporan, dan Forecasting/
├── config/                       # Konfigurasi Laravel, database, dan Forecasting
├── database/                     # migrations/, factories/, dan seeders/
├── docs/                         # Catatan slice, desain database, dan acuan kuliah
├── forecasting/                  # Skrip Python, requirements, dan kontrak JSON
├── public/js/                    # Grafik Owner dan pop-up katalog
├── resources/views/              # Blade untuk beranda, Kapster, katalog, dan Owner
├── routes/                       # web.php, forecasting.php, dan jadwal di console.php
├── storage/app/private/imports/  # Berkas impor lokal
└── tests/                        # Pengujian Feature dan Unit
```

### Menjalankan aplikasi untuk pengembangan

1. Aktifkan PHP 8.5 dan MySQL 8.4 di Laragon. Buat database lokal `barbershop_dev` dan pastikan akun MySQL bisa mengaksesnya. Jalankan perintah berikut.

   ```powershell
   php -v
   if (!(Test-Path -LiteralPath .env)) { Copy-Item -LiteralPath .env.example -Destination .env }
   composer install
   ```

2. Atur koneksi MySQL di `.env`. Sesuaikan `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` dengan Laragon lokal.

   ```dotenv
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=barbershop_dev
   DB_USERNAME=barbershop_dev
   DB_PASSWORD=
   ```

3. Jika `APP_KEY` masih kosong, jalankan `php artisan key:generate`. Bersihkan cache konfigurasi dan periksa tujuan koneksi. Jalankan migrasi setelah `db:show` menampilkan MySQL 8.4 dan database pengembangan.

   ```powershell
   php artisan config:clear
   php artisan db:show
   php artisan migrate
   ```

4. Jika database belum memiliki akun Owner, jalankan `php artisan owner:create pemilik --name="Pemilik"` dan isi kata sandi saat diminta. Jalankan server dengan perintah berikut.

   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000 --no-reload
   ```

Buka [beranda Kapster](http://127.0.0.1:8000/) atau [login Owner](http://127.0.0.1:8000/owner/login). Gunakan `--no-reload` di Windows agar proses Python Forecasting berfungsi. Mulai ulang server setelah mengubah `.env`.

Jika mengembangkan aset lewat Vite, gunakan Node.js 24. Jalankan `npm install`, lalu `npm run dev` di terminal kedua.

### Menjalankan pengujian lokal

Salin `.env.example` ke `.env.testing` jika berkas pengujian belum ada. Atur `APP_ENV=testing`, isi `APP_KEY` dengan kunci lokal, dan arahkan koneksi ke MySQL khusus `barbershop_test`. Cek nama database sebelum migrasi dan tes.

```powershell
php artisan db:show --env=testing
php artisan migrate --env=testing
php artisan test --env=testing
```

### Alur kerja Git dan GitHub

Ambil `main` terbaru sebelum membuat branch. Gunakan `feat/` untuk fitur dan `chore/` untuk dokumentasi atau pemeliharaan. Uji perubahan dan perbarui catatan slice yang sama. Ajukan PR ke `main` dan merge setelah disetujui. Simpan `.env`, kredensial, dan berkas transaksi asli di lingkungan lokal.

### Menjalankan Forecasting Owner

Siapkan lingkungan Python dan pasang dependensinya. Salin baris `FORECAST_PYTHON` yang tercetak ke `.env`, lalu jalankan `php artisan config:clear` dan mulai ulang server. Forecasting aktif secara bawaan. Ubah `FORECAST_ENABLED` menjadi `true` jika konfigurasi lokal sebelumnya mematikannya.

```powershell
python -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r forecasting/requirements.txt
'FORECAST_PYTHON="' + (Resolve-Path -LiteralPath .\.venv\Scripts\python.exe).Path.Replace('\', '/') + '"'
```

#### 1A. Forecasting demo dengan data sintetis

Gunakan database lokal khusus demo yang sudah dimigrasikan.

```powershell
.\.venv\Scripts\python.exe forecasting/generate_synthetic_history.py storage/app/private/imports/slice11-synthetic-20261124
php artisan forecast:demo-import slice11-synthetic-20261124
php artisan forecast:history-window 2026-11
```

Di **Rekap → Pemasukan**, histori Juli–Oktober 2026 tampil berlabel **(Demo)**. Paket yang sama bisa diimpor ulang tanpa menambah transaksi. Prediksi demo **Rp8.573.667** memakai Agustus–Oktober untuk target November 2026. Refresh selalu memakai bulan berjalan WIB. Jika ada bulan yang belum tersedia, lihat `missing_months`. [Rincian data demo](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-11.md)

#### 1B. Forecasting dengan data operasional

Gunakan database transaksi asli. Laravel membaca tiga bulan tertutup dari Pemasukan. Jalankan `php artisan forecast:history-window`, pastikan histori lengkap dan `data_kind=actual`, lalu cocokkan totalnya dengan Rekap. Untuk histori dari berkas lama atau bulan yang benar-benar bernilai Rp0, ikuti [panduan pemilihan sumber](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-11.md#cara-meninjau).

#### Membuat dan memperbarui prediksi

Login sebagai Owner dan buka [Forecasting](http://127.0.0.1:8000/owner/forecasting). Klik **Perbarui prediksi**, lalu cek grafik dan **Lihat angka per bulan**. Jika hasil belum tersedia, periksa `missing_months` dan jalur Python.

Jalankan `php artisan schedule:work` pada terminal kedua untuk pembaruan otomatis lokal. Scheduler memeriksa setiap jam dan menerbitkan satu hasil siap per bulan ketika histori lengkap. Pada server, jalankan `php artisan schedule:run` setiap menit melalui scheduler sistem. [Kontrak endpoint dan status](forecasting/contract/v1/README.md)
