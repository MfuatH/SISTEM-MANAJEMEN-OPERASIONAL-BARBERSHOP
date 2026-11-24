# Sistem Manajemen Operasional Barbershop

Proyek pengembangan aplikasi web untuk operasional satu lokasi barbershop oleh Kelompok 6, Kelas 2023C. Lokasi pengujian yang ditetapkan dalam Quality Management Plan adalah Pangkas Rambut Upin Ipin 11.

## Latar belakang

Pencatatan transaksi, pemasukan, dan pengeluaran secara manual menyulitkan pengecekan selisih kas serta penyusunan laporan. Karyawan juga merangkap sebagai kasir, sementara pemilik membutuhkan rekap pendapatan yang bisa diperiksa tanpa menunggu pencatatan ulang.

Aplikasi menyatukan pencatatan pemasukan, pengeluaran, dan laporan bulanan untuk satu lokasi.

| Sesi | Alur utama |
| --- | --- |
| Kapster | Mulai dari beranda dengan nama berbentuk teks. Gunakan **Layanan** untuk mencatat beberapa layanan dan penjualan produk opsional, atau **Produk** untuk penjualan produk saja. Nama pelanggan opsional dan harga diambil dari katalog. |
| Owner | Istilah Admin pada deskripsi sistem merujuk ke akun Owner yang sama. Masuk dengan username dan kata sandi. Kelola katalog layanan dan produk dari Pemasukan, catat pengeluaran, buka Home dan Rekap, serta ekspor laporan ke Excel. |

Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Pada pergantian bulan, tampilan beralih ke periode baru. Rekap tetap menyediakan histori seluruh periode dan filter bulan atau jenis. Metode pembayaran dan pembuatan struk keluar dari cakupan. Forecasting bersifat opsional. [Rincian kebutuhan dan keputusan](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

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
| Peramalan | Python, pandas, scikit-learn | Jika disetujui, satu model Regresi Linier dipanggil secara internal oleh Laravel. Dashboard memuat empat bulan aktual dan dua prediksi. Versi paket dicatat saat Slice 11–12 dikerjakan. |
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
| 11–12 | `app/Console/Commands/`, `storage/app/private/imports/`, `forecasting/`, dan `resources/views/owner/` jika impor histori atau proyeksi disetujui. |
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
   php artisan serve --host=127.0.0.1 --port=8000
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
| 11 | Bila sumber historis dipakai, rekonsiliasi CSV/Excel privat dan uji impor ulang. | Opsional |
| 12 | Bila disetujui, perbarui tampilan empat bulan aktual dan dua prediksi setiap bulan. | Opsional |
| 13 | Uji integrasi MySQL, pembatasan akses, rekonsiliasi laporan, ekspor, dan UAT. | Direncanakan |
| 14 | Periksa fungsi inti sebelum penerapan, lalu catat evaluasi dan penerimaan. | Direncanakan |

Contoh kueri Rekap telah diperiksa pada MySQL 8.4.3 menggunakan data fiktif. Gunakan urutan dan kriteria selesai pada desain database untuk mengambil tugas berikutnya. [Rincian pengerjaan per slice](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)

## Status backend dan kesiapan frontend

Per **18 November 2026**, Slice 10 sudah masuk `main`. Landing Page, halaman Kapster, login Owner, Home, Pemasukan, Pengeluaran, dan Rekap siap diintegrasikan. Area Owner memakai sidebar bersama, dengan kartu dan grafik di Home serta pop-up katalog di Pemasukan.

Acuan wireframe tersedia dan Forecasting bersifat opsional. [Kesiapan frontend Slice 10](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-10.md#kesiapan-frontend) [Detail kesiapan sembilan tahap frontend](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

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
