<?php
require_once 'config/koneksi.php';
cek_login();

if (isset($_POST['simpan'])) {
    $id_anggota     = $_POST['id_anggota'];
    $jenis_simpanan = $_POST['jenis_simpanan'];
    $nominal        = $_POST['nominal'];
    $id_user        = $_SESSION['id_user'];

    // Menerapkan Transaction (Commit & Rollback - Poin 8)
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_anggota, $id_user, $jenis_simpanan, $nominal]);
        
        // Trigger database otomatis memperbarui kolom total_saldo_simpanan di tabel anggota
        $pdo->commit();
        header("Location: simpanan.php?status=berhasil");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Transaksi Gagal: " . $e->getMessage());
    }
}

$anggota = $pdo->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota")->fetchAll();
$riwayat = $pdo->query("
    SELECT s.*, a.no_anggota, a.nama_anggota, u.nama_petugas 
    FROM simpanan s
    JOIN anggota a ON s.id_anggota = a.id_anggota
    JOIN users u ON s.id_user = u.id_user
    ORDER BY s.id_simpanan DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Simpanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Koperasi Sejahtera Mandiri</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="anggota.php">Data Anggota</a>
            <a class="nav-link active" href="simpanan.php">Transaksi Simpanan</a>
            <a class="nav-link" href="pinjaman.php">Pengajuan Pinjaman</a>
            <a class="nav-link" href="angsuran.php">Bayar Angsuran</a>
            <a class="nav-link" href="laporan.php">Laporan</a>
        </div>
        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Input Setoran Simpanan</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Pilih Anggota</label>
                            <select name="id_anggota" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                <?php foreach ($anggota as $a): ?>
                                    <option value="<?= $a['id_anggota'] ?>"><?= $a['no_anggota'] ?> - <?= $a['nama_anggota'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Jenis Simpanan</label>
                            <select name="jenis_simpanan" class="form-select" required>
                                <option value="Pokok">Simpanan Pokok</option>
                                <option value="Wajib">Simpanan Wajib</option>
                                <option value="Sukarela">Simpanan Sukarela</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal Setoran (Rp)</label>
                            <input type="number" name="nominal" class="form-control" min="1000" required>
                        </div>
                        <button type="submit" name="simpan" class="btn btn-success w-100">Simpan Setoran</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Riwayat Setoran Simpanan</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Anggota</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat as $r): ?>
                            <tr>
                                <td><?= $r['tgl_transaksi'] ?></td>
                                <td><?= $r['no_anggota'] ?> - <?= htmlspecialchars($r['nama_anggota']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= $r['jenis_simpanan'] ?></span></td>
                                <td class="fw-bold"><?= format_rupiah($r['nominal']) ?></td>
                                <td><?= htmlspecialchars($r['nama_petugas']) ?></td>
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