# Catatan Pengerjaan Slice 11 — Data Histori Forecasting

**Status** Implementasi dan pengujian selesai pada branch `feat/slice-11-data-historis`. Menunggu review ke `main`. [PR #17](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/17)

**Fondasi** Berangkat dari `main` commit `900a769`. Lingkungan uji memakai PHP 8.5.12, Laravel 13.42.0, MySQL 8.4.3 LTS, dan Python 3.14.0. Generator memakai pustaka standar Python. [Branch Slice 11](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/tree/feat/slice-11-data-historis)

## Cakupan pengerjaan

1. Membuat generator paket sintetis Juli–Oktober 2026 dengan seed default `20261124`. Asumsi demo adalah buka setiap hari pukul 09.00–21.00 WIB, dua Kapster fiktif, dan sekitar 60% nama pelanggan kosong. Satu transaksi dapat memuat beberapa layanan dan produk terjual, atau penjualan produk saja.
2. Menggunakan harga referensi Potong rambut Rp18.000, Keramas Rp5.000, Semir hitam Rp25.000, Bleaching dan Highlight masing-masing Rp65.000, serta Hair Tonic Rp15.000. Foto menjadi acuan pola dan harga contoh. Volume tetap mengikuti ketentuan 14–15 transaksi per hari.
3. Menambah tiga tabel histori, pilihan satu sumber per bulan, dan perintah untuk pratinjau, impor, perubahan sumber, serta pembacaan window. Pemasukan operasional, Pengeluaran, Home, Rekap, dan ekspor tetap membaca tabel operasional.
4. Menyediakan skema dan enam fixture JSON. Slice 12 memakai tiga bulan lengkap berurutan dan memprediksi satu bulan berikutnya. Target pada kontrak v1 adalah pemasukan kotor dari layanan dan produk terjual.

## Paket CSV privat

| Berkas | Isi |
| --- | --- |
| `manifest.json` | `schema_version` 1, versi dataset, jenis data, zona WIB, mata uang IDR, tanggal cakupan, dan SHA-256 tiap CSV. Manifest sintetis juga mencatat seed, versi generator, asumsi, serta harga. |
| `transactions.csv` | `source_transaction_id`, `recorded_at_wib`, `barber_name`, `customer_name`. Nama pelanggan boleh kosong. Tanggal waktu berformat `YYYY-MM-DD HH:MM:SS`, dalam WIB. |
| `transaction_items.csv` | `source_transaction_id`, `item_number`, `item_type`, `item_name`, `quantity`, `unit_price`, `amount`. Jenis item `service` atau `product`. Nominal rupiah utuh, dengan `amount = quantity × unit_price`. |
| `monthly_revenues.csv` | `month_start`, `revenue`, `transaction_count`, `complete`. Awal bulan berformat `YYYY-MM-01` dan kelengkapan bernilai 0 atau 1. Identitas dataset mengikuti manifest. |

CSV memakai UTF-8, koma, dan header sesuai urutan tabel. Impor membaca paket hanya dari `storage/app/private/imports/` dan memeriksa hash, duplikat, relasi item, cakupan tanggal, jumlah transaksi, serta total rupiah. Database menyimpan metadata dan total bulanan. Baris sintetis maupun aktual tetap berada pada berkas privat. Pemetaan buku/Excel aktual memerlukan contoh struktur file. Importer menerima paket aktual yang telah dinormalisasi ke `csv-v1` dengan `data_kind=actual`.

## Perilaku sumber dan integritas

- Impor ulang dengan manifest dan versi pemetaan yang sama memakai kembali batch semula. `--activate` memilih bulan lengkap yang sudah berakhir. Saat bulan memiliki sumber lain, seluruh aktivasi dan impor baru dibatalkan. Jalankan tanpa `--activate` untuk menyimpan batch terlebih dahulu, kemudian pilih bulan yang ingin diganti.
- `forecast:month-source … application` mencatat bahwa operator sudah mengecek kelengkapan bulan dari aplikasi, termasuk bulan Rp0 bila memang lengkap. `forecast:month-source … import --import-id=…` memilih batch yang mencakup bulan tersebut. Jendela data mensyaratkan bulan impor lengkap.
- Window memakai tepat tiga bulan sebelum target. Status `available` berarti ketiganya siap. `insufficient_data` menyertakan daftar bulan yang hilang, belum ditutup, atau belum lengkap. Setiap nilai memuat jenis dan referensi sumber untuk Slice 12. Window dengan sumber sintetis dan aktual berlabel `mixed`.
- Migrasi `0001_01_01_000010_create_forecasting_history_tables.php` memiliki rollback terarah saat tabel histori masih kosong. Untuk basis data berisi histori atau pilihan sumber, rollback ditolak untuk melindungi data.

## Cara meninjau

Dari root proyek, periksa koneksi MySQL lokal dan jalankan langkah di bawah. Untuk skema pengujian, tambahkan `--env=testing` pada perintah Artisan setelah memeriksa `php artisan db:show --env=testing`.

```powershell
php artisan db:show
php artisan migrate
python forecasting/generate_synthetic_history.py storage/app/private/imports/slice11-synthetic-20261124
php artisan forecast:history-import slice11-synthetic-20261124 --preview
php artisan forecast:history-import slice11-synthetic-20261124 --activate
php artisan forecast:history-window 2026-11
```

Untuk mencantumkan akun audit saat menyimpan, tambahkan `--owner=pemilik` dengan username Owner aktif pada database tersebut. Untuk mengganti pilihan sesuai hasil verifikasi, jalankan salah satu contoh ini. Ganti `1` dengan ID batch yang dilaporkan saat impor.

```powershell
php artisan forecast:month-source 2026-09 application
php artisan forecast:month-source 2026-09 import --import-id=1
```

## Bukti pengujian

| Bulan WIB | Transaksi | Pemasukan kotor sintetis |
| --- | ---: | ---: |
| Juli 2026 | 448 | Rp8.898.000 |
| Agustus 2026 | 450 | Rp9.010.000 |
| September 2026 | 438 | Rp8.573.000 |
| Oktober 2026 | 447 | Rp8.792.000 |
| **Total** | **1.783** | **Rp35.273.000** |

- Pemeriksaan independen pada CSV menemukan 123 hari dengan 14–15 transaksi per hari, 2.085 item, 1.069 nama pelanggan kosong, dan selisih Rp0 untuk tiap agregat. Menjalankan ulang seed yang sama pada paket yang sudah ada memeriksa bahwa isi semua berkas identik.
- Fixture pembanding menghasilkan 15 transaksi, 17 item, dan Rp277.000 dari 14 potong rambut, dua keramas, dan satu Hair Tonic.
- MySQL 8.4.3 di `barbershop_test` berhasil menerapkan, melakukan rollback pada skema kosong, lalu menerapkan kembali migrasi. Dua proses impor serentak memakai satu ID batch yang sama, menghasilkan empat agregat dan empat pilihan sumber. Window November berisi Agustus, September, dan Oktober dengan total persis sama. Batch uji serentak dibersihkan setelah pemeriksaan.
- `php artisan test --env=testing` lulus **65 tes, 912 assertion**, termasuk lima tes Slice 11. Cakupan mencakup pratinjau, impor ulang, hash salah, jumlah salah, rollback konflik pilihan, batas WIB, satu sumber pada periode tumpang tindih, bulan nol, dan bulan belum lengkap.
- Pint, `git diff --check`, skema dan enam fixture JSON diperiksa. Paket CSV hasil generator diabaikan Git.

## Kesiapan frontend

Tim frontend dapat memakai fixture `disabled`, `processing`, `insufficient_data`, `ready`, `stale`, dan `unavailable`. `ready` dan `stale` berisi tiga bulan historis dan satu proyeksi. Gunakan label asal data dan bulan pada respons. [Kontrak dan fixture v1](../../forecasting/contract/v1/README.md)

Slice 11 menambahkan CLI, tabel histori, dan artefak kontrak. Rute, layout, Blade, serta aset halaman yang dipakai frontend tetap sama. Endpoint Owner, model Python, MAE, serta pembaruan bulanan tersedia untuk review di branch Slice 12. [Catatan Slice 12](Catatan-Pengerjaan-Slice-12.md) Ketepatan prediksi lapangan menunggu histori aktual dan evaluasi.

Simulasi merge terhadap branch frontend lokal pada commit 773ce5b menemukan konflik lama di layout login Owner. Konflik yang sama muncul ketika branch tersebut dibandingkan langsung dengan main, dengan ketiga versi file yang identik. Integrasi layout tetap dikerjakan bersama tim frontend saat branch itu ditinjau.

## Referensi

[Desain database dan urutan migrasi](Desain-Database-MySQL-Barbershop.md#6-sumber-histori-dan-forecasting) · [Rencana backend dan kesiapan frontend](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)
