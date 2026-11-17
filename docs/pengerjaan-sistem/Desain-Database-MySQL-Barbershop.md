# Desain Database MySQL Sistem Manajemen Operasional Barbershop

**Kelompok 6, Kelas 2023C · Revisi kebutuhan · Draf untuk peninjauan**

Acuan kebutuhan fungsional adalah *Deskripsi Sistem (Terurut)* dari `deskripsi-sistem (2).md`, bagian 1–7, beserta konfirmasi kebutuhan lanjutan yang dicatat pada bagian 2. Sistem melayani satu lokasi. Kapster memulai sesi dengan nama dari beranda. Owner masuk menggunakan username dan kata sandi. Stack yang disepakati adalah Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS dengan Laragon. Perubahan terhadap lingkup dan kriteria penerimaan awal dicatat pada bagian 9. [Catatan fondasi Slice 00](Catatan-Pengerjaan-Slice-00.md) · [Acuan tugas kuliah](../tugas-kuliah/README.md)

Bagian 1–10 mencatat skema awal Slice 05 dan rencana slice lain. Lihat bagian 11 untuk perubahan transaksi dan produk pada revisi Slice 05.

## 1. ERD usulan

```mermaid
erDiagram
    USERS ||..o{ EXPENSES : mencatat
    USERS |o..o{ SESSIONS : direferensikan
    SERVICES ||..o{ INCOME_ENTRIES : dipilih
```

### Tabel `users`

**Isi dan fungsi.** Akun untuk login Owner dengan username dan kata sandi. Owner adalah sebutan lain untuk Admin. Kedua istilah merujuk pada akun dan izin yang sama. Slice 01 mengubah tabel bawaan melalui migrasi baru.

| Kolom | Tipe MySQL | Kunci dan fungsi | Status |
| --- | --- | --- | --- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | PK. Direferensikan oleh `expenses.recorded_by_user_id`. | Ada |
| `name` | `VARCHAR(255)` | Nama tampilan akun. | Ada |
| `email` | `VARCHAR(255)` pada fondasi | Atribut dari migrasi awal. | Dihapus pada Slice 01 |
| `email_verified_at` | `TIMESTAMP NULL` pada fondasi | Atribut dari migrasi awal. | Dihapus pada Slice 01 |
| `password` | `VARCHAR(255)` | Hash kata sandi Laravel. | Ada |
| `remember_token` | `VARCHAR(100) NULL` | Token bawaan jika fitur ingat saya dipakai. | Ada |
| `created_at`, `updated_at` | `TIMESTAMP NULL` | Waktu pembuatan dan pembaruan akun. | Ada |
| `username` | `VARCHAR(80) NULL`, ASCII | Unik dan tidak peka huruf besar/kecil. Normalisasi huruf kecil dan spasi tepi. Akun yang dibuat melalui perintah Owner wajib memiliki username. Baris lama tetap tersimpan dengan username kosong sehingga aksesnya belum aktif. | Ditambahkan pada Slice 01 |
| `disabled_at` | `DATETIME(6) NULL` | Waktu UTC saat akses akun dinonaktifkan. | Ditambahkan pada Slice 01 |

Satu Owner dapat mencatat nol hingga banyak pengeluaran. Satu record `sessions` bawaan dapat merujuk nol atau satu akun lewat `user_id`. Relasi ini bersifat logis karena migrasi bawaan memasang indeks pada `user_id`. Laravel menyimpan nama Kapster pada sesi tamu dan menyalinnya sebagai teks ke `income_entries.barber_name` saat menyimpan pemasukan. [Autentikasi Laravel](https://laravel.com/docs/13.x/authentication) · [Sesi Laravel](https://laravel.com/docs/13.x/session)

## 2. Perubahan kebutuhan dan keputusan terbuka

### Perbandingan dengan draf awal

| Area | Draf awal | Revisi |
| --- | --- | --- |
| Akun dan akses | Kasir dan Owner memakai akun Laravel. | Owner memakai username dan kata sandi. Kapster memulai sesi dari beranda dengan nama. |
| Pencatatan dan pemasukan | Checkout POS menghasilkan struk dan satu pemasukan per transaksi sah. | Satu simpan Kapster memuat tepat satu layanan dan menghasilkan satu pemasukan. Layanan tambahan dicatat lewat simpan terpisah. Pembuatan struk dihapus dari Slice 05 dan 06. |
| Data layanan, kapster, pelanggan | Tiga master terpusat, transaksi menunjuk pelanggan dan kapster. | Nama Kapster berasal dari sesi. Nama pelanggan adalah keterangan teks opsional per pencatatan. Layanan berasal dari katalog dan harganya tampil otomatis. |
| Pengeluaran | Kasir mencatat tanggal, deskripsi, dan nominal. | Owner memasukkan tanggal, deskripsi, dan jumlah. Sistem menerbitkan ID pengeluaran. Riwayat mendukung pencarian dan ekspor Excel. |
| Laporan | Dashboard harian, mingguan, dan bulanan dengan rekap periode. | Home, Pemasukan, dan Pengeluaran menampilkan bulan berjalan. Rekap menyimpan cakupan seluruh periode dan mendukung filter serta ekspor. |
| Peramalan | Satu Regresi Linier Python dengan proyeksi bulanan. | Fitur opsional. Empat bulan aktual dan dua bulan prediksi, diperbarui setiap bulan bila disetujui. |

### Keputusan dan dampak skema

| ID | Status dan keputusan | Dampak pada desain |
| --- | --- | --- |
| K1 | **Dikonfirmasi 17 November 2026.** Nama Kapster diambil dari sesi. Nama pelanggan diisi sebagai teks opsional per transaksi dan tidak memerlukan profil atau riwayat kunjungan. | Slice 04 dilewati. Revisi Slice 05 menyimpan nama pelanggan pada `income_transactions.customer_name VARCHAR(120) NULL`. Masukan kosong disimpan sebagai `NULL`, dan daftar menampilkan `—` untuk nilai kosong. |
| K2 | **Dikonfirmasi.** Nama Kapster adalah teks, dengan nama sama diperbolehkan. **Keputusan teknis Slice 05.** Dashboard menampilkan pencatatan dari sesi browser aktif. | Slice 03 menyimpan `barber_name` pada sesi Laravel. Slice 05 menyalin nama itu ke pemasukan. Sidik SHA-256 dari ID sesi Laravel membatasi pembacaan, dan tanda tangan UUID mengikat satu form ke sesi yang sama. Kebutuhan melihat catatan lintas sesi oleh Kapster masih terbuka. |
| K3 | **Implementasi awal.** Slice 05 yang sudah di-merge menyimpan satu layanan per pencatatan. **Kebutuhan revisi.** Form Layanan dapat mencatat beberapa layanan dalam satu transaksi. Owner melihat satu baris per item. Harga muncul otomatis saat layanan dipilih. | Slice 02 menyediakan katalog layanan aktif yang dikelola Owner. Usulan revisi menambahkan transaksi induk dan mempertahankan snapshot layanan serta harga per item. Daftar layanan resmi masih terbuka. |
| K4 | **Dikonfirmasi.** Sistem membuat ID pengeluaran. | Gunakan `expenses.id` yang dibuat oleh database. Usulan format tampilan dan ekspor adalah `EXP-{id}`. |
| K5 | **Dikonfirmasi.** Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Rekap mencakup seluruh histori. | Pada pergantian bulan, halaman bulanan memakai rentang baru. Rekap tetap dapat membaca bulan sebelumnya dan menyediakan filter bulan atau jenis. |
| K6 | **Dikonfirmasi.** Pencatatan memakai layanan dan nominal, dengan aksi Kapster sebatas menambah. **Tafsiran draf.** Koreksi dan pembatalan yang diaktifkan berada di area Owner. | Slice 05 memakai waktu server dalam UTC. Kebutuhan mencatat pemasukan mundur masih terbuka. Sebelum Slice 08, pastikan cakupan koreksi atau pembatalan Owner, perlakuan periode, dan histori audit. Metode pembayaran dikeluarkan dari skema. |
| K7 | **Dikonfirmasi.** Owner dan Admin adalah dua sebutan untuk akun yang sama. | Satu jenis akun Owner. Slice 01 menambahkan username dan menghapus atribut email akun. |
| K8 | Apakah Forecasting disetujui, posisi prediksi di mana, dan apa yang diproyeksikan? Apakah data Juli–September 2026 masih diperlukan? | Usulan targetnya pemasukan bulanan, memakai empat bulan aktual terakhir yang sudah lengkap pada grafik dan dua bulan prediksi sesudahnya. Frasa “6 bulan kebelakang” juga dapat berarti dua prediksi atas bulan historis. Konfirmasi arti tersebut, sumber data, dan ketentuan baru sebelum Slice 11–12. |
| K9 | **Dikonfirmasi.** Dashboard Kapster memiliki tombol **Layanan** dan **Produk**. Form Layanan memuat minimal satu layanan dan bagian penjualan produk opsional. Form Produk mencatat penjualan saja. Owner mengisi katalog. | Pemasukan menjumlahkan layanan dan produk terjual. Kolom pemakaian produk dipertahankan untuk membaca catatan lama. Kebutuhan pencatatan stok masih terbuka. |
| K10 | **Dikonfirmasi.** Rekap komisi bulanan Kapster berada di luar lingkup aplikasi. | Foto rekap komisi menjadi acuan proses manual. Rancangan pemasukan hanya memakai nilai layanan dan penjualan produk. |

## 3. Kamus tabel lainnya

Pada tabel usulan, `ID` berarti `BIGINT UNSIGNED`, dengan `AUTO_INCREMENT` untuk PK tunggal `id`. `Rp` berarti `DECIMAL(15,0)` untuk rupiah utuh. `UTC` berarti `DATETIME(6)` berisi waktu UTC. Kolom wajib diisi kecuali dinyatakan `NULL`. PK adalah kunci primer, FK kunci asing, dan UQ kunci unik. Gunakan InnoDB, `utf8mb4`, dan hitung nominal dengan aritmetika presisi eksak. [MySQL DECIMAL](https://dev.mysql.com/doc/refman/8.4/en/fixed-point-types.html)

### Tabel `services` · implementasi Slice 02

**Isi dan fungsi.** Katalog pilihan layanan beserta harga aktif. Owner mengelola katalog di `/owner/services`. Endpoint baca-saja `GET /services/active` menyediakan `id`, `name`, dan `current_price` sebagai teks angka rupiah dari layanan dengan `archived_at IS NULL`. Satu layanan dapat dirujuk oleh banyak pemasukan.

| Kolom | Tipe | Kunci dan kegunaan |
| --- | --- | --- |
| `id` | `ID` | PK. Dirujuk oleh `income_entries.service_id`. |
| `name` | `VARCHAR(120)` | Label layanan pada dropdown. Gunakan nama placeholder untuk contoh dan pengujian selama daftar resmi masih terbuka. |
| `current_price` | `Rp` | Harga katalog saat ini, positif. |
| `archived_at` | `UTC NULL` | Saat layanan dikeluarkan dari pilihan aktif. |
| `created_at`, `updated_at` | `UTC` | Audit perubahan katalog. |

### Tabel `income_entries` · implementasi awal Slice 05

**Isi dan fungsi.** Satu baris per pencatatan Kapster yang berhasil disimpan. Setiap baris menunjuk tepat satu layanan, menyimpan nama Kapster dari sesi, dan memuat keterangan nama pelanggan jika diisi. Baris ini menjadi sumber pemasukan Owner, rekap, dan grafik.

| Kolom | Tipe | Kunci dan kegunaan |
| --- | --- | --- |
| `id` | `ID` | PK internal. |
| `barber_name` | `VARCHAR(120)` | Salinan nama Kapster dari sesi Laravel. Nama yang sama dapat muncul pada banyak catatan. |
| `customer_name` | `VARCHAR(120) NULL` | Teks opsional untuk keterangan pelanggan pada catatan tersebut. Atribut ini tidak memiliki FK. |
| `service_id` | `ID` | FK wajib ke `services.id`. Setiap pencatatan merujuk tepat satu layanan. |
| `service_name` | `VARCHAR(120)` | Salinan nama layanan saat simpan. |
| `amount` | `Rp` | Harga yang dicatat saat simpan. Tetap bernilai sama saat harga katalog berubah. |
| `recorded_at` | `UTC` | Waktu server saat pencatatan, sekaligus dasar periode pemasukan pada implementasi awal. |
| `session_scope` | `BINARY(32)` | SHA-256 ID sesi Laravel untuk membatasi daftar Kapster ke sesi aktif. Cakupan teknis ini bersifat per sesi browser dan tidak memiliki FK. |
| `request_key`, `request_hash` | `CHAR(36)` ASCII kolasi biner, `BINARY(32)` | UQ `request_key`. UUID per permintaan simpan dan SHA-256 payload. Kunci ini mengidentifikasi percobaan pencatatan. |

### Tabel `expenses` · usulan

**Isi dan fungsi.** Setiap baris menyimpan satu pengeluaran manual yang dicatat oleh satu Owner. Nilai ini dipakai untuk riwayat pengeluaran, rekap, dan grafik.

| Kolom | Tipe | Kunci dan kegunaan |
| --- | --- | --- |
| `id` | `ID` | PK yang dibuat otomatis. Dipakai untuk tampilan ID pengeluaran, dengan format usulan `EXP-{id}`. |
| `incurred_on` | `DATE` | Tanggal pengeluaran dari form dalam kalender operasional. |
| `description` | `VARCHAR(255)` | Keterangan transaksi. |
| `amount` | `Rp` | Jumlah pengeluaran, positif. |
| `recorded_by_user_id` | `ID` | FK ke `users.id`. Diisi dari akun Owner yang aktif di server. |
| `created_at` | `UTC` | Waktu penyimpanan untuk audit, termasuk jika tanggal kejadian berbeda. |
| `request_key`, `request_hash` | `CHAR(36)` ASCII kolasi biner, `BINARY(32)` | UUID permintaan dan SHA-256 payload. UQ gabungan `(recorded_by_user_id, request_key)`. |

### Tabel pendukung Laravel yang sudah ada

Tabel berikut digunakan oleh infrastruktur Laravel. `sessions.user_id` dan `password_reset_tokens.email` menyimpan rujukan logis akun. Migrasi bawaan mempertahankan kedua rujukan sebagai kolom biasa. Tipe `VARCHAR` berikut memakai panjang 255 jika panjangnya tidak ditulis.

| Tabel | Kolom, tipe, dan kunci | Isi dan kegunaan |
| --- | --- | --- |
| `sessions` | `id VARCHAR PK`, `user_id BIGINT UNSIGNED NULL` berindeks, `ip_address VARCHAR(45) NULL`, `user_agent TEXT NULL`, `payload LONGTEXT`, `last_activity INT` berindeks | Penyimpanan sesi HTTP. Slice 03 menyimpan nama Kapster pada payload sesi tamu. Slice 05 menandatangani kunci form menggunakan ID sesi yang sudah ada. |
| `password_reset_tokens` | `email VARCHAR PK`, `token VARCHAR`, `created_at TIMESTAMP NULL` | Penyimpanan token reset kata sandi bila alur reset diaktifkan. |
| `cache` | `key VARCHAR PK`, `value MEDIUMTEXT`, `expiration BIGINT` berindeks | Nilai dan masa berlaku cache database. |
| `cache_locks` | `key VARCHAR PK`, `owner VARCHAR`, `expiration BIGINT` berindeks | Kunci proses yang dipakai cache Laravel. |
| `jobs` | `id ID PK`, `queue VARCHAR` berindeks, `payload LONGTEXT`, `attempts SMALLINT UNSIGNED`, `reserved_at INT UNSIGNED NULL`, `available_at INT UNSIGNED`, `created_at INT UNSIGNED` | Antrean pekerjaan Laravel. |
| `job_batches` | `id VARCHAR PK`, `name VARCHAR`, `total_jobs INT`, `pending_jobs INT`, `failed_jobs INT`, `failed_job_ids LONGTEXT`, `options MEDIUMTEXT NULL`, `cancelled_at INT NULL`, `created_at INT`, `finished_at INT NULL` | Status kelompok pekerjaan antrean. |
| `failed_jobs` | `id ID PK`, `uuid VARCHAR UQ`, `connection VARCHAR`, `queue VARCHAR`, `payload LONGTEXT`, `exception LONGTEXT`, `failed_at TIMESTAMP` dengan nilai awal waktu saat ini. Indeks `(connection, queue, failed_at)`. | Catatan pekerjaan yang gagal. Tipe kolom dan indeks mengikuti hasil inspeksi MySQL pengujian. |
| `migrations` | `id INT UNSIGNED AUTO_INCREMENT PK`, `migration VARCHAR`, `batch INT` | Riwayat file dan batch migrasi yang telah dijalankan. |

## 4. Integritas dan alur penyimpanan

- **Akun Owner.** Gunakan username unik dan kata sandi yang di-hash Laravel. Login dan rute Owner memeriksa bahwa akun telah diprovisikan sebagai Owner sesuai K7 dan masih aktif. Regenerasi ID sesi saat login dan invalidasi saat logout. Saat menonaktifkan akun, cabut sesi aktifnya. Tambahkan rate limit login, validasi server, proteksi CSRF, dan HTTPS pada lingkungan target. [Autentikasi Laravel](https://laravel.com/docs/13.x/authentication)
- **Sesi Kapster.** Slice 03 memvalidasi nama dari beranda dan menyimpannya sebagai teks pada sesi tamu Laravel. Dashboard dan form Tambah memerlukan sesi Kapster. Memulai sesi baru atau masuk sebagai Owner membersihkan konteks Kapster lama. Slice 05 membatasi dashboard ke sidik sesi browser aktif. Nama Kapster disalin untuk keterangan pada pemasukan. [Sesi Laravel](https://laravel.com/docs/13.x/session)
- **Pemasukan.** Form memuat pilihan satu layanan, dengan teks `Pilih layanan` saat pilihan kosong. Harga layanan yang dipilih tampil sebagai nilai baca-saja. Server mengambil nama Kapster dari sesi, menormalisasi nama pelanggan opsional, dan memeriksa layanan aktif. Transaksi memakai kunci baca pada baris katalog saat memvalidasi dan menyimpan snapshot. Jika harga yang dikirim berbeda dari harga katalog, form memuat harga terbaru dan meminta simpan ulang. Satu simpan berhasil menghasilkan satu baris pemasukan. Untuk layanan tambahan, buka form baru dan simpan dengan kunci permintaan baru. [Transaksi database Laravel](https://laravel.com/docs/13.x/database#database-transactions)
- **Pengeluaran.** Owner mengisi tanggal, deskripsi, dan jumlah. Server memvalidasi tanggal lokal serta nominal dan mengambil pencatat dari sesi login. Setelah simpan, tampilkan ID yang dibuat oleh database sesuai format tampilan.
- **Kirim ulang.** Server menerbitkan UUID baru dan tanda tangan HMAC untuk satu form. Saat menyimpan atau mengulang, server memverifikasi tanda tangan terhadap sesi aktif. Payload dinormalisasi untuk membentuk hash, termasuk nama Kapster, nama pelanggan opsional, layanan, dan harga yang dikonfirmasi. Kunci dan hash yang sama merujuk ke pemasukan yang sudah tersimpan. Jika isi berubah untuk kunci yang sudah dipakai, server meminta form baru. Jika pengiriman serentak menghasilkan konflik UQ, server membaca catatan yang menang dan mencocokkan sidik sesi serta hash payload. Aturan pengeluaran dibahas pada Slice 07.
- **Data lama.** Semua FK domain memakai `ON DELETE RESTRICT` dan `ON UPDATE RESTRICT`. Owner dapat menghapus layanan yang belum pernah dicatat. Layanan yang sudah tercatat dapat diarsipkan dan diaktifkan kembali. Snapshot layanan dan nama Kapster dipertahankan untuk Rekap lintas periode.
- **Koreksi.** Tindakan koreksi dan pembatalan yang disetujui melewati pemeriksaan izin Owner. Usulan Slice 08 mencatat pelaku, waktu, alasan, serta tautan antara catatan asli dan pengganti. Saat koreksi, simpan audit dan pengganti secara atomik. Isi dan status histori mengikuti hasil keputusan K6.
- **Constraint.** Terapkan `CHECK` untuk harga dan jumlah positif. Validasi nama Kapster pada awal sesi serta panjang nama pelanggan jika diisi. Validasi keterisian deskripsi, format tanggal, rentang nominal, dan status aktif di Laravel. [MySQL CHECK](https://dev.mysql.com/doc/refman/8.4/en/create-table-check-constraints.html)

| Tabel | Indeks utama selain PK | Pemakaian |
| --- | --- | --- |
| `users` | UQ `username` | Identitas akun dan login. |
| `services` | `(archived_at, name)` | Daftar layanan aktif. |
| `income_entries` | `(recorded_at, id)`, `(session_scope, recorded_at, id)`, indeks `service_id`, UQ `request_key` | Rekap periode, dashboard Kapster per sesi, FK layanan, dan pemeriksaan kirim ulang. |
| `expenses` | `(incurred_on, id)`, UQ `(recorded_by_user_id, request_key)` | Rekap periode, FK pencatat, dan simpan ulang. Pencarian ID yang tepat memakai PK `id`. |

## 5. Sumber laporan, grafik, dan ekspor

| Halaman Owner | Sumber dan cakupan | Pencarian, filter, dan ekspor |
| --- | --- | --- |
| Home | Dua total dan dua seri harian dari awal bulan berjalan. | Hari yang sudah berjalan tanpa transaksi ditampilkan Rp0. |
| Pemasukan | `IncomeItems::query()` dari `income_entries` dan `income_product_sales`, dengan waktu dari induk `income_transactions`. Cakupan bulan berjalan WIB. | Cari nama item, Kapster, pelanggan, atau ID transaksi. Total dan XLSX mengikuti seluruh hasil pencarian. [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md) |
| Pengeluaran | `expenses` bulan berjalan, dengan tanggal, ID buatan sistem, deskripsi, dan jumlah. | Cari ID pengeluaran atau deskripsi. Ekspor Excel mengikuti pencarian dalam bulan berjalan. |
| Rekap | Seluruh periode dari dua sumber. Baris dinormalisasi ke jenis, ID sumber, tanggal, nama Kapster bila ada, label, dan nominal. | Cari teks pada baris yang secara konsisten diproyeksikan ke rekap. Filter opsional dengan bulan dan jenis `IN` atau `OUT`. Ekspor seluruh hasil untuk pencarian dan filter yang sama, termasuk baris di luar halaman saat ini. |

**Periode.** Gunakan kalender operasional `Asia/Jakarta` dan interval `[awal bulan, awal bulan berikutnya)`. Hitung batas pemasukan dalam UTC dan bandingkan langsung dengan `recorded_at`. Untuk pengeluaran, pakai tanggal lokal pada `incurred_on`. Saat bulan berganti, Home menampilkan Rp0 dan tabel Pemasukan serta Pengeluaran kosong sampai ada catatan untuk bulan baru. Riwayat tetap tersimpan dan dapat dibaca melalui Rekap. [Fungsi tanggal dan waktu MySQL](https://dev.mysql.com/doc/refman/8.4/en/date-and-time-functions.html)

**Rekap.** Gunakan `UNION ALL` untuk menyatukan hasil karena kedua sumber berisi kejadian yang berbeda. Identitas gabungan adalah `(entry_type, source_id)`. Urutkan berdasarkan tanggal dan identitas gabungan agar paginasi dan ekspor deterministik. Kueri contoh memakai satu bulan. Untuk Rekap tanpa pilihan bulan, kedua cabang dijalankan tanpa predikat tanggal. Tambahkan filter jenis dan pencarian sebagai parameter, dengan predikat yang sama untuk daftar dan ekspor.

```sql
WITH entries AS (
    SELECT 'IN' AS entry_type, i.id AS source_id,
           DATE(CONVERT_TZ(i.recorded_at, '+00:00', '+07:00')) AS business_date,
           i.barber_name, i.service_name AS label, i.amount
    FROM income_entries AS i
    WHERE i.recorded_at >= :utc_start AND i.recorded_at < :utc_end

    UNION ALL

    SELECT 'OUT', e.id, e.incurred_on, NULL, e.description, e.amount
    FROM expenses AS e
    WHERE e.incurred_on >= :local_start AND e.incurred_on < :local_end
)
SELECT entry_type, source_id, business_date, barber_name, label, amount
FROM entries
ORDER BY business_date DESC, entry_type, source_id DESC
```

Di implementasi, Laravel menerapkan satu pembentuk rentang dan sumber kueri laporan. Daftar dan XLSX memakai kueri yang sama dengan cakupan per halaman. Home memakai sumber keuangan yang sama tanpa kata kunci pencarian halaman lain. Grafik mengelompokkan per `business_date` dan `entry_type`; total bulanan menjumlahkan seluruh baris untuk jenis yang sama. Bila layar menampilkan saldo, rumusnya pemasukan dikurangi pengeluaran. XLSX menyimpan nominal sebagai angka selama muat dalam 15 digit presisi Excel. Nilai yang lebih panjang dan label masukan pengguna ditulis sebagai teks. Selisih antara layar, ekspor, dan pembanding dinilai pada data dan filter yang sama. Rekap, total bulanan, dan ekspor dihitung dari kueri tersebut.

Contoh SQL memakai offset WIB `+07:00`. Gunakan aturan zona yang sama untuk pembentukan batas dan pengelompokan tanggal. Tampilkan ID pengeluaran berdasarkan `source_id` pada baris `OUT`. Jika tindakan Owner pada K6 diimplementasikan, terapkan aturan status yang sama pada daftar, agregat, dan ekspor.

## 6. Forecasting opsional

**Keputusan terbuka K8.** Sumber menyebut enam bulan ke belakang dan juga empat bulan aktual ditambah dua bulan prediksi. Usulan untuk disepakati adalah menampilkan empat bulan lengkap terakhir sebagai aktual, diikuti dua bulan prediksi. Contohnya, dengan data aktual April–Juli, prediksi jatuh pada Agustus–September. Jika dua prediksi harus berada di dalam jendela historis, tetapkan sebagai evaluasi historis. Latih model dengan data yang tersedia sebelum bulan yang diuji dan tampilkan status aktual serta prediksi dengan jelas.

Jika disetujui, ambil omzet bulanan dari sumber pemasukan yang sudah direkonsiliasi. Gunakan satu model Regresi Linier Python yang dipanggil secara internal oleh Laravel. Perbarui saat bulan lokal baru mulai, setelah bulan sebelumnya lengkap. Simpan bukti periode latih/uji dan versi data. Empat bulan aktual pada grafik adalah cakupan tampilan. Jumlah bulan pelatihan dan kecukupan data evaluasi perlu disepakati. Data Juli–September 2026 pada Charter menyediakan tiga bulan jika lengkap, sehingga tampilan empat bulan aktual masih memerlukan bulan tambahan. Gunakan status data belum cukup dan catat evaluasi galat **belum terverifikasi** bila pembanding belum memadai.

Jika impor CSV/Excel lama tetap diminta, periksa contoh file, unit per baris, identitas catatan, tanggal, nominal, dan kelengkapan bulan. Simpan sumber secara privat. Pilih satu sumber tervalidasi per bulan, termasuk bulan yang juga memiliki data aplikasi. Agregat impor untuk peramalan tetap terpisah dari Rekap operasional sampai tim menetapkan kebutuhan penggabungan. Tabel berikut adalah opsi hanya untuk impor yang disetujui.

| Tabel opsional | Kolom, tipe, dan kunci | Isi dan kegunaan |
| --- | --- | --- |
| `historical_imports` | `id ID PK`, `source_name VARCHAR(255)`, `file_sha256 BINARY(32)`, `mapping_version VARCHAR(40)`, `imported_at UTC`. UQ `(file_sha256, mapping_version)`. | Versi impor dan identitas file yang direkonsiliasi. Satu batch terbit memuat satu atau lebih agregat bulanan. |
| `historical_monthly_revenues` | `import_id ID FK` ke `historical_imports.id`, `month_start DATE` tanggal 1, `sales_amount Rp` nonnegatif, `transaction_count INT UNSIGNED`. PK `(import_id, month_start)`. | Total pemasukan dan jumlah catatan valid per bulan untuk satu versi impor. |

## 7. Urutan migrasi Laravel yang diusulkan

Pemeriksaan repo menemukan tiga migrasi bawaan `0001_01_01_000000_create_users_table.php`, `0001_01_01_000001_create_cache_table.php`, dan `0001_01_01_000002_create_jobs_table.php`. `migrate:status --env=testing` menunjukkan ketiganya `Ran`. Ketiga migrasi yang terdaftar meliputi fondasi Laravel. Migrasi akun Slice 01 telah masuk ke `main` pada commit `34d6ace`. Slice 02 menambahkan migrasi katalog `0001_01_01_000004_create_services_table.php`. [Catatan Slice 00](Catatan-Pengerjaan-Slice-00.md)

| Urutan | Slice | Perubahan skema yang diusulkan | Dependensi dan keputusan |
| --- | --- | --- | --- |
| Dasar | 00 | Pertahankan migrasi bawaan `users`, `sessions`, cache, dan jobs serta riwayatnya. | Mulai migrasi fitur setelah PR fondasi disetujui dan di-merge. |
| 1 | 01 | `0001_01_01_000003_configure_owner_accounts.php` menambahkan username unik dan atribut penonaktifan, serta menghapus atribut email akun. | Akun aktif diprovisikan dengan `php artisan owner:create`. Rollback mengembalikan format skema awal dengan email placeholder unik bagi baris yang ada. |
| 2 | 02 | Migrasi `0001_01_01_000004_create_services_table.php` membuat `services` dengan harga positif dan indeks `(archived_at, name)`. | Owner mengelola katalog. Layanan diarsipkan lewat `archived_at`. Daftar resmi masih terbuka pada K3. |
| – | 03 | Sesi Kapster memakai `sessions` bawaan Laravel. | Nama tersimpan sebagai teks pada payload. Dashboard memakai sesi tamu aktif dan pratinjau membaca katalog Slice 02. |
| – | 04 | Master pelanggan dilewati. | K1 menetapkan bahwa nama pelanggan hanya keterangan opsional. |
| 3 | 05 | Migrasi `0001_01_01_000005_create_income_entries_table.php` membuat `income_entries` dengan satu FK wajib `service_id` dan `customer_name` opsional. | Bergantung pada `services`. Harga dan nama Kapster disalin saat simpan. `recorded_at` memakai waktu server UTC sesuai implementasi awal K6. |
| 4 | 07 | Buat `expenses`. | Bergantung pada `users`. Sistem mengisi PK dan memakainya sebagai dasar ID yang ditampilkan. |
| Bersyarat | 08 | Tambahkan atribut audit dan relasi pengganti sesuai alur yang disetujui. | K6. Owner memegang izin. Tetapkan cakupan tindakan, periode, dan aturan status sebelum migrasi. |
| Opsional | 11 | Buat `historical_imports`, lalu `historical_monthly_revenues`. | K8, termasuk kebutuhan impor dan pemetaan file aktual. |

Slice 06, 09, dan 10 membaca sumber operasional yang sama. Slice 12 dapat memakai sumber bulanan yang disepakati tanpa migrasi tambahan pada usulan awal. Gunakan migrasi baru setiap kali mengubah fondasi yang sudah diterapkan. Uji penerapan dan rollback pada MySQL 8.4 di skema uji terpisah. Dalam rollback skema fitur, lepaskan tabel anak sebelum induknya. [Migrasi Laravel](https://laravel.com/docs/13.x/migrations)

## 8. Data uji fiktif dan rekonsiliasi

Contoh mengikuti keputusan satu layanan per simpan dan memakai harga katalog. `Layanan A` dan `Layanan B` adalah placeholder. Nominal keduanya hanya untuk rekonsiliasi data uji. Waktu pada tabel adalah WIB. Harga Layanan A naik dari Rp35.000 menjadi Rp40.000 pada awal 1 Oktober. Gunakan target selisih Rp0 untuk pencatatan Kapster, pembukuan, grafik, dan ekspor pada data serta filter yang sama.

| ID uji | Jenis | Tanggal atau waktu lokal | Sesi atau pelaku | Layanan atau deskripsi | Nominal (Rp) |
| --- | --- | --- | --- | --- | ---: |
| I01 | Pemasukan | 29 Sep 2026 10:15:00 | Sesi A, Bayu | Layanan A | 35.000 |
| I02 | Pemasukan | 30 Sep 2026 18:00:00 | Sesi A, Bayu | Layanan B | 15.000 |
| I03 | Pemasukan | 30 Sep 2026 23:59:59.999999 | Sesi B, Bayu | Layanan A | 35.000 |
| I04 | Pemasukan | 1 Okt 2026 00:00:00 | Sesi C, Ari | Layanan A | 40.000 |
| E01 | Pengeluaran | 30 Sep 2026 | Owner | Perlengkapan | 20.000 |
| E02 | Pengeluaran | 1 Okt 2026 | Owner | Kebersihan | 5.000 |

| Periode | Pemasukan (Rp) | Pengeluaran (Rp) | Selisih pemasukan dan pengeluaran (Rp) |
| --- | ---: | ---: | ---: |
| 29 September | 35.000 | 0 | 35.000 |
| 30 September | 50.000 | 20.000 | 30.000 |
| September 2026 | 85.000 | 20.000 | 65.000 |
| 1 Oktober | 40.000 | 5.000 | 35.000 |
| Oktober 2026 | 40.000 | 5.000 | 35.000 |
| Seluruh periode | 125.000 | 25.000 | 100.000 |

- Dari sesi browser A, dashboard Kapster membaca I01 dan I02. Browser B membaca I03 berdasarkan kunci permintaan pada sesi B. Ketiganya menyimpan `barber_name = Bayu`. Owner melihat seluruh baris pada bulan yang sama.
- Uji I01 dengan `customer_name = Andi`, dan I02 dengan nama pelanggan kosong. I01 menyimpan teks nama dan I02 menyimpan `NULL`. Keduanya tetap menghasilkan pemasukan sesuai tabel pembanding.
- Kirim ulang I01 dengan UUID dan payload yang sama. Rekap September tetap memiliki tiga pemasukan. I01 dan I03 masing-masing dihitung Rp35.000 karena keduanya merupakan pencatatan sah yang berbeda.
- Pada uji terpisah, catat Layanan A (Rp35.000) dan Layanan B (Rp15.000) untuk satu kunjungan dalam dua simpan dengan kunci berbeda. Hasil yang diharapkan adalah dua baris pemasukan dengan total Rp50.000. Kirim ulang salah satu simpan dan pastikan jumlah baris serta totalnya tetap sama.
- Cek batas bulan. Tepat saat Oktober dimulai, I03 tetap berada di September dan I04 masuk Oktober. Uji bulan baru tanpa catatan dengan mengharapkan total Rp0 dan tabel kosong. Rekap September tetap menampilkan catatan terdahulu. Setelah harga Layanan A berubah, I01 dan I03 tetap bernilai Rp35.000.
- Cek hasil ekspor. Gunakan ID internal numerik 1 untuk I01 dan E01 saat pengujian. Kedua baris tetap tampil dengan jenis dan ID gabungan yang berbeda. Rekap September dengan filter pemasukan memuat I01–I03 dengan total Rp85.000. Rekap seluruh periode tanpa filter memuat enam entri. Grafik 30 September memuat Rp50.000 pemasukan dan Rp20.000 pengeluaran.

**Pemeriksaan draf.** Kueri Rekap dijalankan pada MySQL 8.4.3 dengan enam baris fiktif dari tabel di atas melalui CTE. Hasilnya cocok dengan pembanding September dan Oktober serta grafik 30 September. November yang kosong menghasilkan Rp0, dan Rekap lintas periode tetap memuat keenam catatan. Nama Kapster yang sama dan ID numerik yang sama pada jenis berbeda tetap muncul pada baris masing-masing. Uji penyimpanan, izin sesi, dan berkas XLSX dijalankan pada slice implementasi terkait.

## 9. Penyesuaian bukti penerimaan QMP

| Kriteria awal | Bukti pada kebutuhan terbaru dan keputusan yang masih diperlukan |
| --- | --- |
| KP-01 · Master operasional | Uji katalog dan pengarsipan sesuai kewenangan. Selaraskan kriteria awal QMP dengan nama Kapster dari sesi dan nama pelanggan opsional pada catatan (K1–K3). |
| KP-02 · Pencatatan dan pembukuan | Peragakan simpan Kapster dan input pengeluaran Owner. Bandingkan Pemasukan, Pengeluaran, Rekap, serta XLSX dengan fixture. Target selisih Rp0. |
| KP-03 · Dashboard | Cocokkan total bulanan dan grafik harian. Kriteria dashboard mingguan pada rancangan awal perlu diselaraskan dengan tampilan terbaru. |
| KP-04 · Autentikasi dan akses | Uji login Owner, akses langsung ke rute terbatas, akun nonaktif, dan cakupan baca dua sesi Kapster bernama sama. Uji izin pembatalan dan koreksi jika tindakan tersebut diterapkan. |
| KP-05 · Keamanan | Uji sesi Laravel, hash kata sandi, validasi, CSRF, dan HTTPS. Jika Forecasting dipakai, uji pemanggilan Python secara internal. |
| KP-06 · Peramalan | Bila disetujui, uji empat aktual dan dua prediksi sesuai sumbu waktu yang disepakati. Catat pemutakhiran bulanan dan status evaluasi galat. Jika fitur tidak disetujui, selaraskan KP-06 dengan Sponsor/Owner karena Charter dan QMP awal memasukkannya dalam lingkup. |
| KP-07 · Penyerahan | Catat versi migrasi, bukti uji aktual, hasil UAT Kapster dan Owner, serta keputusan penerimaan. |

## 10. Urutan pengerjaan per slice

| Slice | Pekerjaan dan dependensi | Kriteria selesai untuk revisi ini |
| --- | --- | --- |
| 00 · Fondasi dan tinjauan | Fondasi Laravel/MySQL telah diuji. Tinjau dampak deskripsi terbaru pada kontrak frontend dan daftar keputusan. | PR fondasi telah disetujui dan masuk `main`. Keputusan yang memengaruhi tiap slice dicatat sebelum migrasi terkait. |
| 01 · Akun Owner | Gunakan `users` dan `sessions` bawaan. Terapkan username, akses Owner, dan logout setelah Slice 00. | Akun yang disetujui dapat login. Sesi Kapster dan pengguna tanpa login ditolak pada rute Owner. Akun nonaktif kehilangan akses. |
| 02 · Katalog layanan | Buat katalog, pilihan layanan aktif, dan perubahan harga/arsip menurut K3. | Layanan dan harga yang sah tersedia bagi form Kapster sesuai kewenangan pengelola yang disepakati. |
| 03 · Sesi Kapster | Mulai dan akhiri sesi tamu dengan nama teks. Lindungi dashboard, pisahkan konteks Owner, dan buka pratinjau Tambah. | Alur dan isolasi sesi lulus uji. Pratinjau menampilkan layanan aktif beserta harga. [Catatan Slice 03](Catatan-Pengerjaan-Slice-03.md) |
| 04 · Master pelanggan | Dilewati sesuai keputusan K1. | Nama pelanggan ditangani sebagai keterangan opsional pada Slice 05. |
| 05 · Pencatatan Kapster | Pencatatan awal satu layanan dikembangkan menjadi transaksi multi-layanan. Form Layanan memuat penjualan produk opsional, dan form Produk khusus penjualan. Owner mengelola katalog serta status layanan. | Sudah di-merge. Uji kedua form, pemulihan arsip, perlindungan riwayat, kirim ulang, dan rekonsiliasi. [Catatan Slice 05](Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge) |
| 06 · Pemasukan Owner | Membaca rincian layanan dan penjualan produk pada bulan berjalan WIB. Menyediakan pencarian dan XLSX. | Sudah di-merge. Setiap item tampil sekali dengan ID induk. Layar, total, dan ekspor memakai filter yang sama. [Catatan Slice 06](Catatan-Pengerjaan-Slice-06.md) |
| 07 · Pengeluaran Owner | Setelah Slice 01, sediakan input tanggal, deskripsi, dan jumlah. Tampilkan ID yang dibuat sistem dan ekspor periode berjalan. | Owner bisa menyimpan dan mengekspor. Kirim ulang menghasilkan satu catatan dengan ID yang sama. |
| 08 · Tindakan Owner bersyarat | Tetapkan apakah cakupan mencakup koreksi, pembatalan tanpa pengganti, atau keduanya sesuai K6. | Jika diterapkan, izin terbatas pada Owner. Histori dan perhitungan rekapan dapat direkonsiliasi. |
| 09 · Rekap lintas periode | Gabungkan hasil Slice 06–07 dan aturan Slice 08 bila berlaku. Sediakan pencarian, filter bulan/jenis, dan XLSX. | Rekap memuat histori lintas bulan dan hasil Excel cocok dengan filter. Selisih terhadap data pembanding Rp0. |
| 10 · Home bulanan | Gunakan sumber Slice 09 untuk total dan dua seri harian bulan berjalan. | Pergantian bulan menghitung rentang baru. Bulan tanpa catatan bernilai Rp0 dan histori tetap tersedia di Rekap. |
| 11 · Histori opsional | Jalankan setelah keputusan K8 dan pemetaan sumber. Verifikasi file privat, cakupan, dan impor ulang. | Setiap bulan direkonsiliasi dan tetap memiliki satu nilai sumber setelah impor ulang. |
| 12 · Forecasting opsional | Setelah K8, gunakan deret bulanan tervalidasi dan jalankan satu model internal Python. | Empat bulan aktual dan dua prediksi berlabel jelas, diperbarui bulanan sesuai sumbu waktu yang disepakati. |
| 13 · Uji integrasi dan UAT | Uji migrasi MySQL, alur Kapster, akses Owner, ekspor dan rekonsiliasi. Sertakan peramalan jika disetujui. | Hasil aktual, status KP, temuan, dan uji ulang terdokumentasi. |
| 14 · Penerapan dan penyerahan | Setelah pemeriksaan fungsi inti, uji lingkungan target dan catat keputusan penerimaan. | Dokumentasi skema, hasil uji, serta keputusan penerimaan tersedia. |

Lihat peta kesiapan untuk menentukan halaman yang sudah dapat memakai alur backend dari `main`. [Peta kesiapan per tahap](Rencana-Backend-Sistem-Operasional-Barbershop.md#kesiapan-frontend-per-tahap)

## 11. Usulan revisi Slice 05 — transaksi multi-layanan dan pilihan produk

Implementasi pada branch revisi mencakup tiga migrasi Laravel (`000006`–`000008`), katalog produk Owner, dua form Kapster, dan satu kueri sumber pemasukan per item. [Catatan dan hasil uji revisi](Catatan-Pengerjaan-Slice-05.md#revisi-kebutuhan-setelah-merge)

### Alur yang diminta

Dashboard Kapster menyediakan dua tombol. Nama Kapster diambil dari sesi dan nama pelanggan tetap opsional di kedua form.

| Tombol | Isi form | Aturan simpan |
| --- | --- | --- |
| **Layanan** | Satu atau beberapa layanan pada bagian **Layanan**. Produk yang terjual diisi pada bagian **Produk dijual (opsional)**. | Wajib minimal satu item layanan. |
| **Produk** | Daftar produk yang dibeli pelanggan. Kapster memilih produk dan memasukkan jumlah. | Wajib minimal satu item penjualan produk. |


- Owner menambahkan pilihan produk ke katalog. Kapster memakai katalog yang sama di kedua form. Harga satuan produk terjual diambil dari katalog.
- Pemasukan Owner pada Slice 06 menampilkan satu baris per item layanan dan penjualan. Baris dari transaksi yang sama memakai ID transaksi yang sama. Keterangan pemakaian produk dari versi sebelumnya dapat ditampilkan pada riwayat.

### Acuan rekap manual

Dua foto yang diberikan pengguna memperlihatkan laporan pemasukan barbershop dan perhitungan komisi Kapster. Pemasukan perlu mencakup penjualan produk. Komisi bulanan berada di luar lingkup aplikasi sesuai konfirmasi pengguna.

| Acuan | Perhitungan yang dapat dicocokkan | Dampak pada desain |
| --- | --- | --- |
| Gambar 1 · rekap harian | 18 × Rp18.000 + Keramas Rp5.000 + Hair Tonic Rp15.000 = Rp344.000. | Penjualan produk memiliki nilai rupiah sendiri. Tulisan QRIS dan Rp230.000 perlu penjelasan jika dipakai untuk rekonsiliasi. |
| Gambar 2 · komisi jasa bulanan | 300 × Rp18.000 + tambahan Rp25.000 + 11 × Rp5.000 = Rp5.480.000. Komisi contoh 45% × Rp5.480.000 = Rp2.466.000. | Perhitungan komisi tetap menjadi acuan proses manual. |
| Gambar 2 · komisi produk dan total Kapster | Bagian Penjualan produk menunjukkan komisi Rp9.000 + Rp3.000 + Rp8.000 + Rp2.000 = Rp22.000. Komisi jasa, komisi produk, serta dua tambahan Rp91.000 dan Rp910.000 berjumlah Rp3.489.000. Kolom Gaji yang diterima tertulis Rp3.500.000. | Angka komisi produk di kertas adalah bagian penghasilan Kapster. Pemasukan dari penjualan di aplikasi dihitung dari jumlah dan harga jual. |

### Skema revisi Slice 05

```mermaid
erDiagram
    INCOME_TRANSACTIONS ||--o{ INCOME_ENTRIES : memuat_layanan
    INCOME_TRANSACTIONS ||--o{ INCOME_PRODUCT_SALES : memuat_penjualan
    SERVICES ||..o{ INCOME_ENTRIES : dipilih
    PRODUCTS |o..o{ INCOME_ENTRIES : pemakaian_lama
    PRODUCTS ||..o{ INCOME_PRODUCT_SALES : terjual
```

| Tabel | Perubahan yang diusulkan |
| --- | --- |
| `income_transactions` | PK `id`. Simpan nama Kapster dari sesi, nama pelanggan opsional, waktu pencatatan UTC, cakupan sesi, kunci permintaan unik, dan hash payload transaksi. Indeks `(recorded_at, id)` untuk laporan dan `(session_scope, recorded_at, id)` untuk dashboard sesi. |
| `income_entries` | Pertahankan ID, snapshot layanan dan harga, serta FK wajib `income_transaction_id`. FK opsional `used_product_id` dan snapshot `used_product_name VARCHAR(120) NULL` dipertahankan untuk riwayat pemakaian produk dari versi sebelumnya. |
| `products` | PK `id`, `name VARCHAR(120)`, `sale_price DECIMAL(15,0) NULL`, dan `archived_at DATETIME(6) NULL`. Owner mengisi katalog. Form penjualan Kapster hanya menampilkan produk aktif dengan harga jual positif. |
| `income_product_sales` | PK `id`. FK wajib `income_transaction_id` ke induk dan `product_id` ke katalog. Simpan `product_name VARCHAR(120)`, `quantity INT UNSIGNED` positif, dan snapshot `unit_price DECIMAL(15,0)` positif. Nilai penjualan per item dihitung dari `quantity × unit_price`. |

Setiap transaksi memiliki minimal satu item yang menghasilkan pemasukan. Form **Layanan** mensyaratkan layanan, sementara form **Produk** mensyaratkan penjualan produk dan menolak item layanan. Laravel menerapkan validasi sesuai rute sebelum menyimpan induk dan item. Produk yang sudah direferensikan dapat diarsipkan. FK memakai `RESTRICT` agar catatan lama tetap bisa dibaca.

### Sumber pemasukan Owner

`IncomeItems::query()` menormalisasi layanan dan penjualan produk dari transaksi induk dengan `UNION ALL`. Identitas baris memakai pasangan jenis dan ID item. Layanan menyumbang snapshot harga. Penjualan produk menyumbang jumlah kali snapshot harga satuan. Pemakaian produk lama tetap tampil sebagai keterangan. Slice 06 menerapkan cakupan bulan berjalan dan pencarian melalui `OwnerIncomeReport` bagi daftar, total, serta XLSX.

### Penyimpanan, migrasi, dan pengujian

1. Migrasi `000006_create_products_table` menambah katalog produk. Migrasi `000007_group_income_entries_into_transactions` membuat induk dan mengaitkan satu transaksi ke setiap catatan lama. ID item, waktu, dan nominal lama dipertahankan.
2. Setelah backfill, migrasi `000007` mewajibkan FK induk, menambah FK pemakaian produk opsional, dan memindahkan kunci kirim ulang ke induk. Migrasi `000008_create_income_product_sales_table` menambah rincian penjualan produk.
3. Terapkan validasi per form, termasuk pilihan produk, jumlah, dan harga katalog. Kedua form memakai alur simpan yang sama untuk menyimpan induk beserta seluruh rincian dalam satu transaksi database. Kirim ulang dengan kunci dan payload yang sama mengembalikan transaksi yang telah tersimpan.
4. Uji form **Layanan** dengan Layanan A Rp35.000, Layanan B Rp15.000, dan penjualan dua unit produk seharga Rp15.000 per unit. Pemasukan Owner memuat tiga baris dengan ID transaksi sama dan total Rp80.000. Uji juga form Layanan dengan penjualan kosong dan dengan layanan kosong.
5. Uji form **Produk** dengan penjualan dua unit produk seharga Rp15.000 per unit. Pemasukan Owner memuat satu baris penjualan senilai Rp30.000. Uji penolakan daftar produk kosong dan permintaan yang menyisipkan item layanan.
6. Uji kirim ulang pada kedua form, penolakan item tidak valid, dan selisih Rp0 sebelum serta sesudah backfill data lama. Pada kasus gagal, jumlah transaksi, jumlah item, dan total pemasukan harus sama dengan keadaan sebelum pengiriman.
7. Uji bahwa layanan yang diarsipkan hilang dari pilihan Kapster, lalu muncul kembali setelah diaktifkan. Uji penghapusan layanan yang belum tercatat serta perlindungan layanan yang memiliki riwayat.
