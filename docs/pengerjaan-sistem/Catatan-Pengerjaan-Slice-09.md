# Catatan Pengerjaan Slice 09 — Rekap Keuangan Lintas Periode

**18 November 2026 · Keputusan hasil bersih dikonfirmasi · Implementasi direncanakan**

## Status dan keputusan

Rekap Owner menggabungkan pemasukan per item dari layanan dan penjualan produk dengan pengeluaran manual dari Slice 07. **Hasil bersih tercatat** menjadi bagian wajib dari ringkasan Rekap dan ekspor Excel. Rumusnya adalah **total pemasukan − total pengeluaran** untuk periode yang sama. [Keputusan K11](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Pengerjaan yang direncanakan

1. Gunakan sumber pemasukan Slice 06 dan tabel `expenses` dari Slice 07. Hitung nilai dari data yang sudah tercatat menggunakan aritmetika rupiah eksak.
2. Tampilkan ringkasan **Total pemasukan**, **Total pengeluaran**, dan **Hasil bersih tercatat** dengan periode WIB yang sama. Pilihan bulan memakai awal bulan sampai sebelum awal bulan berikutnya. Tanpa pilihan bulan, gunakan seluruh periode.
3. **Usulan teknis.** Pencarian dan filter jenis membatasi daftar, total hasil daftar, dan rincian XLSX. Ringkasan periode tetap membaca kedua jenis agar hasil bersih mencakup seluruh pemasukan dan pengeluaran pada bulan yang dipilih. Gunakan label yang membedakan ringkasan periode dari total hasil pencarian.
4. Gunakan ringkasan periode yang sama pada halaman Rekap dan XLSX. Perhitungan memakai sumber yang sudah ada dan dapat ditambahkan tanpa migrasi tabel baru. [Desain laporan](Desain-Database-MySQL-Barbershop.md#5-sumber-laporan-grafik-dan-ekspor)

## Kesiapan frontend

Halaman Rekap masih dalam perencanaan. Wireframe dapat memuat tiga nilai ringkasan dengan label periode, filter bulan dan jenis, pencarian, tabel rincian, serta tombol **Ekspor Excel**. [Peta kesiapan frontend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

## Kriteria selesai dan rencana uji

| Skenario | Hasil yang diharapkan |
| --- | --- |
| Data September 2026 pada desain | Pemasukan Rp85.000, pengeluaran Rp20.000, dan hasil bersih Rp65.000 di halaman dan Excel. |
| Bulan tanpa catatan | Ketiga nilai adalah Rp0. |
| Pengeluaran melampaui pemasukan | Hasil bersih bertanda negatif, misalnya Rp20.000 − Rp25.000 = −Rp5.000. |
| Filter rincian berdasarkan jenis atau pencarian | Daftar dan rincian XLSX mengikuti filter. Ringkasan memakai kedua jenis transaksi dalam periode yang sama sesuai usulan teknis di atas. |

Saat implementasi, cocokkan ketiga nilai pada halaman dan Excel dengan data pembanding. Target selisih terhadap nilai pembanding adalah **Rp0**. [Data uji fiktif](Desain-Database-MySQL-Barbershop.md#8-data-uji-fiktif-dan-rekonsiliasi)
