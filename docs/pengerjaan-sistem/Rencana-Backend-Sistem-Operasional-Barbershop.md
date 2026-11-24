# Rencana Pengerjaan Backend Sistem Manajemen Operasional Barbershop

**Penanggung jawab backend** Bahauddin Rahman Hakim

**Tim** Kelompok 6, Kelas 2023C

## 1. Kebutuhan operasional terkini

Sistem melayani satu lokasi. Stack yang disepakati adalah Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS, dengan Laragon untuk pengembangan lokal. Kebutuhan fitur mengikuti *Deskripsi Sistem (Terurut)* dan konfirmasi berikutnya. Project Charter dan QMP v1.1 mencatat lingkup dan kriteria penerimaan awal. QMP menetapkan Pangkas Rambut Upin Ipin 11 sebagai lokasi uji. [Desain database dan keputusan skema](Desain-Database-MySQL-Barbershop.md) · [Catatan fondasi Slice 00](Catatan-Pengerjaan-Slice-00.md)

| Area | Alur dan keluaran |
| --- | --- |
| Sesi Kapster | Kapster memasukkan nama berbentuk teks dari beranda. Form **Layanan** menerima satu atau beberapa layanan dan penjualan produk opsional. Form **Produk** menerima penjualan produk. Harga muncul otomatis dari katalog. Server mengambil nama Kapster dari sesi dan menyimpan harga pada tiap item. Nama pelanggan dapat diisi sebagai keterangan opsional. |
| Owner | Owner, yang juga disebut Admin dalam deskripsi sistem, masuk dengan akun yang sama melalui username dan kata sandi. Area Owner memakai sidebar bersama untuk Home, Pemasukan, Pengeluaran, dan Rekap. Owner mengelola kedua katalog melalui pop-up di Pemasukan. Forecasting mengikuti persetujuan fitur. |
| Pemasukan dan pengeluaran | Pemasukan berasal dari pencatatan Kapster. Owner mengisi tanggal, deskripsi, dan jumlah pengeluaran, lalu sistem membuat ID. Metode pembayaran dan struk keluar dari cakupan. |
| Periode dan ekspor | Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Home menampilkan total pemasukan, pengeluaran, dan hasil bersih bulanan serta grafik harian Pemasukan dan Pengeluaran. Rekap memuat seluruh histori dengan filter bulan atau jenis. Pemasukan, Pengeluaran, dan Rekap mendukung pencarian dan ekspor Excel pada cakupan masing-masing. |
| Koreksi | Draf menempatkan tindakan koreksi yang diaktifkan di area Owner. Cakupan koreksi, kebutuhan pembatalan mandiri, dan jejak audit perlu diputuskan sebelum Slice 08. |
| Forecasting | Slice 11 menyediakan sumber pemasukan kotor dan kontrak untuk tiga bulan historis WIB dan satu bulan prediksi. Slice 12 menjadwalkan satu Regresi Linier Python secara internal melalui Laravel, evaluasi MAE, dan pembaruan bulanan. |

QMP mencantumkan Andik Ari Mulyono sebagai penerima hasil. Kolom penerimaan akhir pada Charter masih terbuka. Manajer Proyek menyelaraskan pencatatan wewenang serta kriteria QMP yang terdampak perubahan alur sebelum penerimaan akhir. [Acuan tugas kuliah](../tugas-kuliah/README.md)

## 2. Urutan pengerjaan backend

| Slice | Pekerjaan | Dependensi dan kriteria selesai |
| --- | --- | --- |
| 00 | Fondasi Laravel dan MySQL, desain database, dan kontrak data awal. | Fondasi telah diuji. Ambil `main` terbaru untuk pekerjaan fitur setelah PR fondasi disetujui dan di-merge. |
| 01 | Login username dan akses Owner dengan `users` serta `sessions` bawaan. | Migrasi menambahkan username unik dan meniadakan atribut email akun. Provisioning melalui `php artisan owner:create`. Uji login, logout, dan akses langsung ke rute Owner dari sesi tamu. |
| 02 | Katalog dan harga layanan aktif. | Sudah di-merge ke `main`. [Catatan Slice 02](Catatan-Pengerjaan-Slice-02.md) |
| 03 | Sesi Kapster dengan nama teks dan pratinjau harga. | Di-merge ke `main` pada commit `ae14cd6`. Sesi menggunakan `sessions` bawaan. [Catatan Slice 03](Catatan-Pengerjaan-Slice-03.md) |
| 04 | Master pelanggan dilewati berdasarkan K1. | Nama pelanggan cukup menjadi keterangan opsional per catatan pada Slice 05. |
| 05 | Pencatatan satu layanan berkembang menjadi transaksi multi-layanan dan penjualan produk. Katalog Owner juga mendukung pengaktifan kembali arsip dan penghapusan layanan yang belum tercatat. | Selesai dan di-merge pada commit `3762ae0`. Uji mencakup kedua form, backfill, perlindungan riwayat, dan rekonsiliasi. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge) |
| 06 | Pemasukan Owner per item, pencarian, total, dan ekspor XLSX bulan berjalan WIB. | Selesai dan di-merge pada commit `685dbc1`. Daftar dan XLSX memakai kueri yang sama. Pengujian memeriksa batas bulan, pencarian, dan selisih Rp0. [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md) |
| 07 | Pencatatan dan ekspor pengeluaran Owner dengan ID buatan sistem. | Selesai dan di-merge. Daftar, pencarian, total, dan XLSX mengikuti tanggal pengeluaran pada bulan berjalan WIB. [Catatan Slice 07](Catatan-Pengerjaan-Slice-07.md) |
| 08 | Koreksi Owner sesuai keputusan K6. | Ditunda. Tetapkan izin, tindakan, periode, dan histori audit saat pengerjaan dilanjutkan. |
| 09 | Rekap lintas periode. | Selesai dan di-merge. Rekap dan XLSX menampilkan ringkasan periode, termasuk hasil bersih tercatat, serta rincian sesuai filter. [Catatan Slice 09](Catatan-Pengerjaan-Slice-09.md) |
| 10 | Home bulanan. | Selesai dan di-merge. Kartu pemasukan, pengeluaran, hasil bersih, dan grafik harian WIB membaca sumber yang sama dengan Rekap. [Catatan Slice 10](Catatan-Pengerjaan-Slice-10.md) |
| 11 | Histori sintetis dan kontrak Forecasting v1. | Dalam review. Impor CSV privat, satu sumber per bulan, dan rekonsiliasi pada MySQL 8.4. [Catatan Slice 11](Catatan-Pengerjaan-Slice-11.md) |
| 12 | Regresi Linier, hasil bulanan, evaluasi, dan endpoint Owner. | Direncanakan setelah Slice 11 disetujui. Gunakan tiga bulan historis dan satu bulan target. |
| 13–14 | Integrasi MySQL, UAT, penerapan, dan penyerahan. | Simpan hasil uji, temuan, perbaikan, pemeriksaan lingkungan target, dan keputusan penerimaan. |

ERD, kamus data, integritas, indeks, dan urutan migrasi dijabarkan di [Desain Database MySQL](Desain-Database-MySQL-Barbershop.md). Gunakan `DECIMAL` untuk rupiah dan aritmetika presisi eksak di backend. [MySQL DECIMAL](https://dev.mysql.com/doc/refman/8.4/en/fixed-point-types.html)

## Kesiapan frontend per tahap

Tahap 1–8 mengikuti backend yang sudah masuk `main` sampai Slice 10. Tahap 9 mengikuti kontrak v1 pada branch Slice 11. Halaman yang berstatus **Siap integrasi** dapat disambungkan ke rute web Laravel yang tersedia. Saat mengganti tampilan Blade, pertahankan token CSRF, autentikasi berbasis sesi, redirect, dan pesan validasi.

| Tahap frontend | Status backend | Bagian yang siap dibuat frontend |
| --- | --- | --- |
| 1. Perancangan Wireframe | Acuan desain tersedia. | Rancang alur publik, Kapster, dan Owner berdasarkan desain database. Untuk halaman yang backend-nya masih direncanakan, gunakan contoh data pada wireframe dan tandai fitur opsional. [Catatan Slice 00](Catatan-Pengerjaan-Slice-00.md#kesiapan-frontend) |
| 2. Pengembangan Landing Page Utama | **Siap integrasi.** `GET /` menampilkan beranda. `POST /kapster/sesi` memulai sesi dengan field `name`. | Buat beranda, form mulai sesi Kapster, tautan untuk melanjutkan sesi aktif, dan tombol masuk Owner ke `/owner/login`. [Catatan Slice 03](Catatan-Pengerjaan-Slice-03.md#kesiapan-frontend) |
| 3. Pengembangan Halaman Utama Kapster | **Siap integrasi.** `GET /kapster` menampilkan riwayat sesi. `GET /kapster/tambah` dan `POST /kapster/pencatatan` menangani layanan. `GET /kapster/produk` dan `POST /kapster/produk` menangani penjualan produk. | Buat tabel pencatatan sesi, tombol **Layanan** dan **Produk**, form nama pelanggan opsional, pilihan item dan jumlah produk, harga serta total otomatis, dan tombol akhiri sesi lewat `POST /kapster/keluar`. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#kesiapan-frontend) |
| 4. Pengembangan Halaman Login Owner | **Siap integrasi.** `GET /owner/login` membuka form. `POST /owner/login` menerima `username` dan `password`. | Buat form login, tampilkan pesan validasi, dan ikuti redirect setelah login. Gunakan `POST /owner/logout` untuk aksi keluar. [Catatan Slice 01](Catatan-Pengerjaan-Slice-01.md#kesiapan-frontend) · [Catatan integrasi layout login](Catatan-Pengerjaan-Slice-10.md#integrasi-layout-login-owner) |
| 5. Pengembangan Home Dashboard Owner dan Sidebar | **Siap integrasi.** `GET /owner` menyediakan periode, total bulanan, serta seri pemasukan dan pengeluaran per tanggal WIB. | Tampilkan kartu pemasukan, pengeluaran, dan hasil bersih. Beri pilihan Batang atau Garis dan tombol seri pemasukan atau pengeluaran. Gunakan sidebar yang sama untuk Home, Pemasukan, Pengeluaran, dan Rekap. Pertahankan aksi keluar berbasis sesi. [Catatan Slice 10](Catatan-Pengerjaan-Slice-10.md#kesiapan-frontend) |
| 6. Pengembangan Halaman Pemasukan | **Siap integrasi.** Slice 06 menyediakan `GET /owner/pemasukan` dan `GET /owner/pemasukan/ekspor` dengan parameter pencarian `q`. | Buat tabel satu baris per layanan atau penjualan produk, tampilan periode bulan berjalan WIB, pencarian, total hasil, paginasi, dan tombol ekspor Excel yang memakai pencarian yang sama. Tombol katalog layanan dan produk di Pemasukan membuka pop-up. [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md#kesiapan-frontend) · [Penempatan katalog](Catatan-Pengerjaan-Slice-10.md#kesiapan-frontend) |
| 7. Pengembangan Halaman Pengeluaran | **Siap integrasi.** Tersedia `GET /owner/pengeluaran`, `GET /owner/pengeluaran/tambah`, `POST /owner/pengeluaran` dan `GET /owner/pengeluaran/ekspor`. | Buat form tanggal, deskripsi, dan jumlah, lalu tampilkan referensi yang dibuat sistem. Buat tabel, pencarian, total, dan paginasi bulan berjalan WIB. Gunakan pencarian `q` yang sama untuk daftar dan ekspor. [Catatan Slice 07](Catatan-Pengerjaan-Slice-07.md#kesiapan-frontend) |
| 8. Pengembangan Halaman Rekap | **Siap integrasi.** Tersedia `GET /owner/rekap` dan `GET /owner/rekap/ekspor` dengan parameter `month`, `type`, dan `q`. | Buat tabel lintas periode, filter bulan dan jenis, pencarian, serta ringkasan pemasukan, pengeluaran, dan hasil bersih tercatat. Tampilkan total pendapatan kotor dan bersih di bawah tabel saat **Semua jenis** dipilih. Gunakan filter yang sama saat menautkan Excel. [Catatan Slice 09](Catatan-Pengerjaan-Slice-09.md#kesiapan-frontend) |
| 9. Pengembangan Halaman Forecasting | **Siap untuk pengembangan dengan fixture.** Slice 11 menyediakan histori dan kontrak respons v1. Endpoint direncanakan pada Slice 12. | Gunakan fixture enam status untuk grafik tiga bulan historis dan satu prediksi, label periode dan sumber sintetis, serta keadaan kosong dan gagal. [Kontrak v1](../../forecasting/contract/v1/README.md) · [Catatan Slice 11](Catatan-Pengerjaan-Slice-11.md#kesiapan-frontend) |

[Rangkuman di README](../../README.md#status-backend-dan-kesiapan-frontend) · [Desain database](Desain-Database-MySQL-Barbershop.md)

## 3. Catatan historis jadwal awal

Project Charter mencatat rencana awal delapan minggu. Tabel ini merangkum target awal per tahap. Pengurutan pekerjaan aktif mengikuti [rincian slice dan dependensi](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice).

| Waktu rencana awal | Target |
| --- | --- |
| Minggu 1–2 | Pemetaan kebutuhan dan kriteria penerimaan. |
| Minggu 3 | Perancangan ERD, kamus data, dan kontrak frontend. |
| Minggu 4 | Migrasi, data awal, akses, dan alur pencatatan inti. |
| Minggu 5 | Integrasi laporan dan fitur pendukung. |
| Minggu 6–7 | Pengujian, UAT, perbaikan, dan pemeriksaan ulang. |
| Minggu 7 | Penyiapan lingkungan dan pemeriksaan setelah penerapan. |
| Minggu 8 | Dokumentasi, evaluasi, dan penerimaan akhir. |

## 4. Bukti yang harus disiapkan backend

| Kriteria QMP awal | Pemeriksaan pada rancangan terkini |
| --- | --- |
| KP-01 · Data operasional | Uji katalog, harga, dan pengarsipan sesuai izin. Selaraskan cakupan data Kapster dan pelanggan dengan keputusan desain. |
| KP-02 · Pencatatan dan pembukuan | Simpan pemasukan Kapster dan pengeluaran Owner. Uji kirim ulang serta kegagalan penyimpanan. Rekonsiliasi daftar, Rekap, dan XLSX dengan pembanding independen. Cocokkan pemasukan, pengeluaran, dan hasil bersih tercatat pada periode yang sama. Target selisih **Rp0**. |
| KP-03 · Dashboard | Cocokkan total bulan berjalan dan grafik harian, termasuk batas pergantian bulan. Selaraskan kriteria mingguan dari rancangan awal dengan tampilan terbaru. |
| KP-04 · Autentikasi dan akses | Uji login Owner, akses langsung ke rute terbatas, dan cakupan dua sesi Kapster bernama sama. Periksa izin koreksi bila diterapkan. |
| KP-05 · Keamanan | Verifikasi sesi Laravel, hash kata sandi, validasi server, CSRF, serta HTTPS. Jika Forecasting dipakai, periksa pemanggilan Python secara internal. |
| KP-06 · Peramalan | Jika disetujui, uji empat aktual dan dua prediksi serta pembaruan bulanan. Gunakan data uji setelah periode latih untuk menilai galat. Catat **belum terverifikasi** jika pembanding belum memadai. Jika fitur dikeluarkan, selaraskan KP-06 dengan Sponsor/Owner. |
| KP-07 · Penyerahan | Catat versi aplikasi dan migrasi, hasil uji, catatan perbaikan, dan cara menjalankan ulang sistem. |

## 5. Persiapan tugas implementasi

Ambil satu slice per branch. Periksa migrasi yang sudah ada dan gunakan migrasi baru untuk setiap perubahan skema. Catat keputusan yang memengaruhi tabel terkait, contoh masukan dan keluaran untuk frontend, serta bukti uji MySQL sebelum mengajukan PR ke `main`. Gunakan data fiktif dari desain database saat memeriksa rekap dan ekspor. [Alur kerja Git dan GitHub](../../README.md#alur-kerja-git-dan-github)

## Dokumen acuan proyek

- *Deskripsi Sistem (Terurut)* dan konfirmasi kebutuhan berikutnya menjadi dasar alur terkini. Ringkasan acuannya tersedia pada bagian 1 dan [keputusan desain](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka).
- Project Charter, hlm. 1–2, mencatat lingkup, teknologi, batasan, dan jadwal awal.
- Quality Management Plan versi 1.1, hlm. 1 dan 4–6, mencatat kriteria penerimaan, bukti pengujian, dan UAT.
- Presentasi Manajemen Operasional Barbershop, slide 5–12 dan 15, memberi konteks observasi dan jadwal awal.

## Jadwal pelaksanaan aktual

Durasi pelaksanaan proyek adalah **15 minggu**. Rincian target mingguan dan milestone mengikuti keputusan tim.
