<?php
require_once 'config/koneksi.php';
cek_login();

if (isset($_POST['ajukan'])) {
    $last_id     = $pdo->query("SELECT COALESCE(MAX(id_pinjaman), 0) + 1 FROM pinjaman")->fetchColumn();
    $no_pinjaman = "PJM-" . str_pad($last_id, 3, "0", STR_PAD_LEFT);
    
    $id_anggota = $_POST['id_anggota'];
    $id_user    = $_SESSION['id_user'];
    $pokok      = $_POST['pokok_pinjaman'];
    $bunga      = $_POST['bunga_persen'];
    $tenor      = $_POST['tenor_bulan'];
    $tgl        = date('Y-m-d');

    // Memanggil Stored Procedure SQL (Poin 5 & 10)
    $stmt = $pdo->prepare("CALL sp_ajukan_pinjaman(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$no_pinjaman, $id_anggota, $id_user, $pokok, $bunga, $tenor, $tgl]);

    header("Location: pinjaman.php?status=sukses");
    exit;
}

$anggota = $pdo->query("SELECT id_anggota, no_anggota, nama_anggota FROM anggota")->fetchAll();
$pinjaman_list = $pdo->query("
    SELECT p.*, a.no_anggota, a.nama_anggota 
    FROM pinjaman p
    JOIN anggota a ON p.id_anggota = a.id_anggota
    ORDER BY p.id_pinjaman DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Pinjaman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Koperasi Sejahtera Mandiri</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="anggota.php">Data Anggota</a>
            <a class="nav-link" href="simpanan.php">Transaksi Simpanan</a>
            <a class="nav-link active" href="pinjaman.php">Pengajuan Pinjaman</a>
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
                <div class="card-header bg-warning">Form Pengajuan Pinjaman</div>
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
                            <label class="form-label">Pokok Pinjaman (Rp)</label>
                            <input type="number" name="pokok_pinjaman" class="form-control" min="100000" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Bunga Tetap per Bulan (%)</label>
                            <input type="number" step="0.1" name="bunga_persen" class="form-control" value="1.5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenor (Bulan)</label>
                            <input type="number" name="tenor_bulan" class="form-control" min="1" max="60" value="12" required>
                        </div>
                        <button type="submit" name="ajukan" class="btn btn-warning w-100 fw-bold">Proses Pinjaman</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Daftar Pinjaman Anggota</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No. Pinjaman</th>
                                <th>Anggota</th>
                                <th>Pokok</th>
                                <th>Total Tagihan</th>
                                <th>Sisa Tagihan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pinjaman_list as $p): ?>
                            <tr>
                                <td><?= $p['no_pinjaman'] ?></td>
                                <td><?= htmlspecialchars($p['nama_anggota']) ?></td>
                                <td><?= format_rupiah($p['pokok_pinjaman']) ?></td>
                                <td><?= format_rupiah($p['total_tagihan']) ?></td>
                                <td class="fw-bold text-danger"><?= format_rupiah($p['sisa_tagihan']) ?></td>
                                <td>
                                    <span class="badge <?= $p['status_pinjaman'] == 'Lunas' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= $p['status_pinjaman'] ?>
                                    </span>
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