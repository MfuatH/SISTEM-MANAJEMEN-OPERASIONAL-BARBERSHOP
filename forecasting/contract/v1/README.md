# Kontrak JSON Forecasting Owner v1

Frontend dapat memuat [fixture](fixtures/) untuk enam status halaman. [Skema respons](response.schema.json) menjadi acuan bentuk data. Pada Slice 12, endpoint `GET /owner/forecasting/data` memakai autentikasi Owner aktif. Grafik Batang dan Garis pada halaman acuan Forecasting memakai deret bulan dari respons yang sama. Tiga nilai historis dan satu prediksi saat tersedia ditampilkan menurut urutan periode. Frontend dapat mengganti presentasinya dengan mempertahankan periode, status, dan tipe nilai dalam kontrak ini.

- `ready` memuat tiga bulan aktual lengkap berurutan dan satu bulan prediksi tepat sesudahnya. `stale` memuat hasil terakhir dengan periode dan waktu pembangkitan aslinya.
- `disabled`, `processing`, `insufficient_data`, dan `unavailable` membawa array prediksi kosong. `reason_code` dapat dipetakan ke pesan, dengan teks umum bila kode belum dikenal.
- Bulan memakai format `YYYY-MM` pada kalender WIB. Target nilai adalah pemasukan kotor layanan dan produk terjual. Nominal dikirim sebagai string desimal rupiah utuh. Untuk grafik, gunakan konversi numerik hanya pada tahap penggambaran.
- `data_kind` menunjukkan sumber hasil. Nilainya `synthetic`, `actual`, `mixed`, atau `null` jika belum ada sumber. Tampilkan label untuk data sintetis dan campuran. `evaluation` dapat kosong, dan hasil sintetis tidak menetapkan akurasi lapangan.

Angka pada fixture `ready` dan `stale` adalah contoh respons. Gunakan bulan pada respons untuk label grafik. Jika evaluasi tersedia, `test_month` dan `baseline_mae_idr` dapat menyertai metrik utama.
