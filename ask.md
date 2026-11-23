 1. Skalabilitas & Basis Data (Unit 1 - 3)

 Tanya: Kenapa pakai MySQL InnoDB dan dipisah antara PHP dan database?
 Jawab: Agar arsitektur client-server terpisah (decoupled) sehingga mudah di-scale. Engine InnoDB mendukung row-level locking, Foreign Key untuk integritas relasi, dan Transaction (`COMMIT`/`ROLLBACK`) saat transaksi tinggi tanpa membuat tabel terkunci total.




 Tanya: Apa bedanya Stored Procedure, Function, dan Trigger di proyek ini?
 Jawab: Function (`fn_hitung_total_tagihan`) mengembalikan satu nilai hasil perhitungan bunga pinjaman. Stored Procedure (`sp_ajukan_pinjaman`) menjalankan kumpulan perintah untuk memproses pinjaman baru. Trigger (`trg_after_insert_simpanan` & `angsuran`) berjalan otomatis di latar belakang setiap ada data masuk untuk memperbarui saldo dan status lunas.




 Tanya: Kapan `COMMIT` dan `ROLLBACK` digunakan?
 Jawab: Digunakan dalam satu blok transaksi keuangan (seperti bayar angsuran). Jika semua query berhasil, dijalankan `COMMIT` agar data tersimpan permanen. Jika salah satu gagal di tengah jalan, dijalankan `ROLLBACK` agar data kembali ke kondisi semula dan saldo tidak selisih.




 Tanya: Bagaimana cara mencegah SQL Injection di kode akses database?
 Jawab: Menggunakan PDO Prepared Statements (`prepare()` dan `execute()`), sehingga input dari pengguna diperlakukan murni sebagai data/parameter, bukan perintah SQL.



 2. Dokumentasi, Algoritma & Debugging (Unit 4 - 6)

 Tanya: Standar dokumentasi kode apa yang dipakai?
 Jawab: Menggunakan komentar PHPDoc (`/ ... /`) di setiap awal modul dan fungsi yang menjelaskan deskripsi, parameter (`@param`), serta nilai kembalian (`@return`), sehingga bisa di-generate otomatis menjadi dokumentasi teknis.




 Tanya: Apa contoh varian dan invarian di program ini?
 Jawab: Varian adalah variabel yang nilainya berubah selama proses/perulangan (misalnya `$sisa_tagihan` tiap kali cicilan masuk atau variabel `$row` di dalam `foreach`). Invarian adalah aturan yang selalu benar sebelum dan sesudah proses, misalnya `total_saldo_simpanan >= 0` dan `sisa_tagihan` tidak pernah minus.


 Tanya: Bagaimana kompleksitas algoritma (Big-O) pada aplikasi ini?
 Jawab: Pencarian anggota memakai indeks (`idx_no_anggota`) memiliki kompleksitas $\mathcal{O}(\log n)$ (B-Tree), sedangkan perulangan menampilkan semua baris data di tabel laporan memiliki kompleksitas waktu $\mathcal{O}(n)$ sesuai jumlah data.


 Tanya: Bagaimana cara kamu melakukan debugging jika terjadi error?
 Jawab: Mengaktifkan mode eksepsi pada PDO (`PDO::ERRMODE_EXCEPTION`) serta `display_errors` di PHP untuk melacak pesan error dan nomor barisnya secara spesifik, lalu menguji ulang query-nya langsung di SQL.



 3. Profiling, Code Review & Testing (Unit 7 - 10)

 Tanya: Bagaimana cara mengetahui bagian program yang lambat (profiling)?
 Jawab: Di PHP menggunakan fungsi `microtime(true)` untuk menghitung durasi eksekusi kode (ms) dan `memory_get_usage()` untuk memantau penggunaan RAM. Di sisi database menggunakan perintah `EXPLAIN` untuk mengecek apakah query sudah memakai index atau masih full table scan.


 Tanya: Apa saja yang diperiksa saat melakukan Code Review?
 Jawab: Memeriksa standar penulisan kode, modularitas (fungsi tidak ditulis berulang), celah keamanan (seperti SQL Injection dan XSS dengan `htmlspecialchars()`), serta validasi logika bisnis.


 Tanya: Apa perbedaan Unit Testing dan Integration Testing?
 Jawab: Unit Testing menguji satu fungsi kecil secara terisolasi (misalnya menguji fungsi hitung bunga dengan input normal dan input minus menggunakan assertion). Integration Testing menguji alur gabungan antar-modul dari awal sampai akhir (misalnya form PHP $\rightarrow$ Stored Procedure $\rightarrow$ Trigger DB memperbarui tabel lain).



####

 1. Seputar Alur PHP & HTTP (`POST`, `GET`, `SESSION`)

 Tanya: Kenapa form tambah data pakai `method="POST"`, sedangkan hapus data pakai `GET` (`?hapus=...`)?
 Jawab: `POST` dipakai untuk mengirim data sensitif atau data baru agar tidak muncul di URL dan lebih aman. `GET` dipakai untuk parameter ringan yang dikirim lewat URL (seperti ID data yang mau dihapus atau dicari).


 Tanya: Fungsi `session_start()` dan `$_SESSION` di atas kode itu buat apa?
 Jawab: Untuk menyimpan data login petugas di sisi server selama browser masih terbuka, sehingga sistem tahu siapa petugas yang sedang bertransaksi dan mencegah orang luar mengakses halaman dalam tanpa login.


 Tanya: Kenapa setelah `INSERT` atau `UPDATE` selalu ada `header("Location: ...")` dan `exit;`?
 Jawab: Untuk me-redirect halaman agar saat user me-refresh browser (F5), data form tidak terkirim ulang dua kali (double submit). `exit;` dipakai untuk memastikan skrip di bawahnya berhenti dieksekusi setelah redirect.



 2. Seputar Koneksi & Query Database (`PDO` & `SQL`)

 Tanya: Apa bedanya `fetch()` dan `fetchAll()`?
 Jawab: `fetch()` hanya mengambil satu baris data pertama (cocok untuk cek login atau detail satu pinjaman), sedangkan `fetchAll()` mengambil seluruh baris data dalam bentuk array untuk ditampilkan di tabel pakai `foreach`.


 Tanya: Tanda tanya (`?`) di query `prepare("... WHERE username = ?")` itu maksudnya apa?
 Jawab: Itu adalah placeholder (parameter ikat) dari PDO. Nilainya diisi belakangan lewat `execute([$username])` supaya karakter aneh dari input user tidak merusak struktur query (anti SQL Injection).


 Tanya: Kenapa di query laporan pakai `LEFT JOIN`, bukan `INNER JOIN`?
 Jawab: Supaya anggota yang belum punya pinjaman tetap ikut tampil di tabel laporan (dengan nilai pinjaman 0). Kalau pakai `INNER JOIN`, anggota yang tidak punya pinjaman akan hilang dari daftar.


 Tanya: Fungsi `COALESCE(SUM(...), 0)` di SQL itu buat apa?
 Jawab: Untuk mengubah nilai `NULL` menjadi angka `0` kalau belum ada data transaksi sama sekali, sehingga perhitungan di PHP tidak error atau kosong.



 3. Seputar Dasar Pemrograman & Tampilan (`HTML` / `PHP`)

 Tanya: Apa bedanya `require_once` dan `include`?
 Jawab: `require_once` akan langsung menghentikan program (fatal error) jika file koneksinya tidak ketemu, dan memastikan file hanya dipanggil satu kali agar tidak terjadi bentrok fungsi ganda.


 Tanya: Kenapa tiap menampilkan nama anggota selalu dibungkus `htmlspecialchars()`?
 Jawab: Untuk mencegah serangan XSS (Cross-Site Scripting), yaitu agar karakter HTML seperti `<script>` yang mungkin diinput user ditampilkan sebagai teks biasa, bukan dijalankan oleh browser.


 Tanya: Tipe data `DECIMAL(15, 2)` di tabel database maksudnya apa?
 Jawab: Untuk menyimpan angka keuangan dengan presisi tinggi (maksimal 15 digit total dengan 2 angka di belakang koma), jauh lebih akurat dibanding `FLOAT` atau `DOUBLE` yang rawan selisih pembulatan.
