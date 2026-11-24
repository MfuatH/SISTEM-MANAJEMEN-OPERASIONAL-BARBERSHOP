# Sistem Manajemen Operasional Barbershop

Proyek pengembangan aplikasi web untuk operasional satu lokasi barbershop oleh Kelompok 6, Kelas 2023C. Lokasi pengujian yang ditetapkan dalam Quality Management Plan adalah Pangkas Rambut Upin Ipin 11.

## Latar belakang

Pencatatan transaksi, pemasukan, dan pengeluaran secara manual menyulitkan pengecekan selisih kas serta penyusunan laporan. Karyawan juga merangkap sebagai kasir, sementara pemilik membutuhkan rekap pendapatan yang bisa diperiksa tanpa menunggu pencatatan ulang.

Aplikasi menyatukan pencatatan pemasukan, pengeluaran, dan laporan bulanan untuk satu lokasi.

| Sesi | Alur utama |
| --- | --- |
| Kapster | Mulai dari beranda dengan nama berbentuk teks. Gunakan **Layanan** untuk mencatat beberapa layanan dan penjualan produk opsional, atau **Produk** untuk penjualan produk saja. Nama pelanggan opsional dan harga diambil dari katalog. |
| Owner | Istilah Admin pada deskripsi sistem merujuk ke akun Owner yang sama. Masuk dengan username dan kata sandi. Kelola katalog layanan dan produk dari Pemasukan, catat pengeluaran, buka Home dan Rekap, serta ekspor laporan ke Excel. |

Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Pada pergantian bulan, tampilan beralih ke periode baru. Rekap tetap menyediakan histori seluruh periode dan filter bulan atau jenis. Metode pembayaran dan pembuatan struk keluar dari cakupan. Forecasting tersedia secara bawaan. [Rincian kebutuhan dan keputusan](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Stack teknis

Stack proyek mengikuti Project Charter dan draf desain database. Tim menetapkan Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS. Tabel berikut mencatat versi yang digunakan pada Slice 00. Versi persis dependensi PHP tersimpan di `composer.lock`.

| Bagian | Teknologi dan versi | Penggunaan |
| --- | --- | --- |
| Backend web | PHP **8.5.12**, Laravel **13.42.0** | Rute web, sesi Kapster, autentikasi Owner, validasi di server, dan logika operasional. |
| Ekspor Excel | OpenSpout **5.14.0**, Brick Math **1.0.0** | Penulisan XLSX dan penjumlahan rupiah secara eksak. |
| Frontend | JavaScript dan Blade Laravel | Form pencatatan Kapster dan pengeluaran, Rekap, serta dashboard Owner. |
| Database | MySQL **8.4.3 LTS** | Penyimpanan data operasional melalui migrasi Laravel. Draf mengusulkan InnoDB, `utf8mb4`, dan tipe `DECIMAL` untuk nominal rupiah. |
| Pengembangan lokal | Laragon **8.8.0**, Composer **2.10.3** | Menjalankan PHP dan MySQL secara lokal serta memasang dependensi Laravel. |
| Pengujian | PHPUnit **12.5.44** | Menjalankan pengujian PHP melalui `php artisan test`. |
| Peramalan | Python **3.14.0**, scikit-learn **1.9.0** | Slice 11 membuat data sintetis. Slice 12 menjalankan Regresi Linier internal dengan tiga bulan historis lengkap untuk memprediksi satu bulan berikutnya. [Dependensi Python](forecasting/requirements.txt) |
| Lingkungan target | VPS dan HTTPS | Menjalankan aplikasi web dan database, serta skrip peramalan jika disetujui. Pengamanan aplikasi mencakup hash kata sandi, pemeriksaan hak akses, validasi input, dan perlindungan CSRF. |
| Kolaborasi | Git dan GitHub | Branch per tugas, pull request, peninjauan, dan merge ke `main`. |

Nama Kapster dan harga tersimpan pada catatan agar laporan historis memakai nilai saat pencatatan. Koreksi yang diaktifkan berada di area Owner. Rincian tindakan dan kebijakan audit masih ditinjau.

## Struktur folder

Aplikasi Laravel ditempatkan di root repositori. Rencana tampilan Kapster dan Owner memakai Blade dan JavaScript di `resources/`. Folder `forecasting/` menampung modul Python bila disetujui. [Dokumentasi struktur Laravel](https://laravel.com/framework/docs/structure)

```text
.
├── app/
│   ├── Console/
│   │   └── Commands/              # Perintah Artisan untuk impor histori
│   ├── Http/
│   │   ├── Controllers/           # Alur web Kapster dan Owner
│   │   ├── Middleware/            # Pemeriksaan sesi dan hak akses
│   │   └── Requests/              # Validasi input
│   ├── Models/                    # Model Eloquent
│   └── Services/                  # Pencatatan Kapster, Rekap, dan peramalan opsional
├── bootstrap/                    # Bootstrap Laravel saat proyek diinisialisasi
├── config/                       # Konfigurasi aplikasi, database, dan peramalan
├── database/
│   ├── factories/                 # Pembuatan data uji
│   ├── migrations/                # Skema MySQL dan perubahan skema
│   └── seeders/                   # Data awal atau data fiktif untuk pengembangan
├── docs/
│   ├── tugas-kuliah/              # Acuan tugas mata kuliah Manajemen Proyek
│   └── pengerjaan-sistem/         # Desain database, rencana backend, dan bukti uji
├── forecasting/                  # Skrip Python dan daftar dependensinya
├── public/                       # Document root server web
├── resources/
│   ├── css/                       # CSS sumber
│   ├── js/                        # JavaScript sumber
│   └── views/
│       ├── auth/                   # Login Owner
│       ├── master/                 # Katalog layanan
│       ├── pos/                    # Folder scaffold untuk pencatatan Kapster
│       ├── expenses/               # Input pengeluaran Owner
│       └── owner/                  # Pembukuan, dashboard, dan proyeksi
├── routes/                       # Rute web dan console Laravel
├── storage/
│   └── app/
│       └── private/
│           └── imports/            # CSV/Excel asli pada lingkungan lokal
├── tests/
│   ├── Feature/                    # Uji alur web, akses peran, dan integrasi MySQL
│   └── Unit/                       # Uji aturan dan perhitungan
├── .gitignore
└── README.md
```

| Slice | Folder yang dipakai |
| --- | --- |
| 00 | `docs/pengerjaan-sistem/`, `bootstrap/`, `config/` untuk rancangan, kontrak data, dan penyiapan Laravel. |
| 01 | `app/Http/`, `app/Models/`, `routes/`, dan `resources/views/auth/` untuk login dan akses Owner. |
| 02–03 | `database/`, `app/Models/`, dan `app/Http/` untuk katalog serta sesi Kapster. Tampilan terkait berada di `resources/views/master/`, `resources/views/pos/`, dan beranda. |
| 05–08 | `app/Services/`, `database/migrations/`, serta tampilan `pos/`, `expenses/`, dan `owner/` untuk pencatatan, pemasukan, pengeluaran, dan koreksi bersyarat. |
| 09–10 | `app/Services/`, `resources/views/owner/`, dan `resources/js/` untuk pembukuan serta dashboard. |
| 11–12 | `forecasting/` untuk generator dan kontrak, `app/Console/Commands/` serta `app/Services/Forecasting/` untuk impor dan sumber bulanan, dan `storage/app/private/imports/` untuk file lokal. |
| 13–14 | `tests/` untuk kode uji, `docs/pengerjaan-sistem/` untuk bukti UAT dan penyerahan, serta `config/` dan `public/` untuk pengaturan lingkungan target. |

### Menjalankan aplikasi untuk pengembangan

Jalankan perintah dari root repositori di PowerShell. Siapkan **PHP 8.5.x**, **Composer**, dan **MySQL 8.4 LTS**. Di Laragon, pilih PHP dan MySQL sesuai versi tersebut, lalu nyalakan MySQL.

1. **Periksa PHP dan pasang dependensi Laravel.**

   ```powershell
   php -v
   composer --version
   composer install
   ```

   Jika `php -v` masih menampilkan PHP 8.2, jalankan `where.exe php` untuk memeriksa urutan lokasi PHP. Buka terminal baru dari Laragon dan pastikan PHP 8.5.x yang aktif sebelum melanjutkan.

2. **Siapkan konfigurasi lokal.** Buat database kosong `barbershop_dev` di MySQL melalui Laragon, lalu salin konfigurasi contoh jika `.env` belum ada.

   ```powershell
   if (-not (Test-Path -LiteralPath .env)) {
       Copy-Item -LiteralPath .env.example -Destination .env
   }
   ```

   Di `.env`, sesuaikan pengaturan berikut dengan MySQL lokal.

   ```dotenv
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=barbershop_dev
   DB_USERNAME=barbershop_dev
   DB_PASSWORD=
   ```

   Ganti `DB_USERNAME` dan `DB_PASSWORD` dengan akun MySQL lokal yang berhak melakukan migrasi pada skema tersebut. Gunakan `DB_PORT=3307` jika MySQL Laragon berjalan pada port 3307. Simpan `.env` dan kredensial secara lokal.

3. **Siapkan kunci dan migrasi.** Jalankan perintah berikut setelah mengisi `.env`. Jalankan `key:generate` hanya jika `APP_KEY` masih kosong.

   ```powershell
   php artisan config:clear
   php artisan key:generate
   php artisan db:show
   php artisan migrate
   ```

   Sebelum menjalankan migrasi, pastikan `db:show` menampilkan **MySQL 8.4** dan database **`barbershop_dev`**.

4. **Buat akun Owner sekali per database lokal.** Jalankan perintah berikut, lalu masukkan kata sandi saat diminta. Gunakan akun lokal yang sudah ada jika sebelumnya dibuat pada database yang sama.

   ```powershell
   php artisan owner:create pemilik --name="Pemilik"
   ```

5. **Jalankan server Laravel.**

   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000 --no-reload
   ```

   Biarkan terminal server terbuka. Buka [beranda Kapster](http://127.0.0.1:8000) untuk memulai sesi dengan nama atau [login Owner](http://127.0.0.1:8000/owner/login) untuk masuk dengan username dan kata sandi yang dibuat tadi. Tekan `Ctrl+C` di terminal untuk menghentikan server.

   Saat mengembangkan aset melalui Vite, siapkan **Node.js 24** dan **npm**, lalu jalankan perintah ini di terminal kedua dari root repositori. Buka aplikasi melalui alamat Laravel pada langkah 5.

   ```powershell
   npm install
   npm run dev
   ```

### Menjalankan pengujian lokal

Siapkan database terpisah `barbershop_test` dan buat `.env.testing` dari `.env.example`. Isi `APP_ENV=testing`, gunakan `APP_KEY` dari lingkungan lokal, dan atur koneksi MySQL agar mengarah ke `barbershop_test`. Setelah `db:show` mengonfirmasi skema pengujian, jalankan migrasi dan tes.

```powershell
php artisan db:show --env=testing
php artisan migrate --env=testing
php artisan test --env=testing
```

Simpan CSV/Excel aktual di `storage/app/private/imports/` dan gunakan data fiktif untuk pengujian. [Panduan dan bukti Slice 00](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-00.md#cara-mengulang-pemeriksaan)

### Mencoba katalog layanan dan produk

Di `main`, buka **Pemasukan** sebagai Owner, lalu klik **Katalog layanan** atau **Katalog produk**. Kelola nama, harga, dan status lewat pop-up. **Arsipkan** mengeluarkan item dari pilihan Kapster. **Aktifkan kembali** memulihkan item aktif. Gunakan **Hapus** untuk item yang belum tercatat dalam transaksi. Produk aktif dengan harga jual tersedia pada form penjualan Kapster. [Cara meninjau katalog](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-10.md#cara-meninjau)

### Mencoba sesi Kapster

Jalankan `php artisan migrate` pada `barbershop_dev`, lalu mulai sesi Kapster dari beranda. Tombol **Layanan** membuka bagian **Layanan** dan **Produk dijual (opsional)**. Tombol **Produk** membuka form penjualan saja. Setelah **Simpan**, dashboard menampilkan satu baris per item dengan ID transaksi yang sama. [Cara meninjau revisi Slice 05](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-05.md#cara-meninjau-revisi)

### Mencoba Pemasukan Owner

Jalankan `composer install` memakai PHP 8.5. Masuk sebagai Owner dan buka **Pemasukan**. Layanan dan penjualan produk tampil per item untuk bulan berjalan WIB. Masukkan nama item, Kapster, pelanggan, atau ID transaksi untuk mencari. Tombol **Ekspor Excel** mengunduh seluruh hasil untuk pencarian tersebut. [Cara meninjau Slice 06](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-06.md#cara-meninjau)

### Mencoba Pengeluaran Owner

Di `main`, jalankan `php artisan migrate` pada `barbershop_dev`, lalu masuk sebagai Owner dan buka **Pengeluaran**. Isi tanggal, deskripsi, dan jumlah melalui **Tambah pengeluaran**. Daftar menampilkan ID buatan sistem dan total bulan berjalan WIB. Cari berdasarkan ID atau deskripsi, lalu unduh Excel dengan pencarian yang sama. [Cara meninjau Slice 07](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-07.md#cara-meninjau)

### Mencoba Rekap Owner

Di `main`, masuk sebagai Owner dan buka **Rekap**. Pilih bulan atau kosongkan untuk melihat seluruh histori. Gunakan jenis dan pencarian untuk menyaring rincian. Saat **Semua jenis** dipilih, tabel juga menampilkan total pendapatan kotor dan bersih dari hasil filter. Ringkasan menampilkan pemasukan, pengeluaran, dan hasil bersih untuk periode yang sama. Ekspor Excel menyediakan lembar **Rekap** dan **Ringkasan**. Slice ini memakai migrasi Slice 00–07 yang sudah ada. [Cara meninjau Slice 09](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-09.md#cara-meninjau)

### Mencoba Home Owner

Di `main`, masuk sebagai Owner dan buka **Home**. Cocokkan kartu pemasukan, pengeluaran, dan hasil bersih bulan berjalan WIB dengan **Rekap** pada bulan yang sama. Pilih grafik **Batang** atau **Garis** dan aktifkan seri **Pemasukan** atau **Pengeluaran**. Klik **Lihat angka per hari** untuk memeriksa nominal grafik. [Cara meninjau Slice 10](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-10.md#cara-meninjau)

### Menjalankan Forecasting Owner

Forecasting memperkirakan **pemasukan kotor** dari layanan dan produk terjual. Laravel menjalankan satu model Regresi Linier Python dengan **tiga bulan kalender lengkap dan berurutan dalam WIB** untuk memprediksi **satu bulan berikutnya**. Target refresh adalah bulan berjalan WIB. Sebagai contoh, refresh pada November 2026 memakai histori Agustus–Oktober 2026. Pengeluaran dan hasil bersih tetap dapat dilihat di Home dan Rekap.

Jalankan perintah berikut dari root repo setelah [menyiapkan Laravel dan MySQL lokal](#menjalankan-aplikasi-untuk-pengembangan). Gunakan database pengembangan dan akun Owner lokal. Bagian ini memakai PowerShell.

#### 1A. Forecasting demo dengan data sintetis

Gunakan database lokal khusus demo. Paket contoh mencakup **Juli–Oktober 2026** dan dicatat sebagai transaksi Pemasukan berlabel **(Demo)**. Gunakan akun Owner pada database demo untuk meninjau hasilnya.

```powershell
php artisan db:show
php artisan migrate
python forecasting/generate_synthetic_history.py storage/app/private/imports/slice11-synthetic-20261124
php artisan forecast:history-import slice11-synthetic-20261124 --preview
php artisan forecast:demo-import slice11-synthetic-20261124
php artisan forecast:history-window 2026-11
```

Buka **Rekap**, pilih jenis **Pemasukan** dan salah satu bulan Juli–Oktober 2026. Impor paket yang sama dapat dijalankan ulang tanpa menambah transaksi. Hasil contoh berisi **1.783 transaksi** dengan total pemasukan kotor **Rp35.273.000**. Input Agustus–Oktober menghasilkan proyeksi demo November sebesar **Rp8.573.667**. Uji retrospektif Oktober menghasilkan MAE **Rp290.000** dengan satu bulan uji sintetis. Gunakan angka itu sebagai pembanding saat mengecek demo.

`forecast:history-window 2026-11` menampilkan input untuk periode contoh. Refresh tetap memakai bulan berjalan WIB, sehingga mulai Desember 2026 diperlukan sumber tambahan untuk bulan yang tercantum di missing_months. [Rincian hasil demo](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md#bukti-pengujian)

#### 1B. Forecasting dengan data operasional

Gunakan database operasional yang berisi transaksi asli Kapster. Pada setiap bulan WIB yang sudah berakhir, Forecasting otomatis membaca pemasukan kotor layanan dan produk dari catatan aplikasi. Periksa total bulanan di **Rekap → Pemasukan**, lalu cek tiga bulan input saat ini.

```powershell
php artisan forecast:history-window
```

Untuk penggunaan operasional, periksa `data_kind` bernilai `actual`. Jika nilainya `synthetic` atau `mixed`, periksa sumber bulanan dan transaksi demo yang masuk dalam periode tersebut. Pilih database operasional yang bersih dari data demo sebelum menggunakan hasil untuk barbershop. Status `insufficient_data` menyertakan `missing_months`. Bulan yang benar-benar tutup dengan Rp0 dapat dikonfirmasi lewat `php artisan forecast:month-source YYYY-MM application` setelah diperiksa di Rekap. Ganti `YYYY-MM` dengan bulan yang dikonfirmasi.

Jika histori berasal dari catatan lama, normalisasi ke paket CSV v1 berlabel `actual`, simpan di `storage/app/private/imports/`, lalu validasi dan pilih sumber impor. Satu bulan memakai satu sumber. [Format paket dan cara memilih sumber](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-11.md#paket-csv-privat)

Gunakan MAE dari histori aktual untuk meninjau kinerja model jika tersedia bulan pembanding. Ketepatan lapangan masih memerlukan evaluasi data aktual pada beberapa periode.

#### 2. Siapkan Python dan buka Forecasting

Langkah ini berlaku untuk demo maupun penggunaan operasional. Siapkan Python 3.14 dan pasang dependensi proyek di lingkungan virtual.

```powershell
python --version
python -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r forecasting/requirements.txt
$forecastPython = (Resolve-Path -LiteralPath .\.venv\Scripts\python.exe).Path.Replace('\', '/')
'FORECAST_PYTHON="' + $forecastPython + '"'
```

Salin baris `FORECAST_PYTHON` yang tercetak ke `.env`. Forecasting aktif secara bawaan. Jika `.env` lama berisi `FORECAST_ENABLED=false`, hapus baris itu atau ubah menjadi `true`.

```powershell
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Di Windows, `--no-reload` menjaga lingkungan proses yang dibutuhkan Python. Mulai ulang server setelah mengubah `.env`. Login sebagai Owner dan pilih **Forecasting** di sidebar. Klik **Perbarui prediksi** untuk meminta perhitungan langsung, atau gunakan scheduler pada langkah 4. [Halaman Forecasting lokal](http://127.0.0.1:8000/owner/forecasting)

#### 3. Baca hasil dan status

Pilih **Batang** atau **Garis (Line Chart)** pada grafik bulanan. Saat prediksi siap, grafik memuat tiga bulan historis dan satu bulan proyeksi. Warna pembeda dan segmen garis putus-putus menandai prediksi. Buka **Lihat angka per bulan** untuk memeriksa nominal. Halaman juga menampilkan sumber data sintetis atau campuran jika digunakan dan waktu pembaruan. Jika empat bulan lengkap tersedia, MAE menunjukkan rata-rata selisih absolut antara prediksi uji dan nilai aktual. Bandingkan dengan galat pembanding yang memakai pemasukan bulan terakhir sebagai prediksi. [Rincian evaluasi](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md#bukti-pengujian)

| Status halaman | Arti dan cara memeriksa |
| --- | --- |
| `disabled` | Fitur belum aktif. Setel `FORECAST_ENABLED=true` dan bersihkan cache konfigurasi. |
| `processing` | Refresh sedang berjalan. Muat ulang halaman setelah selesai. |
| `insufficient_data` | Histori belum lengkap. Periksa `missing_months` lewat `forecast:history-window`. |
| `ready` | Prediksi sesuai dengan periode dan sumber terkini. |
| `stale` | Halaman menampilkan hasil terakhir saat periode atau sumber berubah. Periksa histori terkini, lalu klik **Perbarui prediksi**. |
| `unavailable` | Prediksi periode ini belum dibuat atau gagal. Klik **Perbarui prediksi**, lalu periksa `reason_code` jika status berlanjut. |

#### 4. Jalankan pembaruan rutin dan cek endpoint

Scheduler memeriksa **setiap jam** dan otomatis menerbitkan satu hasil siap per bulan target WIB, setelah tiga bulan input tersedia. Jika scheduler dimulai setelah awal bulan, hasil dibuat pada pemeriksaan berikutnya. **Perbarui prediksi** dapat menghitung ulang di bulan yang sama. `composer dev` menjalankan scheduler bersama proses dev. Jika memakai `php artisan serve`, jalankan scheduler berikut di terminal kedua.

```powershell
php artisan schedule:list
php artisan schedule:work
```

Server yang dideploy perlu menjalankan `php artisan schedule:run` setiap menit melalui scheduler sistem. `GET /owner/forecasting`, `GET /owner/forecasting/data`, dan `POST /owner/forecasting/refresh` memakai sesi Owner aktif. Tombol manual mengirim POST dengan CSRF. Jika mengakses JSON dari frontend, kirim `Accept: application/json` agar sesi yang sudah berakhir mendapat `401` dalam JSON. Nominal dalam respons dikirim sebagai string rupiah. [Kontrak JSON dan contoh respons](forecasting/contract/v1/README.md) · [Catatan Pengerjaan Slice 12](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md)

## Status pengerjaan per slice

Fondasi Laravel dan MySQL sudah diuji di Slice 00 dan masuk ke `main` melalui [PR #1](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/1). Desain berikut menjadi acuan peninjauan fitur.

| Slice | Pekerjaan dan indikator selesai | Status |
| --- | --- | --- |
| 00 | Verifikasi fondasi Laravel dan MySQL, lalu tinjau kontrak data dan keputusan desain terbaru. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-00.md) |
| 01 | Owner dapat login dengan username, logout, dan mengakses rute sesuai izin. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-01.md) |
| 02 | Owner mengelola katalog layanan. Daftar baca-saja menyajikan layanan aktif beserta harga. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-02.md) |
| 03 | Kapster memulai dan mengakhiri sesi dengan nama teks. Dashboard dilindungi sesi dan menyediakan pratinjau layanan dan harga. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-03.md) |
| 04 | Master pelanggan. Nama pelanggan cukup sebagai keterangan opsional pada pencatatan Slice 05. | Dilewati |
| 05 | Pencatatan satu layanan dikembangkan menjadi transaksi multi-layanan dengan penjualan produk opsional dan form Produk tersendiri. Owner dapat mengelola katalog produk, mengaktifkan kembali arsip layanan, serta menghapus layanan yang belum tercatat. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge) |
| 06 | Pemasukan Owner menampilkan satu baris per layanan atau penjualan produk pada bulan berjalan WIB. Daftar, total, pencarian, dan ekspor XLSX memakai sumber yang sama. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-06.md) |
| 07 | Owner mencatat pengeluaran dengan ID buatan sistem. Riwayat, pencarian, total, dan XLSX mengikuti bulan berjalan WIB. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-07.md) |
| 08 | Tindakan koreksi Owner dan jejak audit menunggu penetapan cakupan. | Ditunda. Pengerjaan dilanjutkan ke Slice 09. |
| 09 | Rekap menggabungkan pemasukan dan pengeluaran lintas periode dengan pencarian, filter, dan hasil bersih tercatat di halaman serta XLSX. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-09.md) |
| 10 | Home Owner menampilkan kartu dan grafik harian interaktif. Home, Pemasukan, Pengeluaran, dan Rekap memakai sidebar bersama. Katalog dibuka dari Pemasukan. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-10.md) |
| 11 | Generator sintetis Juli–Oktober, impor CSV privat dengan rekonsiliasi dan satu sumber per bulan, pembaca tiga bulan, serta kontrak v1 untuk frontend. | Selesai dan diuji. [PR #17](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/17) · [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-11.md) |
| 12 | Regresi Linier, evaluasi MAE, refresh otomatis bulanan dan tombol manual, rute Owner, dan halaman acuan dengan tiga bulan historis serta satu prediksi. | Backend dan halaman acuan selesai diuji. Desain akhir Forecasting dikerjakan tim frontend. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md) |
| 13 | Uji integrasi MySQL, pembatasan akses, rekonsiliasi laporan, ekspor, dan UAT. | Direncanakan |
| 14 | Periksa fungsi inti sebelum penerapan, lalu catat evaluasi dan penerimaan. | Direncanakan |

Contoh kueri Rekap telah diperiksa pada MySQL 8.4.3 menggunakan data fiktif. Gunakan urutan dan kriteria selesai pada desain database untuk mengambil tugas berikutnya. [Rincian pengerjaan per slice](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)

## Status backend dan kesiapan frontend

Per **18 November 2026**, Slice 10 sudah masuk `main`. Landing Page, halaman Kapster, login Owner, Home, Pemasukan, Pengeluaran, dan Rekap siap diintegrasikan. Area Owner memakai sidebar bersama, dengan kartu dan grafik di Home serta pop-up katalog di Pemasukan.

Forecasting siap diuji melalui halaman acuan dan kontrak JSON Owner. Acuan memuat status, histori, prediksi, grafik Batang atau Garis, rincian, dan MAE. Kombinasi backend dengan tema Modern Classic dari `feature/ui-redesign` tersedia untuk tim frontend pada branch `feat/integrasi-backend-frontend`. [Branch integrasi](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feat/integrasi-backend-frontend) · [Handoff Forecasting](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-12.md#handoff-frontend)

## Alur kerja Git dan GitHub

Setiap anggota mengerjakan satu tugas atau slice pada branch tersendiri. Gunakan `main` sebagai tujuan pull request.

1. **Ambil tugas.** Cantumkan nomor slice, penanggung jawab, dependensi, dan kriteria selesai di catatan tugas atau issue. Untuk pekerjaan frontend dan backend yang saling terkait, sepakati format data serta respons validasi sebelum implementasi.
2. **Buat branch dari `main` terbaru.** Gunakan `feat/` untuk pengerjaan fitur utama, misalnya `feat/slice-06-pemasukan-owner`. Gunakan `chore/` untuk pekerjaan lain seperti fondasi, dokumentasi, dan pemeliharaan, misalnya `chore/slice-00-laravel-foundation`.

   ```bash
   git fetch origin
   git switch main
   git pull --ff-only origin main
   git switch -c feat/slice-06-pemasukan-owner
   ```

3. **Kerjakan dan uji di branch tugas.** Uji alur normal dan kasus gagal yang relevan. Gunakan data fiktif untuk pengujian. Simpan `.env`, kredensial, dan file transaksi asli di luar commit. Periksa `git status`, lalu stage hanya berkas yang terkait dengan tugas.

   ```bash
   git commit -m "Implementasikan autentikasi dan akses peran"
   git push -u origin feat/slice-01-auth-akses
   ```

4. **Buka pull request ke `main`.** Cantumkan nomor slice, ringkasan perubahan, cara mencoba, dan bukti uji. Jika skema berubah, jelaskan cara menjalankan migrasi dan rollback. Jika ada perubahan antarmuka, sertakan contoh input dan keluaran untuk frontend.
5. **Tinjau, perbaiki, dan merge.** Anggota tim meninjau perubahan terhadap kriteria slice dan QMP yang terkait. Jika ada temuan atau konflik, perbaiki di branch tugas dan jalankan ulang pemeriksaan yang terdampak. Merge ke `main` setelah review disetujui dan pemeriksaan yang berlaku lulus.
6. **Perbarui status.** Catat tautan pull request dan ubah status slice sesuai hasil. Gunakan **Dalam pengerjaan** saat implementasi dimulai, **Dalam review** setelah pull request siap, dan **Selesai** setelah kriteria terpenuhi serta perubahan di-merge. Untuk UAT dan penyerahan, catat juga bukti uji atau keputusan penerimaan. Ambil `main` terbaru sebelum memulai tugas berikutnya.

7. **Catat pengerjaan tiap slice.** Buat satu dokumen `docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-XX.md` dengan nomor slice dua digit. Catat status, perubahan, keputusan, bukti uji, dan langkah berikutnya. Untuk pekerjaan lanjutan pada slice yang sama, perbarui atau tambahkan isi pada dokumen tersebut. Tautkan catatan dari tabel status slice.

## Dokumen acuan

- **Dokumen tugas kuliah** memuat daftar acuan observasi, Project Charter, dan Quality Management Plan. [Acuan tugas kuliah](docs/tugas-kuliah/README.md)
- **Desain Database MySQL Barbershop** untuk ERD, kamus data, migrasi, contoh rekonsiliasi, dan rincian Slice 00–14. [Draf desain database](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md)
- **Rencana Backend** untuk konteks pengerjaan dan bukti yang perlu disiapkan. [Rencana backend](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md)
- **Manajemen Operasional Barbershop.pdf** untuk latar belakang dan kebutuhan operasional awal.
- **menpro 2.pdf (Project Charter)** untuk cakupan awal dan teknologi proyek.
- **Quality Management Plan_Kelompok 6_2023C.pdf** untuk kriteria penerimaan KP-01 sampai KP-07 dan prosedur UAT.
