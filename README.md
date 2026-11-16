# Sistem Manajemen Operasional Barbershop

Proyek pengembangan aplikasi web untuk operasional satu lokasi barbershop oleh Kelompok 6, Kelas 2023C. Lokasi pengujian yang ditetapkan dalam Quality Management Plan adalah Pangkas Rambut Upin Ipin 11.

## Latar belakang

Pencatatan transaksi, pemasukan, dan pengeluaran secara manual menyulitkan pengecekan selisih kas serta penyusunan laporan. Karyawan juga merangkap sebagai kasir, sementara pemilik membutuhkan rekap pendapatan yang bisa diperiksa tanpa menunggu pencatatan ulang.

Aplikasi menyatukan pencatatan transaksi dan pengeluaran dengan pembukuan, dashboard pendapatan, dan proyeksi omzet bulanan. Pembagian akses mengikuti dua peran operasional.

| Peran | Alur utama |
| --- | --- |
| Karyawan/Kasir | Mengelola data layanan dan harga, kapster, serta pelanggan. Mencatat penjualan di POS, pembayaran tunai atau nontunai, dan pengeluaran. |
| Owner/Pemilik | Memeriksa pembukuan, dashboard pendapatan harian, mingguan, dan bulanan, serta proyeksi omzet bulan berikutnya. |

Pembayaran dicatat manual pada POS. Ruang lingkup pengembangan berfokus pada operasional satu lokasi. Booking daring, payment gateway otomatis, dan pengelolaan multi-cabang berada di luar cakupan.

## Stack teknis

Stack berikut mengikuti Project Charter dan draf desain database. Versi dependensi akan dicatat saat kerangka aplikasi dan berkas dependensi tersedia di repositori.

| Bagian | Teknologi | Penggunaan |
| --- | --- | --- |
| Backend web | PHP, Laravel | Rute web, autentikasi berbasis sesi, otorisasi Kasir/Owner, validasi di server, dan logika operasional. |
| Frontend | JavaScript dan tampilan web Laravel | Form master dan POS, pembukuan, serta dashboard Owner. |
| Database | MySQL | Penyimpanan data operasional melalui migrasi Laravel. Draf mengusulkan InnoDB, `utf8mb4`, dan tipe `DECIMAL` untuk nominal rupiah. |
| Peramalan | Python, pandas, scikit-learn | Satu model Regresi Linier memproyeksikan omzet bulan berikutnya dari rekap pendapatan bulanan. Laravel menjalankan skrip Python secara internal. |
| Lingkungan target | VPS dan HTTPS | Menjalankan aplikasi web, database, dan skrip peramalan. Pengamanan aplikasi mencakup hash kata sandi, pemeriksaan hak akses, validasi input, dan perlindungan CSRF. |
| Kolaborasi | Git dan GitHub | Branch per tugas, pull request, peninjauan, dan merge ke `main`. |

Rancangan mengusulkan agar satu struk POS lunas dan aktif tercatat sebagai satu pemasukan, termasuk ketika struk memuat beberapa layanan. Harga jual disimpan per item agar perubahan harga master tidak mengubah nilai struk lama. Data historis untuk peramalan berasal dari CSV/Excel dengan periode awal Juli–September 2026. Pemetaan dan rekonsiliasi impor bergantung pada contoh data aktual.

## Struktur folder

Aplikasi Laravel ditempatkan di root repositori. Tampilan Kasir dan Owner memakai Blade dan JavaScript di `resources/`. Modul peramalan berada di `forecasting/` dan dipanggil Laravel sebagai proses internal. [Dokumentasi struktur Laravel](https://laravel.com/framework/docs/structure)

```text
.
├── app/
│   ├── Console/
│   │   └── Commands/              # Perintah Artisan untuk impor histori
│   ├── Http/
│   │   ├── Controllers/           # Alur web Kasir dan Owner
│   │   ├── Middleware/            # Pemeriksaan sesi dan hak akses
│   │   └── Requests/              # Validasi input
│   ├── Models/                    # Model Eloquent
│   └── Services/                  # Checkout, pembukuan, dan pemanggilan Python
├── bootstrap/                    # Bootstrap Laravel saat proyek diinisialisasi
├── config/                       # Konfigurasi aplikasi, database, dan peramalan
├── database/
│   ├── factories/                 # Pembuatan data uji
│   ├── migrations/                # Skema MySQL dan perubahan skema
│   └── seeders/                   # Data awal atau data fiktif untuk pengembangan
├── docs/
│   ├── tugas-kuliah/              # Acuan tugas mata kuliah Manajemen Proyek
│   └── pengerjaan-sistem/         # Desain database, rencana backend, dan bukti uji
├── forecasting/                  # Skrip Python dan daftar dependensinya
├── public/                       # Document root server web
├── resources/
│   ├── css/                       # CSS sumber
│   ├── js/                        # JavaScript sumber
│   └── views/
│       ├── auth/                   # Login
│       ├── master/                 # Layanan, kapster, dan pelanggan
│       ├── pos/                    # Checkout dan struk
│       ├── expenses/               # Input pengeluaran
│       └── owner/                  # Pembukuan, dashboard, dan proyeksi
├── routes/                       # Rute web dan console Laravel
├── storage/
│   └── app/
│       └── private/
│           └── imports/            # CSV/Excel asli pada lingkungan lokal
├── tests/
│   ├── Feature/                    # Uji alur web, akses peran, dan integrasi MySQL
│   └── Unit/                       # Uji aturan dan perhitungan
├── .gitignore
└── README.md
```

| Slice | Folder yang dipakai |
| --- | --- |
| 00 | `docs/pengerjaan-sistem/`, `bootstrap/`, `config/` untuk rancangan, kontrak data, dan penyiapan Laravel. |
| 01 | `app/Http/`, `app/Models/`, `routes/`, dan `resources/views/auth/` untuk login dan akses peran. |
| 02–04 | `database/`, `app/Models/`, `app/Http/`, dan `resources/views/master/` untuk data master. |
| 05–08 | `app/Services/`, `database/migrations/`, serta tampilan `pos/` dan `expenses/` untuk transaksi, pemasukan, pengeluaran, dan koreksi. |
| 09–10 | `app/Services/`, `resources/views/owner/`, dan `resources/js/` untuk pembukuan serta dashboard. |
| 11–12 | `app/Console/Commands/` untuk impor, `storage/app/private/imports/` untuk file lokal, serta `forecasting/`, `app/Services/`, dan `resources/views/owner/` untuk proyeksi. |
| 13–14 | `tests/` untuk kode uji, `docs/pengerjaan-sistem/` untuk bukti UAT dan penyerahan, serta `config/` dan `public/` untuk pengaturan lingkungan target. |

### Memulai dari scaffold

1. Salin isi folder scaffold ke root repositori di branch tugas. Sertakan `.gitignore` dan file `.gitkeep` agar Git menyimpan struktur folder kosong. Jika dokumen teknis sudah ada langsung di `docs/`, catat perpindahannya ke `docs/pengerjaan-sistem/` pada commit yang sama dengan pembaruan tautan.
2. Saat menyiapkan Slice 00, sepakati versi PHP, Laravel, MySQL, dan Python. Buat proyek Laravel di direktori kosong terpisah, lalu gabungkan kerangka aplikasinya ke root repositori sambil mempertahankan README, dokumentasi, dan aturan Git di `.gitignore` serta `storage/app/private/.gitignore`. Berkas seperti `artisan`, `composer.json`, dan `routes/web.php` berasal dari langkah ini. [Panduan penyiapan proyek Laravel](https://laravel.com/index.php/learn/getting-started-with-laravel/setting-up-your-laravel-project)
3. Gunakan `.env` lokal untuk koneksi MySQL. Simpan CSV/Excel aktual di `storage/app/private/imports/` dan gunakan data fiktif untuk data uji yang ikut di-commit. Tambahkan skrip dan berkas dependensi Python ke `forecasting/` saat Slice 11–12 dikerjakan.

## Status pengerjaan per slice

Draf desain database tercatat di branch `planning-backend-BahauddinRahmanHakim` dan menunggu peninjauan. Implementasi aplikasi, UAT, dan penerapan belum terverifikasi dari riwayat Git lokal.

| Slice | Pekerjaan dan indikator selesai | Status |
| --- | --- | --- |
| 00 | Tinjau ERD, kamus data, aturan transaksi, urutan migrasi, dan kontrak data dengan frontend. Catat keputusan yang diperlukan untuk slice berikutnya. | Draf tersedia, menunggu tinjauan |
| 01 | Login, logout, dan pembatasan akses. Kedua peran dapat masuk dan keluar. Akses langsung lintas peran dan permintaan perubahan tanpa token CSRF yang sah ditolak. | Direncanakan |
| 02 | Master layanan dan harga. Kasir dapat menambah, melihat, mengubah, dan mengarsipkan layanan. Harga tidak sah ditolak. | Direncanakan |
| 03 | Master kapster. Kasir dapat mengelola data kapster dan kapster yang diarsipkan hilang dari pilihan aktif. | Direncanakan |
| 04 | Master pelanggan. Kasir dapat mengelola data pelanggan dan membedakan pelanggan bernama sama berdasarkan ID. | Direncanakan |
| 05 | Checkout POS dan struk. Simpan pembayaran tunai atau nontunai, pertahankan harga historis, cegah struk ganda saat kirim ulang, dan batalkan seluruh penyimpanan jika salah satu item gagal. | Direncanakan |
| 06 | Pemasukan otomatis dari POS. Setiap struk lunas dan aktif menghasilkan tepat satu pemasukan. | Direncanakan |
| 07 | Pencatatan pengeluaran manual. Kasir dapat menyimpan pengeluaran yang sah dan kirim ulang permintaan yang sama tidak menggandakan catatan. | Direncanakan |
| 08 | Pembatalan dan koreksi. Catatan asal dan pengganti dapat ditelusuri, pembukuan menghitung catatan yang aktif, dan tindakan dibatasi sesuai kewenangan yang disepakati. | Direncanakan |
| 09 | Pembukuan Owner. Rekap pemasukan, pengeluaran, dan saldo per periode cocok dengan hitungan pembanding, dengan target selisih Rp0 pada data uji yang sama. | Direncanakan |
| 10 | Dashboard pendapatan harian, mingguan, dan bulanan. Total pada grafik konsisten dengan pembukuan untuk periode yang sama. | Direncanakan |
| 11 | Impor histori Juli–September 2026. Data aktual dapat direkonsiliasi dengan sumber dan impor ulang tidak menduplikasi catatan. | Direncanakan, menunggu contoh data aktual |
| 12 | Proyeksi bulanan pada dashboard Owner. Tampilkan bulan target, nominal, periode sumber, dan status kecukupan data. | Direncanakan |
| 13 | Uji integrasi MySQL, keamanan, dan UAT. Catat hasil uji terhadap QMP, temuan, perbaikan, dan hasil penerimaan pengguna. | Direncanakan |
| 14 | Penerapan setelah fungsi inti lulus uji, lalu evaluasi dan penyerahan. Catat hasil pemeriksaan lingkungan target dan keputusan penerimaan. | Direncanakan |

Simulasi kueri pada SQLite dengan data fiktif mendukung peninjauan desain. Pengujian migrasi, transaksi, dan konkurensi pada MySQL target dilakukan saat implementasi. Gunakan urutan dan dependensi pada desain database untuk mengambil tugas berikutnya. [Rincian pengerjaan per slice](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice)

## Alur kerja Git dan GitHub

Setiap anggota mengerjakan satu tugas atau slice pada branch tersendiri. Gunakan `main` sebagai tujuan pull request. Salinan lokal saat penyusunan README memiliki `master` dan branch perencanaan. Pengelola repositori memeriksa branch default di GitHub dan menyiapkan `main` jika belum tersedia sebelum tim mengikuti langkah berikut.

1. **Ambil tugas.** Cantumkan nomor slice, penanggung jawab, dependensi, dan kriteria selesai di catatan tugas atau issue. Untuk pekerjaan frontend dan backend yang saling terkait, sepakati format data serta respons validasi sebelum implementasi.
2. **Buat branch dari `main` terbaru.** Gunakan nama yang menunjukkan pekerjaan, misalnya `feat/slice-01-auth-akses`. Nama seperti `fix/slice-05-cegah-struk-ganda` dan `docs/slice-00-kontrak-data` bisa dipakai untuk perbaikan dan dokumentasi.

   ```bash
   git fetch origin
   git switch main
   git pull --ff-only origin main
   git switch -c feat/slice-01-auth-akses
   ```

3. **Kerjakan dan uji di branch tugas.** Uji alur normal dan kasus gagal yang relevan. Gunakan data fiktif untuk pengujian. Simpan `.env`, kredensial, dan file transaksi asli di luar commit. Periksa `git status`, lalu stage hanya berkas yang terkait dengan tugas.

   ```bash
   git commit -m "Implementasikan autentikasi dan akses peran"
   git push -u origin feat/slice-01-auth-akses
   ```

4. **Buka pull request ke `main`.** Cantumkan nomor slice, ringkasan perubahan, cara mencoba, dan bukti uji. Jika skema berubah, jelaskan cara menjalankan migrasi dan rollback. Jika ada perubahan antarmuka, sertakan contoh input dan keluaran untuk frontend.
5. **Tinjau, perbaiki, dan merge.** Anggota tim meninjau perubahan terhadap kriteria slice dan QMP yang terkait. Jika ada temuan atau konflik, perbaiki di branch tugas dan jalankan ulang pemeriksaan yang terdampak. Merge ke `main` setelah review disetujui dan pemeriksaan yang berlaku lulus.
6. **Perbarui status.** Catat tautan pull request dan ubah status slice sesuai hasil. Gunakan **Dalam pengerjaan** saat implementasi dimulai, **Dalam review** setelah pull request siap, dan **Selesai** setelah kriteria terpenuhi serta perubahan di-merge. Untuk UAT dan penyerahan, catat juga bukti uji atau keputusan penerimaan. Ambil `main` terbaru sebelum memulai tugas berikutnya.

## Dokumen acuan

- **Dokumen tugas kuliah** memuat daftar acuan observasi, Project Charter, dan Quality Management Plan. [Acuan tugas kuliah](docs/tugas-kuliah/README.md)
- **Desain Database MySQL Barbershop** untuk ERD, kamus data, migrasi, contoh rekonsiliasi, dan rincian Slice 00–14. [Draf desain database](docs/pengerjaan-sistem/Desain-Database-MySQL-Barbershop.md)
- **Rencana Backend** untuk konteks pengerjaan dan bukti yang perlu disiapkan. [Rencana backend](docs/pengerjaan-sistem/Rencana-Backend-Sistem-Operasional-Barbershop.md)
