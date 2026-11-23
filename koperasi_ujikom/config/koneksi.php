<?php
/**
 * ============================================================================
 * MODUL: KONEKSI BASIS DATA & FUNGSI HELPER (Poin 10, 11, 22, 23)
 * Deskripsi: Mengatur koneksi ke MySQL menggunakan PDO (PHP Data Objects)
 *            agar aman dari SQL Injection dan mendukung Transaction.
 * ============================================================================
 */
session_start();

$host = "localhost";
$db   = "db_koperasi";
$user = "root";
$pass = ""; // Sesuaikan jika MySQL kamu memakai password

try {
    // Membuat instance koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Mengatur mode error agar melempar Exception saat terjadi kesalahan SQL (Debugging Poin 19)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Basis Data Gagal: " . $e->getMessage());
}

/**
 * Fungsi Format Rupiah (Poin 16 & 23: Dokumentasi Fungsi)
 * @param float $angka Nominal uang
 * @return string Format mata uang Rupiah (Contoh: Rp 500.000)
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Prosedur Cek Autentikasi Login (Aturan Bisnis #1)
 * Mencegah akses langsung ke halaman dalam tanpa login.
 */
function cek_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: login.php");
        exit;
    }
}
?>