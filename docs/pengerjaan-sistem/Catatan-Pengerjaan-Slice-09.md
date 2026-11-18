# Catatan Pengerjaan Slice 09 — Rekap Keuangan Lintas Periode

**18 November 2026 · Dalam review · Branch `feat/slice-09-rekap-owner`**

[PR #13 ke `main`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/13)

## Status dan keputusan

Rekap Owner menggabungkan pemasukan per item dari layanan dan penjualan produk dengan pengeluaran manual dari Slice 07. **Hasil bersih tercatat** menjadi bagian wajib dari ringkasan Rekap dan ekspor Excel. Rumusnya adalah **total pemasukan − total pengeluaran** untuk periode yang sama. [Keputusan K11](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Implementasi

1. `OwnerRecapReport` memakai `IncomeItems::query()` dari Slice 06 dan tabel `expenses` dari Slice 07. Setiap layanan, penjualan produk, dan pengeluaran tampil sebagai satu baris. Rekap membaca skema yang sudah ada sehingga dapat dijalankan tanpa migrasi baru.
2. Ringkasan menampilkan **Total pemasukan**, **Total pengeluaran**, dan **Hasil bersih tercatat** untuk bulan yang dipilih dalam WIB atau seluruh periode saat bulan kosong. Aritmetika memakai rupiah eksak, termasuk nilai negatif.
3. Pencarian dan filter jenis menyaring rincian. **Total nominal rincian** menjumlahkan nominal semua baris yang sesuai, termasuk baris setelah halaman pertama. Ringkasan periode tetap mencakup pemasukan dan pengeluaran. Dengan filter Pengeluaran, misalnya, rincian hanya memuat biaya dan hasil bersih tetap memperhitungkan pemasukan.
4. Ekspor XLSX memuat seluruh hasil pada lembar **Rekap** dan tiga nilai periode pada lembar **Ringkasan**. Nilai hingga 15 digit disimpan sebagai angka. Nilai yang lebih panjang disimpan sebagai teks agar digit tetap utuh. [Desain laporan](Desain-Database-MySQL-Barbershop.md#5-sumber-laporan-grafik-dan-ekspor)

## Kesiapan frontend

Halaman Rekap siap diintegrasikan pada branch Slice 09 melalui `GET /owner/rekap`. Tombol **Ekspor Excel** memakai `GET /owner/rekap/ekspor` dengan parameter yang sama. Kedua rute memerlukan akun Owner aktif.

| Parameter | Penggunaan |
| --- | --- |
| `month` | Periode `YYYY-MM` atau kosong untuk seluruh histori. |
| `type` | `IN` untuk pemasukan atau `OUT` untuk pengeluaran. Kosong menampilkan semua jenis. |
| `q` | Pencarian nama item, deskripsi, Kapster, pelanggan, atau ID referensi. |
| `page` | Halaman rincian pada daftar. Ekspor mencakup semua hasil. |

Pertahankan label periode pada tiga nilai ringkasan, total nominal rincian, dan parameter yang sama pada tautan ekspor. Gunakan `/owner/rekap` pada menu Home atau sidebar Owner. [Peta kesiapan frontend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

## Hasil pengujian

Suite Laravel lulus dengan **56 tes dan 733 asersi** pada MySQL 8.4.3 di `barbershop_test`, termasuk lima tes Rekap serta uji regresi Slice 05–07.

| Skenario | Hasil |
| --- | --- |
| Data September 2026 pada desain | Pemasukan Rp85.000, pengeluaran Rp20.000, dan hasil bersih Rp65.000 di halaman dan Excel. |
| Bulan tanpa catatan | Ketiga nilai adalah Rp0. |
| Pengeluaran melampaui pemasukan | Hasil bersih bertanda negatif di layar dan XLSX. Angka negatif di atas 15 digit mempertahankan seluruh digit. |
| Filter rincian berdasarkan jenis atau pencarian | Daftar dan rincian XLSX mengikuti filter. Ringkasan memakai kedua jenis transaksi dalam periode yang sama. Uji 26 hasil memastikan XLSX mencakup baris di luar halaman pertama. |

Uji juga mencakup batas awal bulan WIB, akses khusus Owner, dan penyimpanan deskripsi sebagai teks pada Excel. Uji HTTP melalui login Owner berhasil membuka Rekap, menerapkan filter Pengeluaran, dan mengunduh file. Pembaca openpyxl memverifikasi isi kedua lembar dan hasil bersih Rp65.000. Data dan file fiktif dibersihkan setelah uji. Selisih terhadap data pembanding adalah **Rp0**. [Uji Rekap](../../tests/Feature/OwnerRecapTest.php) · [Data uji fiktif](Desain-Database-MySQL-Barbershop.md#8-data-uji-fiktif-dan-rekonsiliasi)

## Cara meninjau

1. Buka branch Slice 09 dan jalankan Laravel dengan PHP 8.5 serta MySQL 8.4. Jika migrasi sebelumnya masih tertunda pada database lokal, jalankan `php artisan migrate`.
2. Masuk sebagai Owner, buka **Rekap**, lalu pilih bulan yang berisi pemasukan dan pengeluaran. Periksa hasil bersih dengan mengurangkan kedua total pada ringkasan.
3. Pilih jenis **Pengeluaran** dan cari sebuah deskripsi. Klik **Ekspor Excel**. Cocokkan rincian pada lembar **Rekap** dan ketiga total pada lembar **Ringkasan** dengan halaman.
4. Untuk mengulang suite, jalankan `php artisan test --env=testing` pada database pengujian yang sudah dimigrasikan.

## Kelanjutan

Slice 08 ditunda berdasarkan keputusan 18 November 2026. Slice 10 dapat memakai sumber Rekap untuk dashboard bulanan setelah review Slice 09.
