# Catatan Pengerjaan Slice 05 — Pencatatan Kapster

- **Pemeriksaan** 17 November 2026
- **Branch** `feat/slice-05-pencatatan-kapster`
- **Status** Selesai dan disetujui setelah pengecekan manual pada 17 November 2026. [PR Slice 05](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/6)

## Dasar pengerjaan

Slice 03 disetujui dan di-merge ke `main` melalui PR #4 pada commit `ae14cd6`. Slice 04 dilewati sesuai K1. Nama pelanggan cukup sebagai keterangan opsional per catatan. K3 menetapkan tepat satu layanan per pencatatan. Dua layanan dari satu kunjungan disimpan sebagai dua pencatatan. [Catatan Slice 03](Catatan-Pengerjaan-Slice-03.md) · [Keputusan skema](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

## Pekerjaan yang diselesaikan

| Area | Perubahan |
| --- | --- |
| Database | Migrasi `0001_01_01_000005_create_income_entries_table.php` membuat satu tabel pemasukan dengan FK wajib ke `services.id`, nominal rupiah positif, dan kunci permintaan unik. Satu baris adalah satu pencatatan dan satu sumber pemasukan. |
| Model | `IncomeEntry` memiliki relasi ke `Service`. Harga tersimpan sebagai desimal rupiah utuh dan waktu sebagai tanggal UTC berpresisi mikrodetik. |
| Form Kapster | **Tambah** menampilkan layanan aktif, harga otomatis, serta nama pelanggan opsional. **Simpan** mengirim pilihan ke server. Dashboard menampilkan waktu WIB, nama pelanggan jika ada, nama layanan, dan harga yang tersimpan. |
| Validasi dan snapshot | Server mengambil nama Kapster dari sesi dan memeriksa layanan serta harga di dalam transaksi database. Perubahan harga sebelum simpan meminta pengguna meninjau harga terbaru. Nama layanan dan harga yang sudah tersimpan memakai snapshot. |
| Sesi dan kirim ulang | UUID dan tanda tangan mengikat form ke sesi Laravel. Kendala unik dan pencocokan hash mencegah pemasukan ganda, termasuk ketika permintaan diterima serentak. Dashboard memakai sidik sesi untuk membatasi catatan yang dapat dibaca. |
| Periode | Implementasi awal menyimpan waktu server. Kebutuhan pencatatan mundur pada K6 masih terbuka. |

[Lihat migrasi](../../database/migrations/0001_01_01_000005_create_income_entries_table.php) · [Lihat model](../../app/Models/IncomeEntry.php) · [Lihat tes fitur](../../tests/Feature/BarberIncomeTest.php)

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Suite Laravel | **35 tes dan 276 asersi lulus** pada MySQL 8.4.3 di `barbershop_test`. Kasus Slice 05 mencakup nama opsional, nama Kapster dari sesi, validasi satu layanan, pergantian harga, arsip layanan, sesi bernama sama, dan pengiriman ulang. |
| Rekonsiliasi | Layanan A Rp35.000 dan Layanan B Rp15.000 disimpan terpisah. Jumlah sebenarnya **2 baris dengan total Rp50.000**. Selisih terhadap perhitungan pembanding **Rp0**. |
| HTTP dan pengiriman bersamaan | Dua proses PHP pada port lokal berbeda menerima form yang sama secara bersamaan. Keduanya mengarah kembali ke dashboard. Setelah simpan layanan kedua, MySQL tetap berisi dua baris dengan total Rp50.000. Dua sesi bernama Bayu juga diperiksa. Form dan catatan sesi pertama tidak bisa dipakai oleh sesi kedua. |
| Harga pada form | JavaScript dari halaman yang disajikan lewat HTTP diperiksa untuk memastikan harga dan nilai yang dikirim berubah saat layanan dipilih. |
| Migrasi | Penerapan, rollback khusus Slice 05, dan penerapan ulang berhasil pada database uji. Lima migrasi sebelumnya tetap berstatus `Ran` setelah rollback. |
| Format kode | Laravel Pint lulus pada berkas PHP yang berubah. |

Pengujian ini memberi bukti awal untuk KP-02, KP-04, dan KP-05. Bukti pencarian, ekspor, dan Rekap disiapkan pada slice yang membaca catatan tersebut. [Kriteria penerimaan](Desain-Database-MySQL-Barbershop.md#9-penyesuaian-bukti-penerimaan-qmp)

## Cara meninjau

1. Di branch Slice 05 dengan PHP 8.5 dan MySQL Laragon aktif, jalankan `php artisan migrate` pada `barbershop_dev`, lalu jalankan `php artisan serve`.
2. Masuk sebagai Owner dan pastikan ada dua layanan aktif. Gunakan data fiktif Layanan A Rp35.000 dan Layanan B Rp15.000 bila katalog kosong.
3. Buka beranda sebagai tamu, mulai sesi dengan nama Bayu, lalu klik **Tambah**. Simpan Layanan A dengan nama pelanggan Andi. Klik **Tambah** lagi dan simpan Layanan B dengan nama kosong. Dashboard menampilkan dua catatan dan menunjukkan tanda `—` untuk nama kosong.
4. Ubah harga katalog Layanan A sebagai Owner di browser terpisah. Pastikan catatan lama tetap Rp35.000 dan form baru menampilkan harga terbaru.
5. Untuk mengulang suite, jalankan `php artisan migrate --env=testing` lalu `php artisan test --env=testing`.

### Jika tabel pemasukan belum ditemukan

Pesan `barbershop_dev.income_entries doesn't exist` saat membuka dashboard berarti migrasi Slice 05 belum diterapkan pada database pengembangan. Dari terminal PHP 8.5, jalankan `php artisan migrate:status` untuk memeriksa database pengembangan, lalu jalankan `php artisan migrate` jika migrasi `0001_01_01_000005_create_income_entries_table` masih `Pending`. Setelah statusnya `Ran`, muat ulang dashboard.

Saat menindaklanjuti uji manual, migrasi tersebut diterapkan pada `barbershop_dev`. Dashboard Kapster dan form Tambah kemudian berhasil dibuka dengan status HTTP 200 melalui server lokal yang sedang berjalan.

## Revisi kebutuhan setelah merge

**Status revisi** Disetujui dan di-merge ke `main` pada commit `3762ae0` melalui branch `feat/slice-05-multi-layanan`. Form **Layanan** memiliki bagian layanan dan penjualan produk opsional. Form **Produk** mencatat penjualan saja. Katalog layanan Owner menyediakan **Aktifkan kembali** dan **Hapus** sesuai riwayat layanan. [PR #7](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/7) · [Desain revisi](Desain-Database-MySQL-Barbershop.md#11-usulan-revisi-slice-05--transaksi-multi-layanan-dan-pilihan-produk)

**Acuan lapangan** Dua foto rekap manual menunjukkan perhitungan harian Rp344.000 dan rekap komisi bulanan yang memisahkan komisi jasa dari komisi produk. Pengguna menetapkan rekap komisi bulanan di luar lingkup aplikasi. [Pembacaan dan hitungan rekap manual](Desain-Database-MySQL-Barbershop.md#acuan-rekap-manual)

### Implementasi revisi

- Tiga migrasi menambah katalog produk, transaksi induk, dan rincian penjualan. Setiap catatan lama dipasangkan ke satu transaksi induk dengan ID item, waktu, nama, nominal, dan kunci kirim ulang yang sama.
- Owner dapat menambah, memperbarui, dan mengarsipkan produk. Produk aktif dengan harga jual tersedia di form penjualan.
- Owner dapat mengaktifkan kembali layanan yang diarsipkan dan menghapus layanan yang belum tercatat. FK melindungi riwayat untuk layanan yang dipakai pada transaksi.
- Server memeriksa item dan harga katalog pada saat simpan. Transaksi dan semua item disimpan sekaligus. Kunci unik pada induk melindungi pengiriman ulang.
- Dashboard Kapster mengambil catatan per item dari `IncomeItems::query()`. Nilai produk terjual adalah jumlah kali harga satuan saat pencatatan. Riwayat pemakaian produk dari versi sebelumnya tetap dapat dibaca.

### Bukti uji revisi

| Pemeriksaan | Hasil |
| --- | --- |
| Laravel dan MySQL 8.4.3 | **42 tes dan 388 asersi lulus**. Kasus mencakup transaksi gabungan, penjualan saja, snapshot harga, kirim ulang, pembacaan pemakaian lama, pengaktifan kembali arsip, dan perlindungan layanan pada transaksi. |
| Rekonsiliasi | Dua layanan Rp35.000 dan Rp15.000 ditambah dua unit produk Rp15.000 menghasilkan tiga item dengan total **Rp80.000**. Penjualan dua unit tanpa layanan menghasilkan satu item senilai **Rp30.000**. Selisih terhadap pembanding **Rp0**. |
| Uji browser Edge | Form Layanan menampilkan dua kolom di desktop dan satu kolom di layar sempit. Layanan Rp35.000 dan penjualan Rp15.000 tersimpan sebagai dua baris senilai Rp50.000. Uji revisi awal juga memeriksa kirim ulang paralel pada transaksi Rp80.000 dan penjualan produk saja Rp30.000. |
| Migrasi catatan lama | Catatan fiktif Rp35.000 mempertahankan ID item, waktu hingga mikrodetik, nama, nominal, dan kunci kirim ulang saat migrasi, rollback, serta penerapan ulang di `barbershop_test`. Skema lama hanya dapat memuat transaksi satu layanan tanpa produk. |
| Pemeriksaan format | Laravel Pint lulus pada berkas PHP yang berubah. |

### Cara meninjau revisi

1. Gunakan PHP 8.5 dan MySQL Laragon. Jalankan `php artisan migrate` pada `barbershop_dev` sebelum membuka dashboard pada branch revisi.
2. Sebagai Owner, siapkan Layanan A Rp35.000, Layanan B Rp15.000, dan satu produk dengan harga jual Rp15.000.
3. Mulai sesi Kapster. Klik **Layanan**, pilih dua layanan di bagian **Layanan**, lalu tambahkan dua unit di bagian **Produk dijual (opsional)**. Periksa total Rp80.000 sebelum menyimpan. Dashboard menampilkan tiga baris dengan satu ID transaksi.
4. Klik **Produk**, pilih dua unit produk dengan harga satuan yang sama, dan simpan. Dashboard seharusnya menampilkan satu baris penjualan baru senilai Rp30.000 dengan ID transaksi baru.
5. Untuk mengulang suite, jalankan `php artisan migrate --env=testing` lalu `php artisan test --env=testing`.

Untuk uji katalog, arsipkan layanan uji, periksa bahwa pilihan Kapster hilang, lalu klik **Aktifkan kembali**. Uji **Hapus** dengan layanan yang belum tercatat.

### Jika tabel transaksi induk belum ditemukan

Pesan `barbershop_dev.income_transactions doesn't exist` muncul jika kode revisi sudah berjalan dan migrasi revisi masih tertunda. Dari folder proyek dengan PHP 8.5, jalankan `php artisan migrate:status` untuk memeriksa database pengembangan. Jika migrasi `000006`–`000008` masih `Pending`, jalankan `php artisan migrate`. Setelah ketiganya berstatus `Ran`, muat ulang dashboard.

Saat uji manual, ketiga migrasi diterapkan pada `barbershop_dev`. Perbandingan ID, waktu, nominal, data sesi, dan kunci kirim ulang menunjukkan catatan lama tetap sama. Dashboard, form Layanan, dan form Produk masing-masing mengembalikan HTTP 200.

## Kelanjutan

Slice 06 sudah memakai rincian layanan dan penjualan produk untuk halaman Pemasukan Owner. Daftar bulan berjalan, pencarian, dan ekspor Excel memakai sumber per item yang sama. [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md)
