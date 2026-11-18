# Catatan Pengerjaan Slice 10 — Home Dashboard Owner

**18 November 2026 · Dalam review · Branch `feat/slice-10-dashboard-owner`**

[PR #14 ke `main`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/14)

## Status dan lingkup

Slice 10 dimulai dari `main` setelah Rekap Slice 09 di-merge pada commit `3e8ef52`. Home Owner menampilkan kartu total pemasukan, pengeluaran, dan hasil bersih bulan berjalan serta grafik harian dari tanggal 1 sampai hari ini dalam WIB. Menu Home, Pemasukan, Pengeluaran, Rekap, dan katalog tetap tersedia pada navigasi Owner. [Catatan Slice 09](Catatan-Pengerjaan-Slice-09.md)

## Implementasi

- `OwnerFinancialEntries` menyediakan sumber yang dipakai Home dan Rekap. Pemasukan membaca nilai per layanan dan penjualan produk dari transaksi Kapster. Pengeluaran membaca nominal berdasarkan `expenses.incurred_on`. [Kueri bersama](../../app/Services/OwnerFinancialEntries.php)
- `OwnerDashboardReport` mengelompokkan data per tanggal WIB dan menjumlahkan nominal dengan presisi rupiah eksak. Grafik mengisi hari yang sudah berjalan tanpa catatan dengan Rp0. Kartu memakai seluruh bulan berjalan agar cocok dengan Rekap bulan yang sama. Hasil bersih dihitung dari total pemasukan dikurangi total pengeluaran dan dapat bernilai negatif. Jika Owner mencatat pengeluaran bertanggal mendatang dalam bulan ini, kartu memasukkannya pada total bulan tersebut dan grafik menampilkannya ketika tanggal tersebut tiba. [Agregasi Home](../../app/Services/OwnerDashboardReport.php)
- Grafik dibuka dalam tampilan **Batang** dengan kedua seri aktif. Owner memilih **Batang** atau **Garis** lewat radio button. Tombol **Pemasukan** dan **Pengeluaran** mengaktifkan seri secara independen pada grafik dan tabel harian. Jika kedua seri dimatikan, halaman meminta Owner mengaktifkan salah satunya. Kartu bulanan tetap menghitung seluruh periode. [Kontrol grafik](../../public/js/owner-dashboard.js)
- `GET /owner` tetap berada di area Owner dan menggunakan skema dari slice sebelumnya. Saat bulan berganti, Home membaca bulan baru. Rekap tetap dapat membaca catatan sebelumnya.

## Kesiapan frontend

Home siap diintegrasikan pada branch Slice 10. Controller menyuplai nilai berikut pada view `owner.home`.

| Data | Isi |
| --- | --- |
| `month` dan `today` | Awal bulan dan tanggal hari ini dalam WIB. Gunakan untuk judul periode dan batas grafik. |
| `totals` | `income`, `expenses`, dan `net` berupa string nominal rupiah tepat untuk tiga kartu bulan berjalan. |
| `days` | Satu entri per hari berisi `date` dengan format `YYYY-MM-DD`, `income`, dan `expenses`. Hari tanpa transaksi bernilai `"0"`. |

Sambungkan sidebar ke Home, Pemasukan, Pengeluaran, Rekap, Katalog Layanan, dan Katalog Produk. Grafik menggunakan skala yang sama pada tampilan Batang dan Garis. Gunakan radio button untuk memilih jenis grafik serta tombol dengan `aria-pressed` untuk mengaktifkan seri pada grafik dan tabel harian. Gunakan `POST /owner/logout` dengan CSRF untuk keluar. [Peta kesiapan frontend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

## Hasil pengujian

Suite Laravel lulus dengan **58 tes dan 797 asersi** pada MySQL 8.4.3 di `barbershop_test`, termasuk tes regresi akses Owner, daftar bulanan, dan Rekap.

| Pemeriksaan | Hasil |
| --- | --- |
| Sumber pemasukan | Dua layanan Rp35.000 dan Rp15.000 serta dua unit produk Rp17.500 menghasilkan Rp85.000. Kartu Home cocok dengan ringkasan Rekap bulan yang sama. |
| Grafik dan hari kosong | Grafik menampilkan Rp85.000 pemasukan pada hari pertama, Rp20.000 pengeluaran pada hari kedua, serta Rp0 pada hari ketiga. Biaya Rp5.000 bertanggal hari keempat sudah tercakup pada kartu bulan berjalan. |
| Batas periode | Pemasukan sebelum pukul 00.00 WIB masuk bulan sebelumnya. Saat periode berganti, Home menampilkan Rp0 jika bulan baru kosong dan catatan sebelumnya tetap muncul di Rekap. |
| Alur HTTP lokal | Login Owner membuka Home dengan grafik dan tabel harian. Kartu Home serta Rekap menunjukkan Rp65.000 pemasukan dan Rp20.000 pengeluaran untuk data fiktif yang sama. |
| Hasil bersih | Uji MySQL mencocokkan Rp85.000 − Rp25.000 = Rp60.000 dengan Rekap. Uji juga mencakup hasil bersih Rp0 dan −Rp5.000. |
| Interaksi grafik | Uji Edge memverifikasi gambar kedua seri pada Garis, perubahan ke Batang, tombol seri independen, pesan saat semua seri mati, dan tampilan pada lebar 390 piksel. Kartu bulanan cocok sepanjang pengujian. |
| Format dan akses | Laravel Pint serta pemeriksaan sintaks JavaScript lulus. Rute Home memakai autentikasi dan pemeriksaan akun Owner aktif yang ada. |

[Uji Home Owner](../../tests/Feature/OwnerDashboardTest.php)

## Cara meninjau

1. Ambil branch Slice 10, jalankan Laravel pada PHP 8.5 dan MySQL 8.4, lalu masuk sebagai Owner.
2. Buka **Home**. Periksa tiga kartu bulan berjalan. Pilih **Batang** atau **Garis**, lalu aktifkan dan matikan setiap seri. Buka **Lihat angka per hari** untuk mencocokkan nominal pada tanggal tertentu.
3. Buka **Rekap**, pilih bulan yang sama dengan kartu Home, lalu cocokkan total pemasukan, pengeluaran, dan hasil bersih. Rekonsiliasi memakai target selisih **Rp0** pada data dan periode yang sama.
4. Jalankan `php artisan test --env=testing` untuk mengulang suite pada database pengujian yang telah dimigrasikan.

## Kelanjutan

Setelah Slice 10 disetujui, Slice 11 dan 12 mengikuti keputusan tentang data historis serta Forecasting. Pengujian integrasi dan UAT dijadwalkan pada Slice 13. [Urutan pengerjaan](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)
