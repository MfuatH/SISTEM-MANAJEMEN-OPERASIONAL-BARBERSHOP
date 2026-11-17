# Catatan Pengerjaan Slice 06 — Pemasukan Owner

- **Pemeriksaan** 17 November 2026
- **Branch** `feat/slice-06-pemasukan-owner`
- **Status** Implementasi dan pengujian selesai untuk review.

## Dasar pengerjaan

Revisi Slice 05 disetujui setelah uji manual dan di-merge melalui PR #7 pada commit `3762ae0`. Slice 06 membaca transaksi dan rincian yang sudah tersimpan. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge)

## Pekerjaan yang diselesaikan

| Area | Perubahan |
| --- | --- |
| Akses Owner | Menu **Pemasukan** membuka `GET /owner/pemasukan`. Daftar dan `GET /owner/pemasukan/ekspor` menggunakan autentikasi serta pemeriksaan akun Owner aktif yang sudah ada. |
| Daftar | Satu baris per layanan atau penjualan produk. Baris memuat ID transaksi, waktu WIB, Kapster, pelanggan, jenis, item, jumlah, harga satuan, dan nilai. Keterangan pemakaian produk lama tampil jika tersedia. |
| Periode | Filter menggunakan awal bulan WIB inklusif sampai awal bulan berikutnya eksklusif. Kedua batas dikonversi ke UTC. Bulan baru tampil dengan periode baru, dan bulan kosong bernilai Rp0. |
| Pencarian | Cari nama item, Kapster, pelanggan, atau ID transaksi. Pencarian memperlakukan `%` dan `_` sebagai karakter literal. Total menjumlahkan seluruh hasil yang cocok, termasuk item di luar halaman aktif. |
| XLSX | Unduh seluruh hasil dari kueri yang sama. Kolom uang normal bertipe angka. Nominal yang melebihi 15 digit disimpan sebagai teks untuk menjaga presisi. Nama dan label dari masukan pengguna ditulis sebagai teks. Total lembar dijumlahkan dari item yang benar-benar ditulis. |
| Database | Menggunakan `IncomeItems::query()` dari Slice 05. Tidak ada migrasi untuk Slice 06. |

Ekspor memakai OpenSpout **5.14.0** dan aritmetika rupiah memakai Brick Math **1.0.0**. Keduanya tercatat di `composer.lock`. [Dokumentasi OpenSpout](https://github.com/openspout/openspout/blob/v5.14.0/docs/index.md)

[Lihat kueri laporan](../../app/Services/OwnerIncomeReport.php) · [Lihat penulis XLSX](../../app/Services/OwnerIncomeWorkbook.php) · [Lihat tes fitur](../../tests/Feature/OwnerIncomeTest.php)

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Suite Laravel di MySQL 8.4.3 | **47 tes dan 493 asersi lulus** pada `barbershop_test`. |
| Rekonsiliasi gabungan | Dua layanan Rp35.000 dan Rp15.000 serta dua unit produk Rp15.000 menghasilkan tiga baris dengan satu ID transaksi dan total Rp80.000. Selisih daftar, XLSX, dan pembanding Rp0. |
| Pencarian dan paginasi | Pencarian produk menghasilkan total Rp30.000 pada halaman dan XLSX. Pengujian dengan 26 item membuktikan ekspor memuat hasil di luar halaman pertama. |
| Batas WIB | Item sebelum dan tepat pada awal bulan masuk periode masing-masing. Bulan kosong menampilkan Rp0 dan menghasilkan XLSX dengan total Rp0. |
| Isi workbook dan akses | Uji Laravel membaca file XLSX dan XML lembar untuk memeriksa tipe kolom, ketiadaan formula, angka presisi besar, dan pembatasan akses. Uji HTTP login Owner, daftar, dan unduh XLSX memverifikasi total Rp30.000 dengan pembaca openpyxl. File unduhan uji dan data fiktif dibersihkan setelah pemeriksaan. |

## Cara meninjau

1. Di branch Slice 06 dengan PHP 8.5, jalankan `composer install` untuk memasang dependensi sesuai lockfile.
2. Mulai MySQL dan Laravel. Masuk sebagai Owner, lalu buka **Pemasukan**. Gunakan data pengembangan atau contoh layanan Rp35.000 dan penjualan dua produk seharga Rp15.000 per unit.
3. Cocokkan daftar, nilai setiap item, dan total. Gunakan kolom pencarian untuk memilih satu nama produk, lalu klik **Ekspor Excel** dan bandingkan jumlah baris serta total.
4. Untuk mengulang suite, jalankan `php artisan migrate --env=testing` lalu `php artisan test --env=testing`.

## Kelanjutan

Sesudah review Slice 06, lanjutkan ke Slice 07 untuk pencatatan dan ekspor pengeluaran Owner. Rekap lintas periode dijadwalkan pada Slice 09. [Urutan pengerjaan](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)
