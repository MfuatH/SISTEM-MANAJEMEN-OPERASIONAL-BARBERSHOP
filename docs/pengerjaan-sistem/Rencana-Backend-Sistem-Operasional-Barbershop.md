# Rencana Pengerjaan Backend Sistem Manajemen Operasional Barbershop

**Penanggung jawab backend:** Bahauddin Rahman Hakim  
**Tim:** Kelompok 6, Kelas 2023C  
**Langkah pertama:** Susun dan sepakati desain database MySQL pada sesi terpisah. Setelah desain cukup untuk kebutuhan inti, implementasikan skema melalui migrasi Laravel.

## 1. Dasar proyek

Project Charter menetapkan aplikasi web untuk satu lokasi barbershop dengan **Laravel, MySQL, dan satu model Regresi Linier melalui Python**. QMP versi 1.1 mencantumkan Pangkas Rambut Upin Ipin 11 sebagai lokasi uji dan menetapkan dua peran pengguna. **Karyawan/Kasir** menangani input transaksi dan pengeluaran. **Owner/Pemilik** melihat pembukuan, dashboard pendapatan, dan peramalan. Pembayaran tunai atau nontunai dicatat manual.

Pakai **Project Charter** untuk lingkup dan teknologi, serta **QMP** untuk kriteria dan bukti pengujian. Slide observasi memberi konteks masalah. Untuk keputusan penerimaan, QMP mencantumkan Andik Ari Mulyono sebagai penerima hasil, sementara kolom penerimaan akhir di Charter masih terbuka. Manajer Proyek menyelaraskan pencatatan wewenang ini sebelum penerimaan akhir.

## 2. Prioritas pertama: desain database

Mulai dari dua alur yang menentukan struktur data. Pertama, Kasir mencatat penjualan dan pengeluaran. Kedua, Owner melihat pembukuan dan pendapatan untuk suatu periode. Struktur peramalan mengikuti sumber dan bentuk data yang benar-benar tersedia.

| Area | Kebutuhan dari dokumen proyek | Keputusan desain yang perlu diambil |
| --- | --- | --- |
| Akun dan hak akses | Ada akun Kasir dan Owner. Kapster dicatat sebagai data operasional. | Tentukan apakah setiap kapster memiliki akun atau cukup menjadi data kapster. Sepakati siapa yang boleh mengubah setiap data. |
| Data master | Data layanan dan harga, kapster, serta pelanggan dikelola di database terpusat. | Tentukan field minimum pelanggan dan penanganan data master yang sudah dipakai dalam transaksi. Usulan awal adalah mengarsipkan data yang sudah direferensikan agar riwayat tetap utuh. Sepakati perilaku hapus untuk KP-01. |
| Transaksi POS | Transaksi mencatat pelanggan, layanan, kapster, total, dan informasi pembayaran manual. | Pastikan satu transaksi bisa memuat satu atau beberapa layanan, serta apakah kapster dipilih per transaksi atau per layanan. Usulkan penyimpanan harga pada saat transaksi agar perubahan harga layanan tidak mengubah histori. |
| Pemasukan dan pengeluaran | Pemasukan tercatat otomatis dari POS. Pengeluaran dicatat manual. | Usulan awal adalah menjadikan transaksi sah sebagai sumber pemasukan. Jika tim memilih tabel pemasukan terpisah, tetapkan satu referensi unik ke transaksi POS agar pendapatan tidak terhitung dua kali. |
| Pembukuan dan dashboard | Total pendapatan harian, mingguan, dan bulanan sesuai data transaksi periode yang sama. | Sepakati tanggal yang dipakai dalam laporan, zona waktu, awal minggu, serta perlakuan atas koreksi atau pembatalan. Bedakan omzet dari selisih pemasukan dan pengeluaran jika keduanya ditampilkan. |
| Data historis peramalan | Charter meminta minimal tiga bulan data transaksi Juli–September 2026 dalam CSV atau Excel. | Periksa contoh data, kolom tanggal dan nominal, granularitas, duplikasi, serta kelengkapan. Putuskan apakah data historis masuk ke POS atau disimpan sebagai sumber khusus peramalan. Jika sumber digabung, tentukan aturan deduplikasi. |

**Keluaran sesi database:** ERD beserta kardinalitas, kamus data, aturan integritas, catatan keputusan terbuka, urutan migrasi, dan contoh data uji fiktif. Kamus data mencakup tipe kolom, kunci, relasi, indeks yang diperlukan, serta perilaku penghapusan. Gunakan representasi nilai uang yang presisi, misalnya kolom MySQL `DECIMAL`, dan pertahankan presisi yang sama pada perhitungan backend. [Dokumentasi MySQL tentang tipe nilai presisi](https://dev.mysql.com/doc/refman/8.4/en/fixed-point-types.html)

**Desain cukup untuk mulai migrasi** jika alur transaksi, penyimpanan harga historis, sumber angka pembukuan, dan batas hak akses sudah bisa dijelaskan dari ERD. Keputusan yang memengaruhi struktur tabel perlu disepakati sebelum migrasi terkait dibuat. Pemetaan impor dan evaluasi peramalan bisa dirinci setelah contoh data tersedia.

## 3. Catatan historis jadwal awal

Tabel ini merekam rencana awal backend berdasarkan jadwal delapan minggu pada Project Charter. Minggu pada tabel menunjukkan target yang tercantum dalam rencana awal. Untuk mengurutkan pekerjaan di GitHub, gunakan dependensi dan kriteria selesai pada [rincian pengerjaan per slice](Desain-Database-MySQL-Barbershop.md#10-urutan-pengerjaan-per-slice).

| Waktu | Fokus dan pekerjaan backend | Keluaran |
| --- | --- | --- |
| **Minggu 1–2** | Petakan kebutuhan, alur kasir dan Owner, serta keterkaitannya dengan KP-01 sampai KP-07. Minta contoh data historis dan klarifikasi keputusan yang memengaruhi skema. | Daftar kebutuhan backend, rancangan awal database, dan catatan aturan bisnis yang perlu diputuskan. |
| **Minggu 3** | Finalkan ERD dan kamus data. Sepakati dengan frontend bentuk pertukaran data, pesan validasi, dan contoh respons untuk POS, pengeluaran, dashboard, serta peramalan. | Desain database dan kontrak antarmuka yang dapat dipakai bersama. |
| **Minggu 4** | Buat migrasi, model, dan data awal untuk lingkungan pengembangan. Kerjakan autentikasi sesi, otorisasi peran di server, CRUD master, dan alur simpan transaksi POS. Hitung total di server dan simpan semua catatan yang harus berhasil bersama dalam satu transaksi database. | Skema dapat dibangun ulang pada database pengembangan baru. Alur inti Kasir tersedia untuk integrasi. |
| **Minggu 5** | Selesaikan pengeluaran, pembukuan, dan agregasi pendapatan harian, mingguan, serta bulanan. Integrasikan Laravel dengan skrip Python internal untuk proyeksi dan penanganan keadaan data belum cukup. | Modul backend siap diuji. Perhitungan laporan berasal dari sumber data yang disepakati. |
| **Minggu 6–7** | Siapkan data dan lingkungan uji. Jalankan uji unit atau integrasi untuk aturan transaksi, uji rekonsiliasi, uji akses langsung ke rute terbatas, serta pemeriksaan sesi, hashing kata sandi, validasi input, CSRF, dan HTTPS. Dukung UAT bersama Kasir dan Owner, catat temuan, lalu lakukan uji ulang setelah perbaikan. | Bukti pengujian dan status tiap kriteria penerimaan. Laporan pengujian selesai akhir minggu ke-7. |
| **Minggu 7** | Usulkan urutan uji fungsi inti dan UAT alur kritis, lalu perbaikan serta uji ulang bila diperlukan, sebelum penerapan. Siapkan konfigurasi produksi, cadangan data, migrasi, dan pemeriksaan singkat sesudah penerapan. | Sistem terpasang dan alur inti diperiksa di lingkungan target. |
| **Minggu 8** | Serahkan dokumentasi pemasangan, skema, konfigurasi yang diperlukan, dan daftar masalah yang masih terbuka. Serahkan bukti uji kepada Manajer Proyek untuk evaluasi dan penerimaan akhir. | Dokumentasi backend dan paket bukti untuk keputusan penerimaan. |

Tautan berikut memakai dokumentasi Laravel 13.x sebagai contoh. Cocokkan versinya dengan proyek sebelum mulai implementasi. [Migrasi Laravel](https://laravel.com/docs/13.x/migrations) · [Transaksi database Laravel](https://laravel.com/docs/13.x/database#database-transactions) · [Otorisasi Laravel](https://laravel.com/docs/13.x/authorization)

## 4. Bukti yang harus disiapkan backend

| Kriteria QMP | Pemeriksaan dan bukti |
| --- | --- |
| **KP-01 Data operasional** | Uji operasi layanan, kapster, dan pelanggan sesuai peran. Periksa perlakuan terhadap data master yang sudah terhubung ke transaksi. Simpan hasil aktual tiap skenario. |
| **KP-02 POS dan pembukuan** | Simpan transaksi uji dan pengeluaran. Bandingkan total POS serta pembukuan dengan perhitungan pembanding independen untuk data dan periode yang sama. Target QMP adalah **selisih Rp0**. Uji kegagalan simpan agar transaksi tidak tertinggal separuh. |
| **KP-03 Dashboard** | Rekonsiliasi total harian, mingguan, dan bulanan terhadap data transaksi, termasuk data di batas periode. Simpan data uji, hasil pembanding, dan keluaran dashboard. |
| **KP-04 dan KP-05 Akses dan keamanan** | Uji akses sah dan penolakan akses yang dilarang untuk kedua peran, termasuk permintaan langsung ke server. Catat bukti verifikasi sesi, hashing, validasi, CSRF, HTTPS, dan eksekusi modul peramalan secara internal. |
| **KP-06 Peramalan** | Periksa proyeksi tampil di dashboard secara terpisah dari evaluasi galat. Jika data memadai, evaluasi menggunakan nilai aktual dari periode setelah data latih. MAE dalam rupiah adalah usulan metrik di QMP. Jika data pembanding belum cukup, catat evaluasi galat sebagai **belum terverifikasi** dan laporkan ke Manajer Proyek. [Dokumentasi pemisahan data menurut waktu](https://scikit-learn.org/stable/modules/generated/sklearn.model_selection.TimeSeriesSplit.html) |
| **KP-07 Penyerahan** | Catat versi aplikasi dan migrasi, hasil pemeriksaan sebelum penerapan, laporan uji, catatan perbaikan, dan cara menjalankan ulang sistem. |

## 5. Brief siap disalin ke chat database

```text
Saya backend developer Laravel untuk Sistem Manajemen Operasional Barbershop,
Kelompok 6 Kelas 2023C. Fokus sesi ini adalah merancang database MySQL.
Saya akan melampirkan Project Charter (menpro 2.pdf) dan Quality Management Plan
versi 1.1. Gunakan Charter untuk lingkup dan QMP untuk kriteria KP-01 sampai KP-07.

Sistem melayani satu lokasi dengan dua peran, Karyawan/Kasir dan Owner/Pemilik.
Fitur inti meliputi layanan dan harga, kapster, pelanggan, transaksi POS dengan
pembayaran tunai/nontunai yang dicatat manual, pemasukan otomatis, pengeluaran
manual, pembukuan, dashboard pendapatan harian/mingguan/bulanan, dan satu model
Regresi Linier Python untuk proyeksi bulanan. Charter meminta data historis
transaksi Juli–September 2026 dalam CSV/Excel. Struktur file aktual belum saya
berikan, jadi catat asumsi yang diperlukan.

Buat ERD dengan kardinalitas, kamus data, kunci primer dan kunci asing, aturan
integritas dan penghapusan, indeks utama, serta urutan migrasi Laravel.
Sertakan alur pencatatan transaksi sampai laporan dan contoh data uji fiktif
untuk membuktikan total sesuai perhitungan pembanding serta pemasukan tidak
terhitung ganda.

Sebelum menetapkan desain, identifikasi keputusan yang memengaruhi skema:
jumlah layanan per transaksi, hubungan akun dengan kapster, penyimpanan harga
historis, pembatalan atau koreksi, sumber pemasukan, batas periode laporan,
dan perlakuan terhadap data historis. Tandai pilihan teknis sebagai usulan
dan kebutuhan yang belum dikonfirmasi sebagai keputusan terbuka.
Mulai dari desain, lalu saya akan meninjau hasilnya sebelum implementasi.
```

## Dokumen acuan proyek

- Project Charter, hlm. 1–2, untuk lingkup, teknologi, batasan, data historis transaksi, dan jadwal awal.
- Quality Management Plan versi 1.1, hlm. 1 dan 4–6, untuk lokasi uji, peran, metrik, kriteria penerimaan, dan UAT.
- Presentasi Manajemen Operasional Barbershop, slide 5–12 dan 15, untuk masalah awal, alur kerja, dan jadwal awal.

## Jadwal pelaksanaan aktual

Durasi pelaksanaan proyek adalah **15 minggu**. Rincian target tiap minggu, pembagian slice, dan milestone akan ditambahkan di bagian ini setelah jadwal tim disepakati.
