* **Buat folder baru di htdocs**

koperasi\_ujikom/

├── config/

│   └── koneksi.php          # Koneksi PDO + Poin 10, 11 (Akses DB \& Error Handling)

├── database/

│   └── db\_koperasi.sql      # Skema DB, Relasi, SP, Function, Trigger, Transaction (Poin 1-9)

├── testing/

│   ├── profiler.php	     # Unit 7: Profiling waktu eksekusi \& penggunaan memori

│   ├── test\_unit.php         # Unit 9: Unit Testing fungsi \& logika terisolasi

│   └── test\_integrasi.php    # Unit 10: Integration Testing (PHP + SP + Trigger DB)

├── login.php                # Fitur 1: Halaman Login \& Autentikasi

├── logout.php               # Proses Logout

├── index.php                # Fitur 2: Dashboard Ringkasan \& Navigasi

├── anggota.php              # Fitur 3: Menu Data Master Anggota (CRUD)

├── simpanan.php             # Fitur 4a: Transaksi Simpanan (Trigger otomatis)

├── pinjaman.php             # Fitur 4b: Pengajuan Pinjaman (Stored Procedure + Function)

├── angsuran.php             # Fitur 4c: Bayar Angsuran (Transaction Commit/Rollback + Trigger)

└── laporan.php              # Fitur 5: Menu Laporan Keuangan \& Rekapitulasi



**===**

* **Kode SQL - database/db\_koperasi.sql** (**DDL, DML, Relasi Foreign Key, Stored Procedure, Function, Trigger, serta contoh COMMIT \& ROLLBACK)**



**===**

* **Varian \& Invarian (Poin 13):**

Varian: Variabel yang nilainya berubah-ubah selama eksekusi loop atau transaksi, contohnya $row, $grand\_simpanan, atau sisa\_tagihan tiap kali cicilan masuk.



Invarian: Kondisi yang selalu bernilai benar sebelum dan sesudah proses berjalan. Contoh: total\_saldo\_simpanan >= 0 dan sisa\_tagihan selalu sama dengan total\_tagihan - SUM(jumlah\_bayar).



* **Alur Logika \& Teknik Algoritma (Poin 14 \& 15):**

Menggunakan Sequence (urutan koneksi -> validasi input -> eksekusi query -> redirect), Selection/Percabangan (if ($jumlah\_bayar > $sisa\_tagihan) dan IF v\_sisa\_baru <= 0 di Trigger), serta Iteration/Perulangan (foreach saat menampilkan data tabel).



* **Kompleksitas Algoritma (Poin 17):**

Pencarian data anggota berdasarkan no\_anggota atau id\_anggota (Primary Key / Indexed) memiliki kompleksitas $\\mathcal{O}(\\log n)$ menggunakan B-Tree Index InnoDB.



Menampilkan daftar seluruh data di tabel HTML (foreach) memiliki kompleksitas waktu $\\mathcal{O}(n)$ dan kompleksitas ruang $\\mathcal{O}(n)$, di mana $n$ adalah jumlah baris data.



* **Debugging \& Perbaikan Program (Poin 12, 19, 20):**

Aktifnya PDO::ATTR\_ERRMODE => PDO::ERRMODE\_EXCEPTION di koneksi.php memastikan setiap error sintaks SQL atau pelanggaran constraint langsung tertangkap jelas tanpa merusak integritas data (dilindungi oleh rollBack()).



* **Dokumentasi Kode Program (Poin 21 - 24):**

Struktur komentar PHPDoc (/\*\* ... \*/) di atas file dan fungsi pada proyek ini sudah siap di-generate otomatis menjadi dokumentasi teknis web menggunakan alat seperti phpDocumentor atau Doxygen.



**###**

* **Unit 8: (J.620100.032.01) - Menerapkan Code Review**

Inti Kompetensi: Memeriksa kualitas kode sumber (coding standard, keamanan, maintainability, dan efisiensi) sebelum dirilis, lalu mendokumentasikan temuan \& perbaikannya.



Berikut tabel Checklist \& Laporan Hasil Code Review pada proyek koperasi\_ujikom :

|**No.**|**File / Modul**|**Aspek Review**|**Temuan Awal (Sebelum Review)**|**Hasil Perbaikan (Sesudah Review)**|**Status**|
|-|-|-|-|-|-|
|1|login.php \& semua form|Keamanan (SQL Injection)|Query langsung menggabungkan variabel input user ("... WHERE username = '$username'").|Menggunakan PDO Prepared Statements (prepare() \& execute(\[$username])).|Lulus|
|2|anggota.php \& laporan.php|Keamanan (XSS)|Output nama anggota dicetak langsung dengan <?= $row\['nama\_anggota'] ?>.|Dibungkus dengan htmlspecialchars() untuk mencegah eksekusi skrip berbahaya.|Lulus|
|3|simpanan.php \& angsuran.php|Integritas Data|Insert transaksi dan update saldo terpisah tanpa pengaman jika terjadi error di tengah proses.|Diterapkan beginTransaction(), commit(), rollBack(), serta Database Trigger.|Lulus|
|4|config/koneksi.php|Standar Kode \& Modularitas|Koneksi database dan fungsi format\_rupiah() ditulis berulang di setiap file.|Dipusatkan di satu file config/koneksi.php dengan komentar PHPDoc standar.|Lulus|
|5|angsuran.php|Validasi Logika Bisnis|Tidak ada pengecekan jika nominal bayar melebihi sisa pinjaman (menyebabkan saldo minus).|Ditambahkan validasi if ($jumlah\_bayar > $pinjaman\['sisa\_tagihan']) di PHP \& pengaman IF v\_sisa\_baru <= 0 di Trigger.|Lulus|



**Unit 7: (J.620100.031.01) - Melakukan Profiling Program**

Inti Kompetensi: Mengukur performa kode (waktu eksekusi dan konsumsi memori RAM) untuk mendeteksi bottleneck (lambat/boros memori) sesuai permasalahan skalabilitas di studi kasus Koperasi.



**Unit 9: (J.620100.033.02) - Melaksanakan Pengujian Unit Program (Unit Testing).**

Inti Kompetensi: Menguji bagian terkecil dari program (fungsi/prosedur tunggal) secara terisolasi menggunakan skenario Positive Test (input normal) dan Negative Test (input batas/salah) dengan metode Assertion.



**Unit 10: (J.620100.034.02) - Melaksanakan Pengujian Integrasi Program**

Inti Kompetensi: Menguji keterhubungan antar-modul secara menyeluruh (end-to-end), yaitu antara Kode PHP -> Stored Procedure / Query SQL -> Trigger Database -> Perubahan Data di Tabel Lain.

