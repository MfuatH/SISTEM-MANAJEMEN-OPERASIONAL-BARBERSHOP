# Catatan Pengerjaan Slice 00 — Fondasi Laravel dan MySQL

- **Tanggal sesi** 16 November 2026
- **Branch tugas** `chore/slice-00-laravel-foundation`
- **Pull request** [PR #1 ke main](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/1)

## Status pengerjaan

| Pekerjaan | Status | Hasil atau tindak lanjut |
| --- | --- | --- |
| Pemeriksaan repositori dan inisialisasi Laravel | Selesai | Scaffold di `main` diperiksa pada commit `34c3672`. Fondasi Laravel ditambahkan melalui branch Slice 00. |
| Penyiapan Laragon dan MySQL | Selesai di lingkungan lokal | PHP 8.5 dan MySQL 8.4 aktif untuk proyek. Database pengembangan dan pengujian menggunakan skema dan akun masing-masing. |
| Migrasi bawaan dan pengujian | Lulus | Ketiga migrasi bawaan tercatat `Ran` di kedua skema. Rollback, migrasi ulang, dan dua tes bawaan Laravel lulus. |
| Review dan merge fondasi | Selesai | Fondasi masuk ke `main` pada commit `505f053`. |
| Keputusan bisnis dan kontrak fitur | Terbuka sesuai slice terkait | Gunakan daftar keputusan di desain database dan sepakati kontrak dengan frontend sebelum menulis migrasi yang terdampak. |

## Kondisi awal

- Repositori berisi dokumentasi dan struktur direktori. `artisan`, `composer.json`, `bootstrap/app.php`, serta `routes/web.php` belum tersedia. Direktori model dan migrasi berisi `.gitkeep`.
- PHP pada PATH global mengarah ke XAMPP 8.2.12. Port MySQL 3306 sedang digunakan saat penyiapan lingkungan lokal.
- Tim sudah menetapkan Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS. Aturan Git untuk berkas impor aktual sudah tersedia di `storage/app/private/.gitignore`.

## Pekerjaan yang diselesaikan

### 1. Fondasi aplikasi Laravel

- Membuat branch dari `main` yang telah dicocokkan dengan GitHub. Mengambil kerangka resmi Laravel 13 dan menyalin berkas aplikasi ke root repositori.
- Menambahkan `artisan`, bootstrap, konfigurasi, model `User`, rute awal, migrasi bawaan, factory, seeder, dan tes bawaan. Dokumen desain dan aturan Git scaffold dipertahankan.
- Menetapkan PHP `~8.5.0` pada `composer.json`, memasang dependensi dengan Composer di Laragon, dan menyimpan hasil resolusi di `composer.lock`. Menyesuaikan skrip bootstrap agar migrasi dijalankan secara eksplisit setelah koneksi MySQL diperiksa.
- Menjalankan penemuan paket dan membuat kunci aplikasi untuk konfigurasi pengembangan dan pengujian. Menambahkan versi yang dipakai ke [bagian Stack teknis README](../../README.md#stack-teknis).

### 2. Lingkungan pengembangan dan pengujian

| Komponen | Versi yang dipakai |
| --- | --- |
| Laragon | 8.8.0 |
| PHP | 8.5.12 |
| Laravel Framework | 13.42.0 |
| MySQL | 8.4.3 LTS |
| Composer | 2.10.3 |
| PHPUnit | 12.5.44 |

- Menyiapkan Laragon di folder pengguna terpisah dan mengaktifkan PHP 8.5 serta MySQL 8.4. MySQL proyek mendengarkan di `127.0.0.1:3307` karena port 3306 sedang digunakan saat setup. Port di `.env.example` tetap `3306` sebagai nilai contoh.
- Membuat skema `barbershop_dev` dan `barbershop_test` dengan akun lokal terpisah. Menyiapkan `.env` untuk pengembangan dan `.env.testing` untuk pengujian. Kedua berkas disimpan lokal dan diabaikan Git.
- Mengubah `.env.example` agar memakai MySQL dan driver sesi `database`. Mengatur `phpunit.xml` agar menggunakan MySQL dengan skema `barbershop_test`. Tes bawaan memakai driver sesi `array`.
- Menjalankan perintah dengan PHP dari Laragon. Gunakan Terminal Laragon agar `php` mengarah ke PHP 8.5 pada pekerjaan berikutnya. Versi dependensi peramalan akan dicatat pada Slice 11–12.

### 3. Migrasi bawaan

| Migrasi di `database/migrations/` | Tabel yang dibuat |
| --- | --- |
| `0001_01_01_000000_create_users_table.php` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` |

Laravel juga membuat tabel `migrations`. Setelah migrasi, setiap skema berisi sembilan tabel. Slice 01 menambahkan kolom `username` dan `disabled_at` untuk Owner melalui migrasi baru. Skema akun pada Slice 01 memakai username dan kata sandi. Tabel `sessions` bawaan menjadi dasar sesi Laravel untuk Owner dan sesi nama Kapster.

## Bukti pemeriksaan

| Pemeriksaan | Hasil aktual |
| --- | --- |
| `composer validate --strict` dan `composer check-platform-reqs` | Manifest valid dan persyaratan platform terpenuhi pada PHP 8.5.12. |
| `php artisan about` | Laravel 13.42.0, PHP 8.5.12, driver database MySQL, dan sesi database pada konfigurasi pengembangan. |
| `php artisan db:show` dan `php artisan db:show --env=testing` | Kedua koneksi terverifikasi memakai MySQL 8.4.3 pada port 3307 dengan skema tujuan yang berbeda dan kosong sebelum migrasi. Pemeriksaan ulang setelah migrasi menunjukkan sembilan tabel pada masing-masing skema, termasuk `users` dan `sessions`. |
| `php artisan migrate` dan `php artisan migrate --env=testing` | Ketiga migrasi berhasil pada kedua skema. `migrate:status` menunjukkan status `Ran` untuk ketiganya. |
| `php artisan migrate:rollback --env=testing`, lalu migrasi ulang pada skema uji | Ketiga migrasi berhasil dibatalkan dan diterapkan kembali. Tabel `users` di skema pengembangan tetap tersedia selama rollback skema uji. |
| `php artisan test` | Dua tes bawaan lulus, dengan dua assertion. Salah satunya memeriksa respons HTTP halaman awal. |
| Pemeriksaan Git terhadap berkas lokal | `.env`, `.env.testing`, dan berkas CSV/Excel aktual di direktori impor tercakup oleh aturan ignore. |

### Cara mengulang pemeriksaan

1. Buka Terminal Laragon di root repositori. Siapkan `barbershop_dev` dan `barbershop_test` di MySQL 8.4. Gunakan `.env.example` sebagai acuan untuk `.env` dan `.env.testing` lokal, lalu sesuaikan nama skema, akun, kata sandi, dan port untuk masing-masing.
2. Jalankan `composer install`. Jika berkas environment baru dibuat, jalankan `php artisan key:generate` dan `php artisan key:generate --env=testing`.
3. Jalankan `php artisan about` dan `php artisan db:show` untuk kedua lingkungan. Periksa versi MySQL serta nama skema sebelum melanjutkan.
4. Jalankan `php artisan migrate`, `php artisan migrate --env=testing`, dan `php artisan test`. Untuk mengulang pemeriksaan rollback, gunakan skema pengujian yang baru disiapkan dan konfirmasi tujuan koneksinya melalui `php artisan db:show --env=testing` terlebih dahulu.

## Riwayat perubahan dan kelanjutan

| Commit | Perubahan |
| --- | --- |
| [`6b5bd60`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/6b5bd6061227c4f9374a0c76b83aa9be6ffafa70) | Inisialisasi Laravel, konfigurasi MySQL, dan skema uji terpisah. |
| [`b098fd9`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/b098fd9b37fc1f79e722c18be9b8fbffb16f55c9) | Pencatatan versi pasti stack pada README. |

### Revisi desain setelah uji fondasi

Desain kini memakai nama Kapster berbentuk teks, harga dari katalog, serta ID pengeluaran buatan sistem. Home, Pemasukan, dan Pengeluaran berpindah ke bulan berjalan. Rekap menyediakan histori seluruh periode. Kueri Rekap telah diuji dengan data fiktif pada MySQL 8.4.3. [Keputusan desain](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka) · [Bukti rekonsiliasi](Desain-Database-MySQL-Barbershop.md#8-data-uji-fiktif-dan-rekonsiliasi)

Fondasi telah di-merge ke `main`. Branch Slice 01 memakai commit tersebut untuk implementasi login Owner. Setelah Slice 01 ditinjau, lanjutkan ke katalog layanan, sesi Kapster, dan pencatatan berdasarkan keputusan tiap slice. [Urutan pengerjaan per slice](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice) · [Rencana backend](Rencana-Backend-Sistem-Operasional-Barbershop.md)
