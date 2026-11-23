<?php
/**
 * ============================================================================
 * UNIT 7 (J.620100.031.01): MELAKUKAN PROFILING PROGRAM
 * Tujuan: Mengukur waktu eksekusi (ms) dan penggunaan memori (KB) pada query
 *         dan proses transaksi Koperasi Sejahtera Mandiri.
 * ============================================================================
 */
require_once '../config/koneksi.php';

function jalankan_profiling($nama_proses, callable $callback) {
    // 1. Catat waktu & memori SEBELUM proses berjalan
    $mem_awal   = memory_get_usage();
    $waktu_awal = microtime(true);

    // 2. Jalankan fungsi/query yang diuji
    $hasil = $callback();

    // 3. Catat waktu & memori SESUDAH proses berjalan
    $waktu_akhir = microtime(true);
    $mem_akhir   = memory_get_usage();

    // 4. Hitung selisihnya
    $durasi_ms = round(($waktu_akhir - $waktu_awal) * 1000, 4);
    $memori_kb = round(($mem_akhir - $mem_awal) / 1024, 2);

    return [
        'proses' => $nama_proses,
        'durasi' => $durasi_ms . ' ms',
        'memori' => $memori_kb . ' KB',
        'status' => ($durasi_ms < 100) ? 'OPTIMAL (< 100ms)' : 'BOTTLENECK (> 100ms)'
    ];
}

// Skenario 1: Profiling Pencarian Anggota Terindeks (idx_no_anggota)
$profil_1 = jalankan_profiling("Query Cari Anggota (Indexed: AGT-001)", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE no_anggota = ?");
    $stmt->execute(['AGT-001']);
    return $stmt->fetch();
});

// Skenario 2: Profiling Query JOIN Laporan Keuangan
$profil_2 = jalankan_profiling("Query Laporan Relasional (LEFT JOIN + GROUP BY)", function() use ($pdo) {
    return $pdo->query("
        SELECT a.no_anggota, a.nama_anggota, a.total_saldo_simpanan, COALESCE(SUM(p.sisa_tagihan), 0) AS sisa
        FROM anggota a LEFT JOIN pinjaman p ON a.id_anggota = p.id_anggota
        GROUP BY a.id_anggota
    ")->fetchAll();
});

// Skenario 3: Profiling Function SQL Perhitungan Bunga
$profil_3 = jalankan_profiling("Eksekusi Function SQL fn_hitung_total_tagihan", function() use ($pdo) {
    return $pdo->query("SELECT fn_hitung_total_tagihan(5000000, 1.5, 12)")->fetchColumn();
});

$hasil_profiling = [$profil_1, $profil_2, $profil_3];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profiling Program - Unit 7</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h4>Hasil Profiling Performa Aplikasi (Unit J.620100.031.01)</h4>
    <p class="text-muted">Peak Memory Usage PHP: <strong><?= round(memory_get_peak_usage() / 1024, 2) ?> KB</strong></p>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Nama Proses / Modul</th>
                <th>Waktu Eksekusi</th>
                <th>Alokasi Memori</th>
                <th>Evaluasi Performa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil_profiling as $p): ?>
            <tr>
                <td><?= $p['proses'] ?></td>
                <td><?= $p['durasi'] ?></td>
                <td><?= $p['memori'] ?></td>
                <td><span class="badge bg-success"><?= $p['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>