# Sistem Manajemen Operasional Barbershop

Proyek pengembangan aplikasi web untuk operasional satu lokasi barbershop oleh Kelompok 6, Kelas 2023C. Lokasi pengujian yang ditetapkan dalam Quality Management Plan adalah Pangkas Rambut Upin Ipin 11.

## Latar belakang

Pencatatan transaksi, pemasukan, dan pengeluaran secara manual menyulitkan pengecekan selisih kas serta penyusunan laporan. Karyawan juga merangkap sebagai kasir, sementara pemilik membutuhkan rekap pendapatan yang bisa diperiksa tanpa menunggu pencatatan ulang.

Aplikasi menyatukan pencatatan pemasukan, pengeluaran, dan laporan bulanan untuk satu lokasi.

| Sesi | Alur utama |
| --- | --- |
| Kapster | Mulai dari beranda dengan nama berbentuk teks. Pilih layanan untuk melihat harga katalog, lalu simpan pencatatan yang menjadi pemasukan. |
| Owner | Istilah Admin pada deskripsi sistem merujuk ke akun Owner yang sama. Masuk dengan username dan kata sandi. Catat pengeluaran, buka Home, Pemasukan, Pengeluaran, dan Rekap, serta ekspor laporan ke Excel. |

Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Pada pergantian bulan, tampilan beralih ke periode baru. Rekap tetap menyediakan histori seluruh periode dan filter bulan atau jenis. Metode pembayaran dan pembuatan struk keluar dari cakupan. Forecasting bersifat opsional. [Rincian kebutuhan dan keputusan](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Stack teknis

Stack proyek mengikuti Project Charter dan draf desain database. Tim menetapkan Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS. Tabel berikut mencatat versi yang digunakan pada Slice 00. Versi persis dependensi PHP tersimpan di `composer.lock`.

| Bagian | Teknologi dan versi | Penggunaan |
| --- | --- | --- |
| Backend web | PHP **8.5.12**, Laravel **13.42.0** | Rute web, sesi Kapster, autentikasi Owner, validasi di server, dan logika operasional. |
| Frontend | JavaScript dan Blade Laravel | Form pencatatan Kapster dan pengeluaran, Rekap, serta dashboard Owner. |
| Database | MySQL **8.4.3 LTS** | Penyimpanan data operasional melalui migrasi Laravel. Draf mengusulkan InnoDB, `utf8mb4`, dan tipe `DECIMAL` untuk nominal rupiah. |
| Pengembangan lokal | Laragon **8.8.0**, Composer **2.10.3** | Menjalankan PHP dan MySQL secara lokal serta memasang dependensi Laravel. |
| Pengujian | PHPUnit **12.5.44** | Menjalankan pengujian PHP melalui `php artisan test`. |
| Peramalan | Python, pandas, scikit-learn | Jika disetujui, satu model Regresi Linier dipanggil secara internal oleh Laravel. Dashboard memuat empat bulan aktual dan dua prediksi. Versi paket dicatat saat Slice 11–12 dikerjakan. |
| Lingkungan target | VPS dan HTTPS | Menjalankan aplikasi web dan database, serta skrip peramalan jika disetujui. Pengamanan aplikasi mencakup hash kata sandi, pemeriksaan hak akses, validasi input, dan perlindungan CSRF. |
| Kolaborasi | Git dan GitHub | Branch per tugas, pull request, peninjauan, dan merge ke `main`. |

Satu pencatatan Kapster menghasilkan satu pemasukan. Nama Kapster dan harga tersimpan pada catatan agar laporan historis memakai nilai saat pencatatan. Koreksi yang diaktifkan berada di area Owner. Rincian tindakan dan kebijakan audit masih ditinjau.

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
│       ├── master/                 # Katalog layanan dan pelanggan bila dibutuhkan
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
| 02–04 | `database/`, `app/Models/`, `app/Http/`, dan `resources/views/master/` untuk katalog, sesi Kapster, serta data pelanggan bila dibutuhkan. |
| 05–08 | `app/Services/`, `database/migrations/`, serta tampilan `pos/`, `expenses/`, dan `owner/` untuk pencatatan, pemasukan, pengeluaran, dan koreksi bersyarat. |
| 09–10 | `app/Services/`, `resources/views/owner/`, dan `resources/js/` untuk pembukuan serta dashboard. |
| 11–12 | `app/Console/Commands/`, `storage/app/private/imports/`, `forecasting/`, dan `resources/views/owner/` jika impor histori atau proyeksi disetujui. |
| 13–14 | `tests/` untuk kode uji, `docs/pengerjaan-sistem/` untuk bukti UAT dan penyerahan, serta `config/` dan `public/` untuk pengaturan lingkungan target. |

### Menyiapkan lingkungan lokal

1. Pilih PHP 8.5.x dan MySQL 8.4 LTS di Terminal Laragon, lalu jalankan `composer install` dari root repositori.
2. Gunakan `.env.example` sebagai acuan untuk `.env` dan `.env.testing`. Arahkan keduanya ke skema terpisah, `barbershop_dev` dan `barbershop_test`, sesuai port MySQL lokal.
3. Jalankan `php artisan about`, `php artisan db:show`, dan `php artisan db:show --env=testing` sebelum menerapkan migrasi bawaan. Simpan CSV/Excel aktual di `storage/app/private/imports/` dan gunakan data fiktif untuk pengujian. [Panduan dan bukti Slice 00](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-00.md#cara-mengulang-pemeriksaan)

### Menyiapkan akun Owner

Jalankan `php artisan migrate`, lalu `php artisan owner:create pemilik --name="Pemilik"` pada lingkungan lokal. Masukkan kata sandi saat diminta, lalu buka `/owner/login`. Perintah membuat akun Owner dengan username dan kata sandi. Nama hanya digunakan untuk tampilan.

Untuk menguji alur di MySQL, jalankan `php artisan migrate --env=testing`, lalu `php artisan test --env=testing`.

## Status pengerjaan per slice

Fondasi Laravel dan MySQL sudah diuji di Slice 00 dan masuk ke `main` melalui [PR #1](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/1). Desain berikut menjadi acuan peninjauan fitur.

| Slice | Pekerjaan dan indikator selesai | Status |
| --- | --- | --- |
| 00 | Verifikasi fondasi Laravel dan MySQL, lalu tinjau kontrak data dan keputusan desain terbaru. | Selesai dan di-merge. [Catatan pengerjaan](docs/pengerjaan-sistem/Catatan-Pengerjaan-Slice-00.md) |
| 01 | Owner dapat login dengan username, logout, dan mengakses rute sesuai izin. | Implementasi diuji |
| 02 | Katalog layanan menyediakan pilihan aktif dan harga yang valid untuk form Kapster, sesuai kewenangan pengelola yang disepakati. | Direncanakan |
| 03 | Kapster memulai sesi dengan nama teks. Dashboard menampilkan catatan sesuai cakupan sesi yang disepakati. | Direncanakan |
| 04 | Tentukan kebutuhan nama pelanggan dan relasi master sesuai keputusan K1. | Bersyarat |
| 05 | Simpan pencatatan Kapster dengan harga otomatis, snapshot harga, dan satu pemasukan per penyimpanan sah. | Direncanakan |
| 06 | Pemasukan Owner menampilkan bulan berjalan. Hasil pencarian dan XLSX dapat direkonsiliasi. | Direncanakan |
| 07 | Owner mencatat pengeluaran dengan ID buatan sistem. Riwayat dan XLSX mengikuti bulan berjalan. | Direncanakan |
| 08 | Jika disetujui, Owner menjalankan tindakan koreksi yang dicatat untuk audit. | Bersyarat |
| 09 | Rekap menggabungkan pemasukan dan pengeluaran lintas periode serta menyediakan filter dan XLSX. Target selisih Rp0. | Direncanakan |
| 10 | Home menampilkan dua total bulanan dan grafik harian. Pergantian bulan memakai rentang baru dan histori tersedia di Rekap. | Direncanakan |
| 11 | Bila sumber historis dipakai, rekonsiliasi CSV/Excel privat dan uji impor ulang. | Opsional |
| 12 | Bila disetujui, perbarui tampilan empat bulan aktual dan dua prediksi setiap bulan. | Opsional |
| 13 | Uji integrasi MySQL, pembatasan akses, rekonsiliasi laporan, ekspor, dan UAT. | Direncanakan |
| 14 | Periksa fungsi inti sebelum penerapan, lalu catat evaluasi dan penerimaan. | Direncanakan |

Contoh kueri Rekap telah diperiksa pada MySQL 8.4.3 menggunakan data fiktif. Gunakan urutan dan kriteria selesai pada desain database untuk mengambil tugas berikutnya. [Rincian pengerjaan per slice](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)

## Alur kerja Git dan GitHub

Setiap anggota mengerjakan satu tugas atau slice pada branch tersendiri. Gunakan `main` sebagai tujuan pull request.

1. **Ambil tugas.** Cantumkan nomor slice, penanggung jawab, dependensi, dan kriteria selesai di catatan tugas atau issue. Untuk pekerjaan frontend dan backend yang saling terkait, sepakati format data serta respons validasi sebelum implementasi.
2. **Buat branch dari `main` terbaru.** Gunakan nama yang menunjukkan pekerjaan, misalnya `feat/slice-01-auth-akses`. Nama seperti `fix/slice-05-cegah-pencatatan-ganda` dan `docs/slice-00-kontrak-data` bisa dipakai untuk perbaikan dan dokumentasi.

   ```bash
   git fetch origin
   git switch main
   git pull --ff-only origin main
   git switch -c feat/slice-01-auth-akses
   ```

3. **Kerjakan dan uji di branch tugas.** Uji alur normal dan kasus gagal yang relevan. Gunakan data fiktif untuk pengujian. Simpan `.env`, kredensial, dan file transaksi asli di luar commit. Periksa `git status`, lalu stage hanya berkas yang terkait dengan tugas.

   ```bash
   git commit -m "Implementasikan autentikasi dan akses peran"
   git push -u origin feat/slice-01-auth-akses
   ```

4. **Buka pull request ke `main`.** Cantumkan nomor slice, ringkasan perubahan, cara mencoba, dan bukti uji. Jika skema berubah, jelaskan cara menjalankan migrasi dan rollback. Jika ada perubahan antarmuka, sertakan contoh input dan keluaran untuk frontend.
5. **Tinjau, perbaiki, dan merge.** Anggota tim meninjau perubahan terhadap kriteria slice dan QMP yang terkait. Jika ada temuan atau konflik, perbaiki di branch tugas dan jalankan ulang pemeriksaan yang terdampak. Merge ke `main` setelah review disetujui dan pemeriksaan yang berlaku lulus.
6. **Perbarui status.** Catat tautan pull request dan ubah status slice sesuai hasil. Gunakan **Dalam pengerjaan** saat implementasi dimulai, **Dalam review** setelah pull request siap, dan **Selesai** setelah kriteria terpenuhi serta perubahan di-merge. Untuk UAT dan penyerahan, catat juga bukti uji atau keputusan penerimaan. Ambil `main` terbaru sebelum memulai tugas berikutnya.

## Dokumen acuan

- **Dokumen tugas kuliah** memuat daftar acuan observasi, Project Charter, dan Quality Management Plan. [Acuan tugas kuliah](docs/tugas-kuliah/README.md)
- **Desain Database MySQL Barbershop** untuk ERD, kamus data, migrasi, contoh rekonsiliasi, dan rincian Slice 00–14. [Draf desain database](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md)
- **Rencana Backend** untuk konteks pengerjaan dan bukti yang perlu disiapkan. [Rencana backend](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md)
- **Manajemen Operasional Barbershop.pdf** untuk latar belakang dan kebutuhan operasional awal.
- **menpro 2.pdf (Project Charter)** untuk cakupan awal dan teknologi proyek.
- **Quality Management Plan_Kelompok 6_2023C.pdf** untuk kriteria penerimaan KP-01 sampai KP-07 dan prosedur UAT.
