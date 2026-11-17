# Catatan Pengerjaan Slice 07 — Pengeluaran Owner

**17 November 2026 · Selesai dan di-merge ke `main` · Branch `feat/slice-07-pengeluaran-owner`**

[PR #11](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/11)

## Status pengerjaan

Slice 07 menyediakan pencatatan pengeluaran manual untuk Owner, riwayat bulan berjalan WIB, pencarian, total, dan unduhan Excel. Menu **Pengeluaran** ditautkan dari Home Owner. Dasarnya adalah autentikasi Slice 01 dan pola laporan Slice 06. [Desain database](Desain-Database-MySQL-Barbershop.md#3-kamus-tabel-lainnya) · [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md)

## Pengerjaan database dan backend

1. Migrasi `0001_01_01_000009_create_expenses_table.php` membuat tabel `expenses`. Tanggal operasional `incurred_on` memakai `DATE`, waktu simpan `created_at` memakai `DATETIME(6)` UTC, dan jumlah memakai `DECIMAL(15,0)` dengan batas positif. FK pencatat ke `users.id` memakai `RESTRICT` untuk hapus dan ubah. Indeks `(incurred_on, id)` mendukung riwayat berdasarkan periode. [Migrasi](../../database/migrations/0001_01_01_000009_create_expenses_table.php)
2. Owner mengisi tanggal, deskripsi, dan jumlah rupiah bulat. Server mengambil pencatat dari akun yang sedang login dan menampilkan referensi `EXP-{id}` dari primary key otomatis. **Aturan implementasi.** Nilai awal tanggal adalah hari ini dalam WIB. Owner dapat mencatat tanggal lain yang valid, dan tanggal tersebut menentukan periode laporan. [Validasi](../../app/Http/Requests/StoreExpenseRequest.php) · [Model](../../app/Models/Expense.php)
3. Form membawa UUID untuk setiap pencatatan. Kombinasi pencatat dan UUID unik di database. Pengiriman ulang dengan isi sama mengembalikan referensi yang sama. Penggunaan UUID yang sama dengan isi berbeda ditolak dan Owner diminta membuka form baru. [Controller](../../app/Http/Controllers/OwnerExpenseController.php)
4. Daftar memuat catatan dari awal bulan WIB hingga sebelum awal bulan berikutnya, diurutkan dari tanggal terbaru lalu ID terbaru. Pencarian menerima deskripsi atau ID seperti `EXP-42` dan `42`. Daftar, total, dan Excel memakai kriteria yang sama. Excel mencakup seluruh hasil pencarian, termasuk yang berada di halaman berikutnya. Riwayat lama tetap tersimpan untuk Rekap lintas periode pada Slice 09. [Kueri laporan](../../app/Services/OwnerExpenseReport.php) · [Ekspor](../../app/Services/OwnerExpenseWorkbook.php)

## Kesiapan frontend

Halaman Pengeluaran siap diintegrasikan dari `main`. Gunakan sesi login Owner yang sama dengan halaman Pemasukan.

| Rute | Penggunaan frontend |
| --- | --- |
| `GET /owner/pengeluaran` | Tampilkan periode bulan berjalan WIB, tabel dengan ID, tanggal, deskripsi dan jumlah, total hasil, serta paginasi. Kirim `q` sebagai parameter pencarian. |
| `GET /owner/pengeluaran/tambah` | Tampilkan form dengan tanggal awal sesuai WIB dan UUID yang disediakan server. |
| `POST /owner/pengeluaran` | Kirim `incurred_on`, `description`, `amount`, `request_key`, dan token CSRF. Pertahankan pesan validasi dan pesan sukses dengan referensi sistem. |
| `GET /owner/pengeluaran/ekspor` | Unduh `pengeluaran-YYYY-MM.xlsx`. Sertakan nilai `q` yang sama dengan daftar. |

Untuk tahap **Home Dashboard Owner dan Sidebar**, menu **Pengeluaran** dapat diarahkan ke daftar di atas. Kartu total dan grafik Home dikerjakan pada Slice 10. [Peta kesiapan sembilan tahap](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

## Bukti pengujian

`php artisan test --env=testing --no-ansi --compact` lulus dengan 51 tes dan 627 asersi, termasuk pengujian Pemasukan Slice 06. Tabel berikut merangkum bukti Slice 07.

| Pemeriksaan | Hasil pada MySQL 8.4.3 |
| --- | --- |
| Migrasi | Migrasi `000009` berhasil diterapkan, di-rollback, lalu diterapkan kembali pada `barbershop_test`. Migrasi sebelumnya tetap tercatat. |
| Validasi dan akses | Hanya Owner aktif dapat membuka rute Pengeluaran dan menyimpan catatan. Isian yang gagal validasi tidak menghasilkan pengeluaran. |
| Kirim ulang | Pengiriman kedua dengan UUID dan isi yang sama menghasilkan ID yang sama dan tetap satu baris. Isi berbeda untuk UUID yang sudah digunakan ditolak. |
| Periode WIB | Tanggal 31 Oktober dan 1 Desember berada di luar tampilan November. Pergantian bulan menampilkan periode baru, dan bulan kosong menampilkan Rp0. Catatan lama tetap tersimpan. |
| Rekonsiliasi | Dua catatan Rp75.000 dan Rp100.000 menghasilkan Rp175.000 di daftar dan XLSX. Pencarian `%_` secara literal menghasilkan satu catatan dan total Rp100.000 di keduanya. Selisih terhadap pembanding Rp0. |
| Format XLSX | Uji membuka file hasil unduhan, memeriksa nilai sel, dan memastikan deskripsi yang dimulai tanda sama dengan tersimpan tanpa formula. |
| Alur HTTP lokal | Login, token CSRF, simpan, kirim ulang, pencarian, dan unduh XLSX berhasil. Pembaca openpyxl memverifikasi dua baris, total numerik Rp175.000, serta selisih Rp0. Data dan file fiktif dibersihkan sesudah uji. |

[Uji fitur Pengeluaran Owner](../../tests/Feature/OwnerExpenseTest.php)

## Cara meninjau

1. Ambil `main` terbaru dengan PHP 8.5 dan MySQL 8.4 aktif. Periksa konfigurasi lokal mengarah ke `barbershop_dev`, lalu jalankan `php artisan migrate` sebelum membuka menu Pengeluaran.
2. Masuk sebagai Owner dan buka **Pengeluaran**, lalu klik **Tambah pengeluaran**. Catat dua pengeluaran fiktif Rp75.000 dan Rp100.000 pada bulan berjalan, misalnya dengan deskripsi `Uji perlengkapan` pada keduanya. Cocokkan referensi yang tampil dan total setelah mencari deskripsi tersebut. Jika deskripsi sudah dipakai, pilih istilah uji baru agar hasilnya tepat dua baris.
3. Ekspor dengan pencarian yang sama. Cocokkan dua baris dan total Rp175.000 pada Excel.
4. Untuk mengulang uji otomatis gunakan `php artisan migrate --env=testing` lalu `php artisan test --env=testing`.

## Kelanjutan

Slice 08 bergantung pada keputusan Owner terkait koreksi dan jejak audit sesuai K6. Slice 09 akan memakai data pemasukan dan pengeluaran untuk Rekap lintas periode. [Urutan pengerjaan](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)
