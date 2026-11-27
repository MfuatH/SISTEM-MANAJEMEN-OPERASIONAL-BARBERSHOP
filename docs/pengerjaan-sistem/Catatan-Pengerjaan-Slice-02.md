# Catatan Pengerjaan Slice 02 — Katalog Layanan

- **Branch** `feat/slice-02-katalog-layanan`
- **Pull request** [PR #3 ke main](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/pull/3)
- **Status** Implementasi dan pengujian selesai. PR #3 telah di-merge ke `main` pada commit `808e9a6`.

---

## Dasar pengerjaan

Slice 02 dimulai dari `main` pada commit `34d6ace`, setelah login Owner dari Slice 01 di-merge. Implementasi mengikuti usulan K3 bahwa Owner mengelola katalog. Kasus uji memakai **Layanan A** dan **Layanan B** dengan nominal fiktif sambil menunggu daftar layanan resmi. [Catatan Slice 01](Catatan-Pengerjaan-Slice-01.md) · [Keputusan desain K3](Desain-Database-MySQL-Barbershop.md#2-perubahan-kebutuhan-dan-keputusan-terbuka)

---

## Pekerjaan yang diselesaikan

| Area | Perubahan |
| --- | --- |
| Skema katalog | Migrasi membuat tabel `services` dengan ID, nama, harga rupiah `DECIMAL(15,0)`, waktu arsip, serta waktu dibuat dan diperbarui. Indeks `(archived_at, name)` mendukung daftar aktif. Constraint MySQL mewajibkan harga positif. |
| Pengelolaan Owner | Dari `/owner/services`, Owner bisa melihat katalog, menambah layanan, mengubah nama atau harga, dan mengarsipkan layanan. Rute pengelolaan memeriksa sesi dan akun aktif. |
| Arsip | Sistem menyimpan waktu arsip di `archived_at`. Owner masih bisa melihat baris yang diarsipkan. Pengulangan aksi arsip mempertahankan waktu arsip yang sudah tercatat. |
| Daftar aktif | `GET /services/active` menyediakan data baca-saja yang dapat diakses tanpa login. Respons memuat ID dan nama layanan aktif, serta harganya sebagai teks angka rupiah. Endpoint ini disiapkan untuk form Kapster. |
| Validasi harga | Backend menerima rupiah utuh positif sesuai kapasitas kolom. Input nol, negatif, pecahan, boolean, atau melebihi batas kolom ditolak. |

Berkas utama dapat ditinjau melalui [migrasi katalog](../../database/migrations/0001_01_01_000004_create_services_table.php), [model layanan](../../app/Models/Service.php), [kontroler katalog](../../app/Http/Controllers/ServiceController.php), dan [tes fitur](../../tests/Feature/ServiceCatalogTest.php).

---

## Kesiapan frontend

**Katalog layanan siap diintegrasikan.** Di `main`, tombol **Katalog layanan** pada Pemasukan membuka `GET /owner/services` dalam pop-up untuk menampilkan daftar, harga, status arsip, dan tindakan katalog. Form tambah mengirim `name` dan `current_price` ke `POST /owner/services`. Pembaruan memakai `PUT /owner/services/{service}` dan pengarsipan memakai `PATCH /owner/services/{service}/archive`.

**Pilihan layanan Kapster siap digunakan.** `GET /services/active` mengembalikan layanan aktif dalam `data`, dengan `id`, `name`, dan `current_price`. Tindakan **Aktifkan kembali** serta **Hapus** ditambahkan pada revisi Slice 05. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#kesiapan-frontend) · [Pop-up Pemasukan](Catatan-Pengerjaan-Slice-10.md#kesiapan-frontend)

---

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Lingkungan | MySQL **8.4.3** pada `barbershop_test`. Migrasi juga diterapkan pada `barbershop_dev`. |
| Migrasi dan rollback | Migrasi maju, rollback pada katalog uji yang kosong, dan migrasi ulang lulus. Tabel `users` tetap tersedia saat migrasi katalog dibatalkan. |
| Suite Laravel | **19 tes dan 105 assertion lulus**, termasuk regresi autentikasi Owner. Skenario baru mencakup tambah layanan, ubah harga, arsip dan pengulangan arsip, penolakan akses tamu, daftar layanan aktif, serta validasi dan constraint harga. |
| Format kode | Laravel Pint lulus untuk berkas PHP yang berubah. |

Bukti ini mendukung KP-01 untuk katalog layanan dan KP-04 untuk pembatasan pengelolaan. Pemeriksaan harga historis pada transaksi dilakukan saat pencatatan Kapster tersedia di Slice 05. [Kriteria penerimaan](Desain-Database-MySQL-Barbershop.md#9-penyesuaian-bukti-penerimaan-qmp)

---

## Panduan review

Untuk meninjau alur terbaru dari `main`, buka **Pemasukan → Katalog layanan**. Langkah di bawah memakai branch implementasi awal. [Cara meninjau Slice 10](Catatan-Pengerjaan-Slice-10.md#cara-meninjau)

1. Checkout branch PR di Terminal Laragon dan jalankan `php artisan migrate` pada database lokal.
2. Masuk sebagai Owner, pilih **Katalog layanan**, dan tambah **Layanan A** dengan harga uji Rp35.000. Ubah harganya menjadi Rp40.000.
3. Buka `GET /services/active` dan cocokkan harga dengan nilai terakhir. Arsipkan Layanan A dan periksa bahwa statusnya tampil di halaman Owner serta daftar aktif sudah mengeluarkan layanan tersebut.
4. Coba buka `/owner/services` dari sesi tamu dan pastikan aplikasi mengarahkan ke login. Jalankan `php artisan migrate --env=testing` dan `php artisan test --env=testing` untuk mengulang pengujian otomatis.

---

## Riwayat dan kelanjutan

| Commit | Perubahan |
| --- | --- |
| [`19118ef`](https://github.com/MfuatH/SISTEM-MANAJEMEN-OPERASIONAL-BARBERSHOP/commit/19118ef7aec1bec163d486934d86ab5d5069c309) | Migrasi katalog, halaman pengelolaan Owner, daftar layanan aktif, dan pengujian. |

Slice 03 dimulai dari hasil merge PR #3 dan memakai katalog aktif untuk pratinjau di form Tambah. [Catatan pengerjaan Slice 03](Catatan-Pengerjaan-Slice-03.md). [Urutan pengerjaan per slice](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)
