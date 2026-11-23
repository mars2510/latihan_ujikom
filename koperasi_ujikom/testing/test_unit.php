<?php
/**
 * ============================================================================
 * UNIT 9 (J.620100.033.02): MELAKSANAKAN PENGUJIAN UNIT PROGRAM (UNIT TESTING)
 * Tujuan: Menguji fungsi-fungsi tunggal secara terisolasi menggunakan mekanisme
 *         Assertion (membandingkan Expected Output vs Actual Output).
 * ============================================================================
 */
require_once '../config/koneksi.php';

// Fungsi unit murni untuk menghitung bunga flat (diisolasi dari database)
function hitung_tagihan_unit($pokok, $bunga_persen, $tenor) {
    if ($pokok <= 0 || $tenor <= 0 || $bunga_persen < 0) {
        return false; // Validasi input tidak valid
    }
    return $pokok + ($pokok * ($bunga_persen / 100) * $tenor);
}

// Fungsi Assertion sederhana (pengganti PHPUnit agar mudah dijalankan tanpa composer)
function assert_equals($id_test, $skenario, $expected, $actual) {
    $lulus = ($expected === $actual);
    return [
        'id'       => $id_test,
        'skenario' => $skenario,
        'expected' => var_export($expected, true),
        'actual'   => var_export($actual, true),
        'hasil'    => $lulus ? 'PASS' : 'FAIL'
    ];
}

$hasil_unit_test = [
    // UT-01: Menguji fungsi format_rupiah()
    assert_equals("UT-01", "Format Rupiah bilangan bulat 500000", "Rp 500.000", format_rupiah(500000)),
    
    // UT-02: Menguji perhitungan pinjaman normal (Pokok 1.000.000, Bunga 2%, Tenor 10 bln -> 1.200.000)
    assert_equals("UT-02", "Hitung tagihan pinjaman normal (Positive Test)", 1200000.0, hitung_tagihan_unit(1000000, 2, 10)),
    
    // UT-03: Menguji perhitungan pinjaman tanpa bunga (0%)
    assert_equals("UT-03", "Hitung tagihan bunga 0% (Boundary Test)", 1000000.0, hitung_tagihan_unit(1000000, 0, 12)),
    
    // UT-04: Menguji input negatif/tidak valid (Negative Test)
    assert_equals("UT-04", "Tolak pinjaman dengan pokok minus (Negative Test)", false, hitung_tagihan_unit(-500000, 1.5, 12))
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Unit Testing - Unit 9</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h4>Laporan Pengujian Unit Program (Unit J.620100.033.02)</h4>
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID Test</th>
                <th>Skenario Pengujian Unit</th>
                <th>Expected Output</th>
                <th>Actual Output</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil_unit_test as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= $t['skenario'] ?></td>
                <td><code><?= $t['expected'] ?></code></td>
                <td><code><?= $t['actual'] ?></code></td>
                <td>
                    <span class="badge <?= $t['hasil'] == 'PASS' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $t['hasil'] ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>