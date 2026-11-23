<?php
require_once 'config/koneksi.php';

$hash_baru = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username IN ('admin', 'kasir')");
$stmt->execute([$hash_baru]);

echo "Berhasil! Password untuk admin dan kasir sekarang sudah di-set ke: admin123";