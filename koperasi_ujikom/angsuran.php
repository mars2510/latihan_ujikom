<?php
require_once 'config/koneksi.php';
cek_login();

$pesan_error = "";

if (isset($_POST['bayar'])) {
    $id_pinjaman  = $_POST['id_pinjaman'];
    $jumlah_bayar = $_POST['jumlah_bayar'];
    $id_user      = $_SESSION['id_user'];

    // Cek sisa tagihan dan hitung angsuran ke-berapa
    $stmt = $pdo->prepare("SELECT sisa_tagihan FROM pinjaman WHERE id_pinjaman = ?");
    $stmt->execute([$id_pinjaman]);
    $pinjaman = $stmt->fetch();

    if ($jumlah_bayar > $pinjaman['sisa_tagihan']) {
        $pesan_error = "Nominal bayar melebihi sisa tagihan (" . format_rupiah($pinjaman['sisa_tagihan']) . ")!";
    } else {
        try {
            // POIN 8: COMMIT & ROLLBACK TRANSACTION
            $pdo->beginTransaction();

            $angsuran_ke = $pdo->query("SELECT COALESCE(MAX(angsuran_ke), 0) + 1 FROM angsuran WHERE id_pinjaman = $id_pinjaman")->fetchColumn();

            $ins = $pdo->prepare("INSERT INTO angsuran (id_pinjaman, id_user, angsuran_ke, jumlah_bayar) VALUES (?, ?, ?, ?)");
            $ins->execute([$id_pinjaman, $id_user, $angsuran_ke, $jumlah_bayar]);

            // Trigger trg_after_insert_angsuran otomatis memotong sisa_tagihan & set status Lunas jika 0
            $pdo->commit();
            header("Location: angsuran.php?status=lunas_tercatat");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $pesan_error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

// Ambil pinjaman yang statusnya masih 'Berjalan'
$pinjaman_aktif = $pdo->query("
    SELECT p.*, a.nama_anggota 
    FROM pinjaman p 
    JOIN anggota a ON p.id_anggota = a.id_anggota 
    WHERE p.status_pinjaman = 'Berjalan'
")->fetchAll();

// Ambil riwayat angsuran
$riwayat_angsuran = $pdo->query("
    SELECT ang.*, p.no_pinjaman, a.nama_anggota 
    FROM angsuran ang
    JOIN pinjaman p ON ang.id_pinjaman = p.id_pinjaman
    JOIN anggota a ON p.id_anggota = a.id_anggota
    ORDER BY ang.id_angsuran DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Angsuran</title>
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
            <a class="nav-link" href="pinjaman.php">Pengajuan Pinjaman</a>
            <a class="nav-link active" href="angsuran.php">Bayar Angsuran</a>
            <a class="nav-link" href="laporan.php">Laporan</a>
        </div>
        <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <?php if ($pesan_error): ?>
        <div class="alert alert-danger"><?= $pesan_error ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Input Pembayaran Cicilan</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Pilih Pinjaman Aktif</label>
                            <select name="id_pinjaman" class="form-select" required>
                                <option value="">-- Pilih No. Pinjaman --</option>
                                <?php foreach ($pinjaman_aktif as $pa): ?>
                                    <option value="<?= $pa['id_pinjaman'] ?>">
                                        <?= $pa['no_pinjaman'] ?> - <?= $pa['nama_anggota'] ?> (Sisa: <?= format_rupiah($pa['sisa_tagihan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal Bayar (Rp)</label>
                            <input type="number" name="jumlah_bayar" class="form-control" min="1000" required>
                        </div>
                        <button type="submit" name="bayar" class="btn btn-primary w-100">Bayar Angsuran</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Riwayat Pembayaran Angsuran</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal Bayar</th>
                                <th>No. Pinjaman</th>
                                <th>Anggota</th>
                                <th>Angsuran Ke</th>
                                <th>Jumlah Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_angsuran as $ra): ?>
                            <tr>
                                <td><?= $ra['tgl_bayar'] ?></td>
                                <td><?= $ra['no_pinjaman'] ?></td>
                                <td><?= htmlspecialchars($ra['nama_anggota']) ?></td>
                                <td>Ke-<?= $ra['angsuran_ke'] ?></td>
                                <td class="fw-bold text-success"><?= format_rupiah($ra['jumlah_bayar']) ?></td>
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