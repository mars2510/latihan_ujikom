<?php
require_once 'config/koneksi.php';
cek_login();

// Mengambil data agregat untuk kartu statistik Dashboard
$total_anggota  = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
$total_simpanan = $pdo->query("SELECT COALESCE(SUM(total_saldo_simpanan), 0) FROM anggota")->fetchColumn();
$pinjaman_aktif = $pdo->query("SELECT COUNT(*) FROM pinjaman WHERE status_pinjaman = 'Berjalan'")->fetchColumn();
$sisa_piutang   = $pdo->query("SELECT COALESCE(SUM(sisa_tagihan), 0) FROM pinjaman WHERE status_pinjaman = 'Berjalan'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Koperasi Sejahtera Mandiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar Navigasi Utama -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Koperasi Sejahtera Mandiri</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link active" href="index.php">Dashboard</a>
            <a class="nav-link" href="anggota.php">Data Anggota</a>
            <a class="nav-link" href="simpanan.php">Transaksi Simpanan</a>
            <a class="nav-link" href="pinjaman.php">Pengajuan Pinjaman</a>
            <a class="nav-link" href="angsuran.php">Bayar Angsuran</a>
            <a class="nav-link" href="laporan.php">Laporan</a>
        </div>
        <span class="navbar-text text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama_petugas']) ?></span>
        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <h4 class="mb-4">Dashboard Ringkasan Operasional</h4>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card text-bg-info text-white shadow-sm">
                <div class="card-body">
                    <h6>Total Anggota Aktif</h6>
                    <h3><?= $total_anggota ?> Orang</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success shadow-sm">
                <div class="card-body">
                    <h6>Total Dana Simpanan</h6>
                    <h3><?= format_rupiah($total_simpanan) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning shadow-sm">
                <div class="card-body">
                    <h6>Pinjaman Berjalan</h6>
                    <h3><?= $pinjaman_aktif ?> Transaksi</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger shadow-sm">
                <div class="card-body">
                    <h6>Total Sisa Piutang</h6>
                    <h3><?= format_rupiah($sisa_piutang) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>