<?php
require_once 'config/koneksi.php';
cek_login();

// Query Relasional Lengkap (Menggabungkan Anggota, Pinjaman Aktif, dan Total Angsuran Masuk)
$laporan = $pdo->query("
    SELECT 
        a.no_anggota, 
        a.nama_anggota, 
        a.total_saldo_simpanan,
        COALESCE(SUM(p.total_tagihan), 0) AS total_pinjaman,
        COALESCE(SUM(p.sisa_tagihan), 0) AS sisa_hutang
    FROM anggota a
    LEFT JOIN pinjaman p ON a.id_anggota = p.id_anggota
    GROUP BY a.id_anggota
    ORDER BY a.no_anggota ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Koperasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sembunyikan tombol cetak dan navbar saat di-print */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 no-print">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Koperasi Sejahtera Mandiri</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="anggota.php">Data Anggota</a>
            <a class="nav-link" href="simpanan.php">Transaksi Simpanan</a>
            <a class="nav-link" href="pinjaman.php">Pengajuan Pinjaman</a>
            <a class="nav-link" href="angsuran.php">Bayar Angsuran</a>
            <a class="nav-link active" href="laporan.php">Laporan</a>
        </div>
        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Laporan Rekapitulasi Simpanan & Pinjaman Anggota</h4>
        <button onclick="window.print()" class="btn btn-secondary no-print">Cetak Laporan (PDF / Print)</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No. Anggota</th>
                        <th>Nama Anggota</th>
                        <th>Total Saldo Simpanan</th>
                        <th>Akumulasi Pinjaman</th>
                        <th>Sisa Kewajiban Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Inisialisasi Variabel Invarian (Total Keseluruhan)
                    $grand_simpanan = 0;
                    $grand_hutang   = 0;
                    foreach ($laporan as $row): 
                        $grand_simpanan += $row['total_saldo_simpanan'];
                        $grand_hutang   += $row['sisa_hutang'];
                    ?>
                    <tr>
                        <td><?= $row['no_anggota'] ?></td>
                        <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
                        <td class="text-success"><?= format_rupiah($row['total_saldo_simpanan']) ?></td>
                        <td><?= format_rupiah($row['total_pinjaman']) ?></td>
                        <td class="text-danger"><?= format_rupiah($row['sisa_hutang']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">TOTAL KESELURUHAN:</td>
                        <td class="text-success"><?= format_rupiah($grand_simpanan) ?></td>
                        <td>-</td>
                        <td class="text-danger"><?= format_rupiah($grand_hutang) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</body>
</html>