# Rencana Pengerjaan Backend Sistem Manajemen Operasional Barbershop

**Penanggung jawab backend** Bahauddin Rahman Hakim

**Tim** Kelompok 6, Kelas 2023C

## 1. Kebutuhan operasional terkini

Sistem melayani satu lokasi. Stack yang disepakati adalah Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS, dengan Laragon untuk pengembangan lokal. Kebutuhan fitur mengikuti *Deskripsi Sistem (Terurut)* dan konfirmasi berikutnya. Project Charter dan QMP v1.1 mencatat lingkup dan kriteria penerimaan awal. QMP menetapkan Pangkas Rambut Upin Ipin 11 sebagai lokasi uji. [Desain database dan keputusan skema](Desain-Database-MySQL-Barbershop.md) · [Catatan fondasi Slice 00](Catatan-Pengerjaan-Slice-00.md)

| Area | Alur dan keluaran |
| --- | --- |
| Sesi Kapster | Kapster memasukkan nama berbentuk teks dari beranda. Form menampilkan harga otomatis untuk satu layanan yang dipilih. Server mengambil nama Kapster dari sesi dan menyimpan layanan serta harga saat pencatatan. Nama pelanggan dapat diisi sebagai keterangan teks opsional. Setiap layanan tambahan dicatat lewat simpan terpisah. |
| Owner | Owner, yang juga disebut Admin dalam deskripsi sistem, masuk dengan akun yang sama melalui username dan kata sandi. Area Owner memuat Home, Pemasukan, Pengeluaran, Rekap, dan Forecasting bila disetujui. |
| Pemasukan dan pengeluaran | Pemasukan berasal dari pencatatan Kapster. Owner mengisi tanggal, deskripsi, dan jumlah pengeluaran, lalu sistem membuat ID. Metode pembayaran dan struk keluar dari cakupan. |
| Periode dan ekspor | Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Home menampilkan dua total bulanan dan dua seri harian. Rekap memuat seluruh histori dengan filter bulan atau jenis. Pemasukan, Pengeluaran, dan Rekap mendukung pencarian dan ekspor Excel pada cakupan masing-masing. |
| Koreksi | Draf menempatkan tindakan koreksi yang diaktifkan di area Owner. Cakupan koreksi, kebutuhan pembatalan mandiri, dan jejak audit perlu diputuskan sebelum Slice 08. |
| Forecasting | Jika disetujui, Laravel memanggil satu model Regresi Linier Python secara internal. Tampilan memuat empat bulan aktual dan dua bulan prediksi yang diperbarui setiap bulan. Posisi dua bulan prediksi dan kebutuhan histori Juli–September 2026 masih perlu dikonfirmasi. |

QMP mencantumkan Andik Ari Mulyono sebagai penerima hasil. Kolom penerimaan akhir pada Charter masih terbuka. Manajer Proyek menyelaraskan pencatatan wewenang serta kriteria QMP yang terdampak perubahan alur sebelum penerimaan akhir. [Acuan tugas kuliah](../tugas-kuliah/README.md)

## 2. Urutan pengerjaan backend

| Slice | Pekerjaan | Dependensi dan kriteria selesai |
| --- | --- | --- |
| 00 | Fondasi Laravel dan MySQL, desain database, dan kontrak data awal. | Fondasi telah diuji. Ambil `main` terbaru untuk pekerjaan fitur setelah PR fondasi disetujui dan di-merge. |
| 01 | Login username dan akses Owner dengan `users` serta `sessions` bawaan. | Migrasi menambahkan username unik dan meniadakan atribut email akun. Provisioning melalui `php artisan owner:create`. Uji login, logout, dan akses langsung ke rute Owner dari sesi tamu. |
| 02 | Katalog dan harga layanan aktif. | Sudah di-merge ke `main`. [Catatan Slice 02](Catatan-Pengerjaan-Slice-02.md) |
| 03 | Sesi Kapster dengan nama teks dan pratinjau harga. | Di-merge ke `main` pada commit `ae14cd6`. Sesi menggunakan `sessions` bawaan. [Catatan Slice 03](Catatan-Pengerjaan-Slice-03.md) |
| 04 | Master pelanggan dilewati berdasarkan K1. | Nama pelanggan cukup menjadi keterangan opsional per catatan pada Slice 05. |
| 05 | Simpan satu layanan per pencatatan Kapster dan hasilkan satu pemasukan. | Selesai dan disetujui setelah pengecekan manual. Pengujian memeriksa nama pelanggan opsional, snapshot harga, kirim ulang, serta total Rp50.000 dengan selisih Rp0. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md) |
| 05 (revisi) | Kapster memakai dua form. **Layanan** memuat minimal satu layanan dengan produk opsional. **Produk** memuat minimal satu item penjualan tanpa layanan. Keduanya memakai katalog Owner. | Dalam review. Backfill catatan lama, penyimpanan atomik, kirim ulang, dan rekonsiliasi diuji di MySQL. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge) |
| 06–07 | Tampilkan Pemasukan Owner per item dan siapkan pengeluaran dengan ID buatan sistem. | Slice 06 dapat memakai `IncomeItems::query()` untuk rincian layanan dan penjualan. Daftar dan XLSX memakai cakupan bulan dan pencarian yang sama. |
| 08 | Koreksi Owner sesuai keputusan K6. | Tetapkan izin, aksi yang tersedia, perlakuan periode, dan histori audit sebelum migrasi. |
| 09–10 | Rekap lintas periode dan Home bulanan. | Gunakan sumber data yang sama untuk daftar, grafik, total, dan ekspor. Uji batas awal bulan, bulan tanpa transaksi, dan histori yang tetap tersedia di Rekap. |
| 11–12 | Impor histori dan Forecasting bila disetujui. | Konfirmasi bentuk file aktual, sumber per bulan, posisi prediksi, dan evaluasi galat. Simpan file impor di direktori privat. |
| 13–14 | Integrasi MySQL, UAT, penerapan, dan penyerahan. | Simpan hasil uji, temuan, perbaikan, pemeriksaan lingkungan target, dan keputusan penerimaan. |

ERD, kamus data, integritas, indeks, dan urutan migrasi dijabarkan di [Desain Database MySQL](Desain-Database-MySQL-Barbershop.md). Gunakan `DECIMAL` untuk rupiah dan aritmetika presisi eksak di backend. [MySQL DECIMAL](https://dev.mysql.com/doc/refman/8.4/en/fixed-point-types.html)

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
| KP-02 · Pencatatan dan pembukuan | Simpan pemasukan Kapster dan pengeluaran Owner. Uji kirim ulang serta kegagalan penyimpanan. Rekonsiliasi daftar, Rekap, dan XLSX dengan pembanding independen. Target selisih **Rp0**. |
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
