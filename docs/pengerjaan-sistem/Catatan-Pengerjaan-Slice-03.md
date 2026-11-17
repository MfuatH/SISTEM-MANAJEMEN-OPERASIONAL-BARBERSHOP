# Catatan Pengerjaan Slice 03 — Sesi Kapster

- **Pemeriksaan** 17 November 2026
- **Branch** `feat/slice-03-sesi-kapster`
- **Status** Disetujui dan di-merge ke `main` pada commit `ae14cd6`. [Pull request Slice 03](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/4)

## Dasar pengerjaan

Katalog layanan Slice 02 masuk ke `main` melalui PR #3 pada commit `808e9a6`. Kapster memulai sesi di beranda dengan nama berupa teks. Owner tetap memakai akun Owner dari Slice 01. [Catatan Slice 02](Catatan-Pengerjaan-Slice-02.md) · [Keputusan K2](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Pekerjaan yang diselesaikan

| Area | Perubahan |
| --- | --- |
| Beranda | Form menerima nama Kapster. Sesi yang sudah aktif dapat dilanjutkan. Pengguna juga dapat membuka login Owner dari beranda. |
| Penyimpanan sesi | Laravel menyimpan nama pada `barber_name` di tabel sesi bawaan. Memulai sesi baru meregenerasi sesi browser dan membersihkan konteks sebelumnya. |
| Dashboard | `/kapster` menampilkan nama aktif, akses ke tombol **Tambah**, tabel pencatatan dengan keadaan awal kosong, dan tombol **Akhiri sesi**. Dashboard dan pratinjau memerlukan sesi Kapster. |
| Pratinjau pencatatan | Tombol **Tambah** membuka `/kapster/tambah`. Dropdown memakai layanan aktif dari katalog dan menampilkan harga saat opsi dipilih. Penyimpanan pencatatan dan pengisian tabel dashboard dijadwalkan pada Slice 05. |
| Pemisahan akses | Sesi Kapster terbatas pada alur Kapster. Login Owner yang berhasil menghapus konteks Kapster pada sesi browser yang sama. Owner yang masih login diarahkan kembali ke area Owner ketika mencoba membuka rute Kapster. |
| Akhir sesi | Tombol **Akhiri sesi** membersihkan konteks Kapster dan mengarahkan browser ke beranda. |

Slice 03 memakai skema yang telah tersedia dari Slice 00–02. Berkas utama meliputi [rute](../../routes/web.php), [kontroler sesi Kapster](../../app/Http/Controllers/BarberSessionController.php), [penjaga sesi](../../app/Http/Middleware/EnsureBarberSession.php), dan [tes fitur](../../tests/Feature/BarberSessionTest.php).

## Kesiapan frontend

**Landing Page Utama siap diintegrasikan.** Gunakan `GET /` untuk beranda dan `POST /kapster/sesi` dengan field `name` serta token CSRF untuk memulai sesi. Tampilkan opsi melanjutkan sesi aktif dan tautan masuk Owner ke `/owner/login`.

**Halaman Utama Kapster siap diintegrasikan.** Halaman `GET /kapster` memerlukan sesi Kapster yang aktif. Tombol **Akhiri sesi** memakai `POST /kapster/keluar`. Form penyimpanan layanan dan penjualan produk tersedia melalui revisi Slice 05. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#kesiapan-frontend)

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Suite Laravel | **28 tes dan 163 assertion lulus** pada MySQL 8.4.3. Skenario mencakup mulai sesi tanpa akun, masukan invalid, akses langsung tanpa sesi, akhir sesi, pergantian mode Owner, dan pratinjau layanan aktif. |
| Sesi database dan HTTP | Uji HTTP dengan driver sesi database memakai dua klien terpisah bernama sama. Mengakhiri satu sesi membiarkan sesi lain tetap aktif. Akses Owner dari sesi Kapster diarahkan ke login, dan POST tanpa token CSRF ditolak. |
| Katalog | Pratinjau memuat harga dan nama layanan aktif dari katalog. Layanan yang sudah diarsipkan dikeluarkan. |
| Format kode | Laravel Pint lulus untuk berkas PHP terkait. |

Uji ini mencakup pembatasan akses dalam KP-04 serta perlindungan sesi dan CSRF dalam KP-05. Isolasi baris pencatatan akan diuji setelah Slice 05 menyediakan penyimpanan pemasukan. [Kriteria penerimaan](Desain-Database-MySQL-Barbershop.md#9-penyesuaian-bukti-penerimaan-qmp)

## Panduan review

1. Checkout branch PR di Terminal Laragon. Jalankan `php artisan migrate` untuk menyiapkan migrasi dasar yang belum diterapkan.
2. Buka beranda dari sesi tamu, masukkan nama, dan pastikan dashboard Kapster menampilkan nama tersebut. Klik **Tambah**. Jika katalog kosong, masuk sebagai Owner terlebih dahulu untuk menambahkan data uji, kemudian keluar untuk memulai sesi Kapster.
3. Pilih layanan aktif dan periksa harga yang tampil. Buka rute Owner dari sesi Kapster dan periksa pengalihan ke login.
4. Akhiri sesi dan coba buka `/kapster` secara langsung. Ulangi dengan dua browser atau jendela privat terpisah yang memakai nama sama. Selanjutnya jalankan `php artisan migrate --env=testing` dan `php artisan test --env=testing`.

## Catatan uji manual dan keputusan untuk Slice 05

Pada 17 November 2026, pengguna melaporkan bahwa sesi Kapster dapat dimulai dengan nama, dilanjutkan setelah kembali ke beranda, dan diakhiri. Pengguna juga membuka dashboard Owner, katalog beserta tabel layanan, dan form tambah layanan.

Konfirmasi kebutuhan pada tanggal yang sama menetapkan bahwa nama Kapster diambil dari sesi. Nama pelanggan adalah keterangan teks opsional per catatan. Kebutuhan profil dan riwayat kunjungan pelanggan berada di luar cakupan, sehingga Slice 04 dilewati. Usulan Slice 05 menyimpan nilai kosong sebagai `NULL` dan menampilkan `—`. [Keputusan K1 di desain database](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

Keputusan K3 untuk Slice 05 menetapkan tepat satu layanan per pencatatan. Jika pelanggan mengambil dua layanan, Kapster mengisi dan menyimpan form dua kali. Kedua layanan menjadi dua catatan pemasukan yang sah. [Keputusan K3 di desain database](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Kelanjutan

Slice 05 dimulai dari merge Slice 03. Pekerjaan berikutnya menambahkan penyimpanan satu layanan per pencatatan, snapshot harga, dan perlindungan terhadap kirim ulang. [Catatan Pengerjaan Slice 05](Catatan-Pengerjaan-Slice-05.md)
