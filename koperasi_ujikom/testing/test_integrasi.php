<?php
/**
 * ============================================================================
 * UNIT 10 (J.620100.034.02): MELAKSANAKAN PENGUJIAN INTEGRASI PROGRAM
 * Tujuan: Menguji alur integrasi antar modul:
 *         1. Modul Simpanan -> Trigger Update Saldo Anggota
 *         2. Modul Pinjaman (Stored Procedure + Function) -> Insert Pinjaman
 *         3. Modul Angsuran -> Trigger Pengurangan Sisa & Pelunasan Otomatis
 * ============================================================================
 */
require_once '../config/koneksi.php';

$log_integrasi = [];

try {
    // Mulai transaksi pengujian agar data dummy test tidak mengotori database asli
    $pdo->beginTransaction();

    // --- TEST INTEGRASI 1: Simpanan + Trigger Saldo Anggota ---
    $saldo_awal = $pdo->query("SELECT total_saldo_simpanan FROM anggota WHERE id_anggota = 2")->fetchColumn();
    
    $stmt = $pdo->prepare("INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal) VALUES (2, 1, 'Wajib', 250000)");
    $stmt->execute();
    
    $saldo_akhir = $pdo->query("SELECT total_saldo_simpanan FROM anggota WHERE id_anggota = 2")->fetchColumn();
    $pass_1 = ((float)$saldo_akhir === (float)($saldo_awal + 250000));
    
    $log_integrasi[] = [
        'kode'   => 'INT-01',
        'modul'  => 'PHP Insert Simpanan + Trigger trg_after_insert_simpanan',
        'detail' => "Saldo awal: $saldo_awal | Setor: 250000 | Saldo akhir otomatis: $saldo_akhir",
        'status' => $pass_1 ? 'PASS' : 'FAIL'
    ];

    // --- TEST INTEGRASI 2: Stored Procedure Pinjaman + Function Bunga ---
    $stmt_sp = $pdo->prepare("CALL sp_ajukan_pinjaman('PJM-TEST', 2, 1, 1000000, 2.0, 5, '2026-11-23')");
    $stmt_sp->execute();
    $stmt_sp->closeCursor(); // Wajib ditutup setelah memanggil Stored Procedure di PDO

    $data_pjm = $pdo->query("SELECT * FROM pinjaman WHERE no_pinjaman = 'PJM-TEST'")->fetch();
    // Pokok 1.000.000 + bunga (2% * 5 bln = 10% = 100.000) -> Total harus 1.100.000
    $pass_2 = ($data_pjm && (float)$data_pjm['total_tagihan'] === 1100000.0 && $data_pjm['status_pinjaman'] === 'Berjalan');

    $log_integrasi[] = [
        'kode'   => 'INT-02',
        'modul'  => 'PHP Call SP sp_ajukan_pinjaman + Function fn_hitung_total_tagihan',
        'detail' => "Total Tagihan terbentuk: {$data_pjm['total_tagihan']} | Status awal: {$data_pjm['status_pinjaman']}",
        'status' => $pass_2 ? 'PASS' : 'FAIL'
    ];

    // --- TEST INTEGRASI 3: Pelunasan Angsuran + Trigger Auto-Lunas ---
    $id_pjm_test = $data_pjm['id_pinjaman'];
    $stmt_ang = $pdo->prepare("INSERT INTO angsuran (id_pinjaman, id_user, angsuran_ke, jumlah_bayar) VALUES (?, 1, 1, 1100000)");
    $stmt_ang->execute([$id_pjm_test]);

    $cek_lunas = $pdo->query("SELECT sisa_tagihan, status_pinjaman FROM pinjaman WHERE id_pinjaman = $id_pjm_test")->fetch();
    $pass_3 = ((float)$cek_lunas['sisa_tagihan'] === 0.0 && $cek_lunas['status_pinjaman'] === 'Lunas');

    $log_integrasi[] = [
        'kode'   => 'INT-03',
        'modul'  => 'PHP Insert Angsuran + Trigger trg_after_insert_angsuran',
        'detail' => "Bayar penuh 1.100.000 -> Sisa tagihan: {$cek_lunas['sisa_tagihan']} | Status otomatis: {$cek_lunas['status_pinjaman']}",
        'status' => $pass_3 ? 'PASS' : 'FAIL'
    ];

    // Batalkan data simulasi pengujian agar database tetap bersih
    $pdo->rollBack();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error Pengujian Integrasi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Integration Testing - Unit 10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h4>Laporan Pengujian Integrasi Program (Unit J.620100.034.02)</h4>
    <p class="text-muted">Menguji integrasi end-to-end antara lapisan aplikasi PHP dan otomasi basis data MySQL.</p>
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Kode Uji</th>
                <th>Integrasi Modul yang Diuji</th>
                <th>Hasil Observasi Data</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($log_integrasi as $log): ?>
            <tr>
                <td><?= $log['kode'] ?></td>
                <td><?= $log['modul'] ?></td>
                <td><?= $log['detail'] ?></td>
                <td>
                    <span class="badge <?= $log['status'] == 'PASS' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $log['status'] ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>