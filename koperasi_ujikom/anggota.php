<?php
require_once 'config/koneksi.php';
cek_login();

// Proses Tambah Anggota Baru (Aturan Bisnis #2)
if (isset($_POST['tambah'])) {
    // Generate Nomor Anggota Otomatis (Contoh: AGT-003)
    $last_id = $pdo->query("SELECT COALESCE(MAX(id_anggota), 0) + 1 FROM anggota")->fetchColumn();
    $no_anggota = "AGT-" . str_pad($last_id, 3, "0", STR_PAD_LEFT);
    
    $nama   = trim($_POST['nama_anggota']);
    $alamat = trim($_POST['alamat']);
    $no_hp  = trim($_POST['no_hp']);
    $tgl    = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO anggota (no_anggota, nama_anggota, alamat, no_hp, tgl_daftar) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$no_anggota, $nama, $alamat, $no_hp, $tgl]);
    
    header("Location: anggota.php?pesan=sukses");
    exit;
}

// Proses Hapus Anggota
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM anggota WHERE id_anggota = ?");
    $stmt->execute([$_GET['hapus']]);
    header("Location: anggota.php?pesan=hapus");
    exit;
}

// Ambil seluruh data anggota (Kompleksitas Waktu O(N))
$anggota_list = $pdo->query("SELECT * FROM anggota ORDER BY id_anggota DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Master Anggota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Koperasi Sejahtera Mandiri</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link active" href="anggota.php">Data Anggota</a>
            <a class="nav-link" href="simpanan.php">Transaksi Simpanan</a>
            <a class="nav-link" href="pinjaman.php">Pengajuan Pinjaman</a>
            <a class="nav-link" href="angsuran.php">Bayar Angsuran</a>
            <a class="nav-link" href="laporan.php">Laporan</a>
        </div>
        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <!-- Form Tambah Anggota -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Registrasi Anggota Baru</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_anggota" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">No. Handphone</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <button type="submit" name="tambah" class="btn btn-primary w-100">Simpan Anggota</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Anggota -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">Daftar Anggota & Saldo Simpanan</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No. Anggota</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Saldo Simpanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($anggota_list as $row): ?>
                            <tr>
                                <td><span class="badge bg-primary"><?= $row['no_anggota'] ?></span></td>
                                <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                <td class="fw-bold text-success"><?= format_rupiah($row['total_saldo_simpanan']) ?></td>
                                <td>
                                    <a href="?hapus=<?= $row['id_anggota'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus anggota ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>