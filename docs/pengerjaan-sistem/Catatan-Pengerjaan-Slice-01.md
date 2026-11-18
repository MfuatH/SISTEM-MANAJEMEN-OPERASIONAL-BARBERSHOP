# Catatan Pengerjaan Slice 01 — Login Owner

- **Branch** `feat/slice-01-auth-owner`
- **Pull request** [PR #2 ke main](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/2)
- **Status** Implementasi dan pengujian selesai. PR #2 sudah di-merge ke `main` pada commit `34d6ace`.

## Dasar pengerjaan

Slice 00 telah masuk ke `main` pada commit `505f053` dan menyediakan Laravel, MySQL, serta tabel bawaan `users` dan `sessions`. Pada Slice 01, **Owner dan Admin adalah dua sebutan untuk akun dan izin yang sama**. Akun memakai username dan kata sandi. [Catatan Slice 00](Catatan-Pengerjaan-Slice-00.md) · [Keputusan K7](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Pekerjaan yang diselesaikan

| Area | Perubahan |
| --- | --- |
| Skema akun | Migrasi baru menambah `username` unik dan `disabled_at` pada `users`, serta menghapus `email` dan `email_verified_at`. Data akun lama tetap tersimpan dengan username kosong dan akses belum aktif. |
| Pembuatan akun | Perintah `php artisan owner:create` memvalidasi username dan meminta kata sandi secara interaktif. Model menyimpan kata sandi sebagai hash. Username dinormalisasi menjadi huruf kecil. |
| Login dan logout | Form login tersedia di `/owner/login`. Login yang sah meregenerasi sesi dan mengarahkan Owner ke `/owner`. Logout mengakhiri sesi dan meregenerasi token CSRF. |
| Perlindungan akses | Rute Owner memerlukan sesi login yang sah dan akun aktif. Sesi tamu, termasuk sesi Kapster, dialihkan ke login. Akun nonaktif ditolak saat login dan kehilangan akses pada permintaan berikutnya. |
| Kontrol login | Form login dan logout memakai CSRF. Percobaan login dibatasi 10 permintaan per menit per IP. |

Berkas utama dapat ditinjau melalui [migrasi akun](../../database/migrations/0001_01_01_000003_configure_owner_accounts.php), [rute web](../../routes/web.php), [kontroler autentikasi](../../app/Http/Controllers/OwnerAuthController.php), [pembuatan akun](../../app/Console/Commands/CreateOwner.php), dan [pengujian autentikasi](../../tests/Feature/OwnerAuthenticationTest.php).

## Kesiapan frontend

**Login Owner siap diintegrasikan.** Halaman `GET /owner/login` membuka form. Kirim `username` dan `password` dengan token CSRF ke `POST /owner/login`. Tampilkan pesan validasi dari Laravel. Login berhasil menuju area Owner `/owner`, dan tombol keluar menggunakan `POST /owner/logout`. Pemeriksaan akun aktif dan akses area Owner dilakukan di server.

## Seeder akun Owner lokal

`LocalOwnerSeeder` menyediakan akun demo yang dapat dibuat ulang setelah skema lokal dimigrasikan. Isi `LOCAL_OWNER_PASSWORD` pada `.env` dengan minimal 12 karakter dan maksimal 72 byte. Nilai awal username dan nama tampilan adalah `pemilik_demo` dan `Pemilik Demo`. Ubah melalui `LOCAL_OWNER_USERNAME` dan `LOCAL_OWNER_NAME` jika diperlukan.

Jalankan `php artisan config:clear`, `php artisan migrate`, lalu `php artisan db:seed --class=LocalOwnerSeeder` dari direktori proyek. Login lewat `/owner/login` dengan kredensial dari `.env`. Seeder tersedia pada lingkungan `local` dan `testing`. Username yang sudah terdaftar memakai data akun yang tersimpan, termasuk kata sandi dan status aktifnya. [LocalOwnerSeeder](../../database/seeders/LocalOwnerSeeder.php)

Pengujian pada MySQL 8.4.3 dengan skema sementara lulus dengan **63 tes dan 886 asersi**. Pemeriksaan mencakup login akun hasil seed, hash kata sandi, pengulangan seeder pada akun nonaktif, penolakan ketika kata sandi kosong, dan penolakan di lingkungan produksi.

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Lingkungan uji | Laravel memakai `barbershop_test` di MySQL **8.4.3**. `migrate:status --env=testing` mencatat migrasi Slice 01 berstatus `Ran`. |
| Migrasi dan rollback | Migrasi maju, rollback dengan satu akun fiktif, dan migrasi ulang berhasil. Rollback memulihkan struktur email melalui nilai placeholder unik. Uji migrasi ulang memastikan ID dan hash kata sandi akun fiktif tetap tersimpan. |
| Suite Laravel | **10 tes dan 44 assertion lulus** pada pemeriksaan ulang. Tes fitur mencakup login dan logout, akses langsung tamu, kata sandi salah, akun nonaktif, CSRF, username unik, perintah pembuatan akun, serta batas percobaan login. |
| Pemeriksaan kode | Laravel Pint lulus untuk berkas PHP yang diubah. |
| Database pengembangan lokal | Migrasi maju berhasil pada `barbershop_dev`. Akun Owner dapat dibuat oleh penguji melalui perintah interaktif. |

Bukti di atas mendukung KP-04 dan bagian KP-05 yang terkait autentikasi, validasi, dan CSRF. Pemeriksaan HTTPS di lingkungan target mengikuti Slice 13–14. [Kriteria penerimaan](Desain-Database-MySQL-Barbershop.md#9-penyesuaian-bukti-penerimaan-qmp)

## Panduan review

1. Checkout branch PR dari Terminal Laragon. Jalankan `php artisan db:show` dan `php artisan db:show --env=testing` untuk memastikan database pengembangan serta pengujian terpisah.
2. Jalankan `php artisan migrate` dan `php artisan owner:create pemilik --name="Pemilik"` untuk membuat akun di database lokal. Masukkan kata sandi saat diminta.
3. Buka `/owner/login`, masuk, lalu coba keluar. Setelah keluar, buka `/owner` secara langsung dan pastikan browser dialihkan ke halaman login.
4. Jalankan `php artisan migrate --env=testing` dan `php artisan test --env=testing`. Untuk mengulang pemeriksaan rollback, gunakan database uji terpisah dan pastikan migrasi Slice 01 tercatat sebagai migrasi terakhir melalui `migrate:status --env=testing`.

## Riwayat dan kelanjutan

| Commit | Perubahan |
| --- | --- |
| [`c9afaf3`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/c9afaf3a54e7cba17c0aff30e92515c989ffd333) | Migrasi dan autentikasi Owner, pengujian, serta sinkronisasi dokumen. |
| [`244b3c5`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/244b3c5be01010daab88fafec84999064f923171) | Penegasan bahwa Owner dan Admin merujuk pada akun yang sama. |

Slice 02 dimulai dari hasil merge PR #2. Owner mengelola katalog pada rancangan yang diterapkan, dengan Layanan A dan Layanan B sebagai data uji sementara daftar resmi disiapkan. [Urutan pengerjaan per slice](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)
