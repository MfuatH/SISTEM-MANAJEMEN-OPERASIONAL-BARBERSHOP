# Desain Database MySQL Sistem Manajemen Operasional Barbershop

**Kelompok 6, Kelas 2023C · Revisi kebutuhan · Draf untuk peninjauan**

Acuan kebutuhan fungsional adalah *Deskripsi Sistem (Terurut)* dari `deskripsi-sistem (2).md`, bagian 1–7, beserta konfirmasi kebutuhan lanjutan yang dicatat pada bagian 2. Sistem melayani satu lokasi. Kapster memulai sesi dengan nama dari beranda. Owner masuk menggunakan username dan kata sandi. Stack yang disepakati adalah Laravel 13.x, PHP 8.5.x, dan MySQL 8.4 LTS dengan Laragon. Perubahan terhadap lingkup dan kriteria penerimaan awal dicatat pada bagian 9. [Catatan fondasi Slice 00](Catatan-Pengerjaan-Slice-00.md) · [Acuan tugas kuliah](../tugas-kuliah/README.md)

## 1. ERD usulan

```mermaid
erDiagram
    USERS ||..o{ EXPENSES : mencatat
    USERS |o..o{ SESSIONS : direferensikan
    SERVICES ||..o{ INCOME_ENTRIES : dipilih
```

### Tabel `users`

**Isi dan fungsi.** Akun yang dapat diprovisikan untuk login Owner. Owner dan Admin adalah sebutan untuk orang yang sama. Implementasi memakai satu akun Owner dengan username dan kata sandi. Slice 01 mengubah tabel bawaan melalui migrasi baru.

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
| Pencatatan dan pemasukan | Checkout POS menghasilkan struk dan satu pemasukan per transaksi sah. | Form Kapster menyimpan satu pemasukan per pencatatan. Pembuatan struk dihapus dari Slice 05 dan 06. |
| Data layanan, kapster, pelanggan | Tiga master terpusat, transaksi menunjuk pelanggan dan kapster. | Nama Kapster berupa teks. Memilih layanan langsung menampilkan harga katalog. Arti nama pada form Tambah dan kebutuhan master pelanggan masih perlu dipastikan. |
| Pengeluaran | Kasir mencatat tanggal, deskripsi, dan nominal. | Owner memasukkan tanggal, deskripsi, dan jumlah. Sistem menerbitkan ID pengeluaran. Riwayat mendukung pencarian dan ekspor Excel. |
| Laporan | Dashboard harian, mingguan, dan bulanan dengan rekap periode. | Home, Pemasukan, dan Pengeluaran menampilkan bulan berjalan. Rekap menyimpan cakupan seluruh periode dan mendukung filter serta ekspor. |
| Peramalan | Satu Regresi Linier Python dengan proyeksi bulanan. | Fitur opsional. Empat bulan aktual dan dua bulan prediksi, diperbarui setiap bulan bila disetujui. |

### Keputusan dan dampak skema

| ID | Status dan keputusan | Dampak pada desain |
| --- | --- | --- |
| K1 | **Terbuka.** Siapa yang dimaksud dengan nama pada form Tambah? | Usulan inti memakai nama Kapster dari sesi untuk label pencatatan. Jika form juga memerlukan nama pelanggan bebas, tambahkan `customer_name VARCHAR(120)`. Jika pelanggan dipilih dari master, tentukan `customers` dan FK `customer_id` sebelum Slice 04–05. |
| K2 | **Dikonfirmasi.** Nama Kapster adalah teks, dengan nama sama diperbolehkan. **Usulan teknis.** Dashboard Kapster hanya menampilkan pencatatan yang dibuat pada sesi browser aktif. | Simpan `barber_name` pada setiap pemasukan. Gunakan sesi Laravel bawaan dan kunci permintaan per pencatatan untuk membatasi cakupan baca. Konfirmasi jika Kapster perlu melihat riwayat dari sesi sebelumnya. |
| K3 | **Dikonfirmasi.** Harga muncul otomatis setelah layanan dipilih. **Terbuka.** Apa daftar layanan resmi, siapa yang mengelola katalog, dan apakah satu simpan memuat lebih dari satu layanan? | Harga tampil sebagai nilai baca-saja. Saat simpan, server mengambil harga katalog dan menyimpan snapshot. Baseline mengusulkan Owner mengelola katalog dan satu layanan per pencatatan. |
| K4 | **Dikonfirmasi.** Sistem membuat ID pengeluaran. | Gunakan `expenses.id` yang dibuat oleh database. Usulan format tampilan dan ekspor adalah `EXP-{id}`. |
| K5 | **Dikonfirmasi.** Home, Pemasukan, dan Pengeluaran memakai bulan berjalan. Rekap mencakup seluruh histori. | Pada pergantian bulan, halaman bulanan memakai rentang baru. Rekap tetap dapat membaca bulan sebelumnya dan menyediakan filter bulan atau jenis. |
| K6 | **Dikonfirmasi.** Pencatatan memakai layanan dan nominal, dengan aksi Kapster sebatas menambah. **Tafsiran draf.** Koreksi dan pembatalan yang diaktifkan berada di area Owner. | Kolom metode pembayaran dikeluarkan dari skema. Sebelum Slice 08, pastikan apakah Owner membutuhkan pembatalan tanpa pengganti atau cukup koreksi, cakupan pemasukan/pengeluaran, perlakuan periode, dan histori audit. Master pelanggan dan pencatatan mundur pemasukan masih terbuka. |
| K7 | **Dikonfirmasi.** Owner dan Admin adalah orang yang sama. Login memakai username dan kata sandi. | Satu jenis akun Owner. Slice 01 menambahkan username dan menghapus atribut email akun. |
| K8 | Apakah Forecasting disetujui, posisi prediksi di mana, dan apa yang diproyeksikan? Apakah data Juli–September 2026 masih diperlukan? | Usulan targetnya pemasukan bulanan, memakai empat bulan aktual terakhir yang sudah lengkap pada grafik dan dua bulan prediksi sesudahnya. Frasa “6 bulan kebelakang” juga dapat berarti dua prediksi atas bulan historis. Konfirmasi arti tersebut, sumber data, dan ketentuan baru sebelum Slice 11–12. |

## 3. Kamus tabel lainnya

Pada tabel usulan, `ID` berarti `BIGINT UNSIGNED`, dengan `AUTO_INCREMENT` untuk PK tunggal `id`. `Rp` berarti `DECIMAL(15,0)` untuk rupiah utuh. `UTC` berarti `DATETIME(6)` berisi waktu UTC. Kolom wajib diisi kecuali dinyatakan `NULL`. PK adalah kunci primer, FK kunci asing, dan UQ kunci unik. Gunakan InnoDB, `utf8mb4`, dan hitung nominal dengan aritmetika presisi eksak. [MySQL DECIMAL](https://dev.mysql.com/doc/refman/8.4/en/fixed-point-types.html)

### Tabel `services` · usulan, bergantung pada K3

**Isi dan fungsi.** Katalog pilihan layanan beserta harga aktif. Satu layanan dapat dirujuk oleh banyak pemasukan.

| Kolom | Tipe | Kunci dan kegunaan |
| --- | --- | --- |
| `id` | `ID` | PK. Dirujuk oleh `income_entries.service_id`. |
| `name` | `VARCHAR(120)` | Label layanan pada dropdown. Gunakan nama placeholder untuk contoh dan pengujian selama daftar resmi masih terbuka. |
| `current_price` | `Rp` | Harga katalog saat ini, positif. |
| `archived_at` | `UTC NULL` | Saat layanan dikeluarkan dari pilihan aktif. |
| `created_at`, `updated_at` | `UTC` | Audit perubahan katalog. |

### Tabel `income_entries` · usulan, bergantung pada K1, K3, dan K6

**Isi dan fungsi.** Satu baris per pencatatan Kapster yang berhasil disimpan. Menjadi sumber pemasukan Owner, rekap, dan grafik. Setiap baris memuat teks nama Kapster dan menunjuk tepat satu layanan pada desain inti.

| Kolom | Tipe | Kunci dan kegunaan |
| --- | --- | --- |
| `id` | `ID` | PK internal. |
| `barber_name` | `VARCHAR(120)` | Salinan nama Kapster dari sesi Laravel. Nama yang sama dapat muncul pada banyak catatan. |
| `service_id` | `ID` | FK ke `services.id`. |
| `service_name` | `VARCHAR(120)` | Salinan nama layanan saat simpan. |
| `amount` | `Rp` | Harga yang dicatat saat simpan. Tetap bernilai sama saat harga katalog berubah. |
| `recorded_at` | `UTC` | Waktu pencatatan dari server dan dasar periode pemasukan pada desain inti. |
| `request_key`, `request_hash` | `CHAR(36)` ASCII kolasi biner, `BINARY(32)` | UQ `request_key`. UUID per permintaan simpan dan SHA-256 payload. Kunci ini mengidentifikasi percobaan pencatatan. |

Keputusan K1 menentukan apakah form perlu menyimpan nama pelanggan sebagai atribut tambahan.

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
| `sessions` | `id VARCHAR PK`, `user_id BIGINT UNSIGNED NULL` berindeks, `ip_address VARCHAR(45) NULL`, `user_agent TEXT NULL`, `payload LONGTEXT`, `last_activity INT` berindeks | Penyimpanan sesi HTTP. Payload sesi tamu menyimpan nama Kapster dan kunci permintaan yang diterbitkan untuk form pada sesi tersebut. |
| `password_reset_tokens` | `email VARCHAR PK`, `token VARCHAR`, `created_at TIMESTAMP NULL` | Penyimpanan token reset kata sandi bila alur reset diaktifkan. |
| `cache` | `key VARCHAR PK`, `value MEDIUMTEXT`, `expiration BIGINT` berindeks | Nilai dan masa berlaku cache database. |
| `cache_locks` | `key VARCHAR PK`, `owner VARCHAR`, `expiration BIGINT` berindeks | Kunci proses yang dipakai cache Laravel. |
| `jobs` | `id ID PK`, `queue VARCHAR` berindeks, `payload LONGTEXT`, `attempts SMALLINT UNSIGNED`, `reserved_at INT UNSIGNED NULL`, `available_at INT UNSIGNED`, `created_at INT UNSIGNED` | Antrean pekerjaan Laravel. |
| `job_batches` | `id VARCHAR PK`, `name VARCHAR`, `total_jobs INT`, `pending_jobs INT`, `failed_jobs INT`, `failed_job_ids LONGTEXT`, `options MEDIUMTEXT NULL`, `cancelled_at INT NULL`, `created_at INT`, `finished_at INT NULL` | Status kelompok pekerjaan antrean. |
| `failed_jobs` | `id ID PK`, `uuid VARCHAR UQ`, `connection VARCHAR`, `queue VARCHAR`, `payload LONGTEXT`, `exception LONGTEXT`, `failed_at TIMESTAMP` dengan nilai awal waktu saat ini. Indeks `(connection, queue, failed_at)`. | Catatan pekerjaan yang gagal. Tipe kolom dan indeks mengikuti hasil inspeksi MySQL pengujian. |
| `migrations` | `id INT UNSIGNED AUTO_INCREMENT PK`, `migration VARCHAR`, `batch INT` | Riwayat file dan batch migrasi yang telah dijalankan. |

## 4. Integritas dan alur penyimpanan

- **Akun Owner.** Gunakan username unik dan kata sandi yang di-hash Laravel. Login dan rute Owner memeriksa bahwa akun telah diprovisikan sebagai Owner sesuai K7 dan masih aktif. Regenerasi ID sesi saat login dan invalidasi saat logout. Saat menonaktifkan akun, cabut sesi aktifnya. Tambahkan rate limit login, validasi server, proteksi CSRF, dan HTTPS pada lingkungan target. [Autentikasi Laravel](https://laravel.com/docs/13.x/authentication)
- **Sesi Kapster.** Validasi nama dari beranda dan simpan sebagai teks pada sesi tamu Laravel. Untuk cakupan baca yang diusulkan, dashboard mengambil pencatatan berdasarkan kunci permintaan yang sudah diterbitkan dan tercatat pada sesi browser aktif. Nama berfungsi sebagai label pada catatan. Dua browser yang memasukkan nama sama tetap mendapat daftar aktivitas masing-masing. Saat berpindah mode, akhiri konteks lama dan regenerasi sesi. Owner membaca semua catatan lewat rute yang memeriksa autentikasi dan izin. [Sesi Laravel](https://laravel.com/docs/13.x/session)
- **Pemasukan.** Dropdown menampilkan teks `Pilih layanan` sebelum Kapster memilih. Setelah pilihan ditetapkan, form langsung menampilkan harga katalog dalam rupiah sebagai nilai baca-saja. Server menyalin nama Kapster dari sesi dan memvalidasi layanan aktif. Di dalam transaksi, baca harga katalog dari server dan simpan nama layanan serta nominal sebagai snapshot. Bandingkan harga yang terakhir ditampilkan dengan harga server sebelum menyimpan. Jika berbeda, tampilkan harga terbaru dan minta konfirmasi ulang. Satu simpan berhasil menghasilkan satu baris pemasukan. [Transaksi database Laravel](https://laravel.com/docs/13.x/database#database-transactions)
- **Pengeluaran.** Owner mengisi tanggal, deskripsi, dan jumlah. Server memvalidasi tanggal lokal serta nominal dan mengambil pencatat dari sesi login. Setelah simpan, tampilkan ID yang dibuat oleh database sesuai format tampilan.
- **Kirim ulang.** Server terlebih dahulu mendaftarkan `request_key` untuk satu form pencatatan pada sesi Kapster. Saat menyimpan atau mengulang, periksa bahwa kunci berasal dari sesi aktif. Kunci dan hash yang sama mengembalikan pencatatan yang sama, termasuk setelah simpan berhasil tetapi respons terputus. Tolak kunci yang sudah tersimpan jika hash berbeda. Gunakan kunci baru setelah Kapster mengonfirmasi perubahan pada form yang masih belum tersimpan. Pada konflik UQ, baca catatan yang menang dan cocokkan hash. Untuk pengeluaran, terapkan aturan serupa dalam cakupan akun Owner.
- **Data lama.** Semua FK domain memakai `ON DELETE RESTRICT` dan `ON UPDATE RESTRICT`. Katalog yang sudah dipakai diarsipkan dan akun dinonaktifkan. Nama Kapster dan snapshot layanan tetap berada pada catatan setelah sesi HTTP berakhir. Catatan keuangan dipertahankan untuk Rekap lintas periode.
- **Koreksi.** Tindakan koreksi dan pembatalan yang disetujui melewati pemeriksaan izin Owner. Usulan Slice 08 mencatat pelaku, waktu, alasan, serta tautan antara catatan asli dan pengganti. Saat koreksi, simpan audit dan pengganti secara atomik. Isi dan status histori mengikuti hasil keputusan K6.
- **Constraint.** Terapkan `CHECK` untuk harga dan jumlah positif. Validasi keterisian nama dan deskripsi, format tanggal, rentang nominal, serta status aktif di Laravel. [MySQL CHECK](https://dev.mysql.com/doc/refman/8.4/en/create-table-check-constraints.html)

| Tabel | Indeks utama selain PK | Pemakaian |
| --- | --- | --- |
| `users` | UQ `email` yang sudah ada, UQ `username` usulan | Identitas akun dan login. |
| `services` | `(archived_at, name)` | Daftar layanan aktif. |
| `income_entries` | `(recorded_at, id)`, indeks `service_id`, UQ `request_key` | Rekap periode, FK layanan, dan pencarian catatan berdasarkan kunci permintaan. |
| `expenses` | `(incurred_on, id)`, UQ `(recorded_by_user_id, request_key)` | Rekap periode, FK pencatat, dan simpan ulang. Pencarian ID yang tepat memakai PK `id`. |

## 5. Sumber laporan, grafik, dan ekspor

| Halaman Owner | Sumber dan cakupan | Pencarian, filter, dan ekspor |
| --- | --- | --- |
| Home | Dua total dan dua seri harian dari awal bulan berjalan. | Hari yang sudah berjalan tanpa transaksi ditampilkan Rp0. |
| Pemasukan | `income_entries` bulan berjalan, dengan nama Kapster dan snapshot layanan. | Cari nama atau layanan. Ekspor Excel mengikuti pencarian dalam bulan berjalan. |
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

Di implementasi, Laravel menerapkan satu pembentuk rentang dan sumber kueri laporan. Daftar dan XLSX memakai kueri yang sama dengan cakupan per halaman. Home memakai sumber keuangan yang sama tanpa kata kunci pencarian halaman lain. Grafik mengelompokkan per `business_date` dan `entry_type`; total bulanan menjumlahkan seluruh baris untuk jenis yang sama. Bila layar menampilkan saldo, rumusnya pemasukan dikurangi pengeluaran. XLSX menyimpan nominal sebagai angka dan label masukan pengguna sebagai teks. Selisih antara layar, ekspor, dan pembanding dinilai pada data dan filter yang sama. Rekap, total bulanan, dan ekspor dihitung dari kueri tersebut.

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

Pemeriksaan repo menemukan tiga migrasi bawaan `0001_01_01_000000_create_users_table.php`, `0001_01_01_000001_create_cache_table.php`, dan `0001_01_01_000002_create_jobs_table.php`. `migrate:status --env=testing` menunjukkan ketiganya `Ran`. Ketiga migrasi yang terdaftar meliputi fondasi Laravel. Seluruh migrasi operasional berada pada daftar usulan di bawah. Fondasi Slice 00 tercatat diuji dan sudah masuk ke `main`. [Catatan Slice 00](Catatan-Pengerjaan-Slice-00.md)

| Urutan | Slice | Perubahan skema yang diusulkan | Dependensi dan keputusan |
| --- | --- | --- | --- |
| Dasar | 00 | Pertahankan migrasi bawaan `users`, `sessions`, cache, dan jobs serta riwayatnya. | Mulai migrasi fitur setelah PR fondasi disetujui dan di-merge. |
| 1 | 01 | `0001_01_01_000003_configure_owner_accounts.php` menambahkan username unik dan atribut penonaktifan, serta menghapus atribut email akun. | Akun aktif diprovisikan dengan `php artisan owner:create`. Rollback mengembalikan format skema awal dengan email placeholder unik bagi baris yang ada. |
| 2 | 02 | Buat `services`. | K3 mencakup pengelola, jumlah pilihan per simpan, dan aturan arsip. Harga pada form mengikuti katalog. |
| – | 03 | Sesi Kapster memakai `sessions` bawaan Laravel. | Simpan nama sebagai teks pada payload dan batasi daftar menurut cakupan sesi yang disepakati. |
| 3, bersyarat | 04 | Buat `customers` jika dibutuhkan. | K1 menentukan kebutuhan nama pelanggan bebas atau referensi master. |
| 4 | 05 | Buat `income_entries`. | Bergantung pada `services` dan keputusan tentang nama pada form, kardinalitas layanan, serta tanggal pencatatan. Harga dan nama Kapster disalin saat simpan. |
| 5 | 07 | Buat `expenses`. | Bergantung pada `users`. Sistem mengisi PK dan memakainya sebagai dasar ID yang ditampilkan. |
| Bersyarat | 08 | Tambahkan atribut audit dan relasi pengganti sesuai alur yang disetujui. | K6. Owner memegang izin. Tetapkan cakupan tindakan, periode, dan aturan status sebelum migrasi. |
| Opsional | 11 | Buat `historical_imports`, lalu `historical_monthly_revenues`. | K8, termasuk kebutuhan impor dan pemetaan file aktual. |

Slice 06, 09, dan 10 membaca sumber operasional yang sama. Slice 12 dapat memakai sumber bulanan yang disepakati tanpa migrasi tambahan pada usulan awal. Gunakan migrasi baru setiap kali mengubah fondasi yang sudah diterapkan. Uji penerapan dan rollback pada MySQL 8.4 di skema uji terpisah. Dalam rollback skema fitur, lepaskan tabel anak sebelum induknya. [Migrasi Laravel](https://laravel.com/docs/13.x/migrations)

## 8. Data uji fiktif dan rekonsiliasi

Contoh mengikuti usulan satu layanan per simpan dan harga dari katalog. `Layanan A` dan `Layanan B` adalah placeholder. Nominal keduanya hanya untuk rekonsiliasi data uji. Waktu pada tabel adalah WIB. Harga Layanan A naik dari Rp35.000 menjadi Rp40.000 pada awal 1 Oktober. Gunakan target selisih Rp0 untuk pencatatan Kapster, pembukuan, grafik, dan ekspor pada data serta filter yang sama.

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
- Kirim ulang I01 dengan UUID dan payload yang sama. Rekap September tetap memiliki tiga pemasukan. I01 dan I03 masing-masing dihitung Rp35.000 karena keduanya merupakan pencatatan sah yang berbeda.
- Cek batas bulan. Tepat saat Oktober dimulai, I03 tetap berada di September dan I04 masuk Oktober. Uji bulan baru tanpa catatan dengan mengharapkan total Rp0 dan tabel kosong. Rekap September tetap menampilkan catatan terdahulu. Setelah harga Layanan A berubah, I01 dan I03 tetap bernilai Rp35.000.
- Cek hasil ekspor. Gunakan ID internal numerik 1 untuk I01 dan E01 saat pengujian. Kedua baris tetap tampil dengan jenis dan ID gabungan yang berbeda. Rekap September dengan filter pemasukan memuat I01–I03 dengan total Rp85.000. Rekap seluruh periode tanpa filter memuat enam entri. Grafik 30 September memuat Rp50.000 pemasukan dan Rp20.000 pengeluaran.

**Pemeriksaan draf.** Kueri Rekap dijalankan pada MySQL 8.4.3 dengan enam baris fiktif dari tabel di atas melalui CTE. Hasilnya cocok dengan pembanding September dan Oktober serta grafik 30 September. November yang kosong menghasilkan Rp0, dan Rekap lintas periode tetap memuat keenam catatan. Nama Kapster yang sama dan ID numerik yang sama pada jenis berbeda tetap muncul pada baris masing-masing. Uji penyimpanan, izin sesi, dan berkas XLSX dijalankan pada slice implementasi terkait.

## 9. Penyesuaian bukti penerimaan QMP

| Kriteria awal | Bukti pada kebutuhan terbaru dan keputusan yang masih diperlukan |
| --- | --- |
| KP-01 · Master operasional | Uji katalog dan pengarsipan sesuai kewenangan. Selaraskan cakupan master Kapster dan pelanggan dari QMP dengan keputusan K1–K3. |
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
| 03 · Sesi Kapster | Mulai sesi dari beranda menggunakan nama sebagai teks. Pertahankan cakupan baca sesuai K2. | Kapster melihat aktivitas sesi browser yang sedang berjalan dan dapat membuka form Tambah. |
| 04 · Master pelanggan bersyarat | Putuskan K1 sebelum menetapkan atribut nama pelanggan atau relasi master. | Skema dan form mencerminkan jawaban tim. |
| 05 · Pencatatan Kapster | Gunakan pilihan katalog dan tampilkan harga otomatis. Simpan nama Kapster, snapshot layanan dan harga, serta kunci permintaan. | Pencatatan tampil di dashboard sesi dan menghasilkan satu pemasukan walau form dikirim ulang. |
| 06 · Pemasukan Owner | Baca Slice 05 untuk bulan berjalan dan sediakan pencarian serta XLSX. | Setiap catatan sah tampil sekali. Pada bulan baru, daftar dimulai dari rentang baru. Layar dan ekspor cocok untuk pencarian yang sama. |
| 07 · Pengeluaran Owner | Setelah Slice 01, sediakan input tanggal, deskripsi, dan jumlah. Tampilkan ID yang dibuat sistem dan ekspor periode berjalan. | Owner bisa menyimpan dan mengekspor. Kirim ulang menghasilkan satu catatan dengan ID yang sama. |
| 08 · Tindakan Owner bersyarat | Tetapkan apakah cakupan mencakup koreksi, pembatalan tanpa pengganti, atau keduanya sesuai K6. | Jika diterapkan, izin terbatas pada Owner. Histori dan perhitungan rekapan dapat direkonsiliasi. |
| 09 · Rekap lintas periode | Gabungkan hasil Slice 06–07 dan aturan Slice 08 bila berlaku. Sediakan pencarian, filter bulan/jenis, dan XLSX. | Rekap memuat histori lintas bulan dan hasil Excel cocok dengan filter. Selisih terhadap data pembanding Rp0. |
| 10 · Home bulanan | Gunakan sumber Slice 09 untuk total dan dua seri harian bulan berjalan. | Pergantian bulan menghitung rentang baru. Bulan tanpa catatan bernilai Rp0 dan histori tetap tersedia di Rekap. |
| 11 · Histori opsional | Jalankan setelah keputusan K8 dan pemetaan sumber. Verifikasi file privat, cakupan, dan impor ulang. | Setiap bulan direkonsiliasi dan tetap memiliki satu nilai sumber setelah impor ulang. |
| 12 · Forecasting opsional | Setelah K8, gunakan deret bulanan tervalidasi dan jalankan satu model internal Python. | Empat bulan aktual dan dua prediksi berlabel jelas, diperbarui bulanan sesuai sumbu waktu yang disepakati. |
| 13 · Uji integrasi dan UAT | Uji migrasi MySQL, alur Kapster, akses Owner, ekspor dan rekonsiliasi. Sertakan peramalan jika disetujui. | Hasil aktual, status KP, temuan, dan uji ulang terdokumentasi. |
| 14 · Penerapan dan penyerahan | Setelah pemeriksaan fungsi inti, uji lingkungan target dan catat keputusan penerimaan. | Dokumentasi skema, hasil uji, serta keputusan penerimaan tersedia. |
