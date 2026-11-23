-- ============================================================================
-- POIN 1 & 2: MEMPERSIAPKAN & MENGGUNAKAN PERANGKAT LUNAK SQL (DDL)
-- ============================================================================
DROP DATABASE IF EXISTS db_koperasi;
CREATE DATABASE db_koperasi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_koperasi;

-- 1. Tabel Users (Petugas Kasir / Admin) - Aturan Bisnis #1
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Disimpan dalam bentuk hash MD5/Bcrypt
    nama_petugas VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir'
) ENGINE=InnoDB;

-- 2. Tabel Anggota - Aturan Bisnis #2
CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    no_anggota VARCHAR(20) NOT NULL UNIQUE, -- Contoh: AGT-001
    nama_anggota VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(15),
    tgl_daftar DATE NOT NULL,
    total_saldo_simpanan DECIMAL(15, 2) DEFAULT 0.00, -- Diupdate otomatis oleh Trigger
    INDEX idx_no_anggota (no_anggota) -- Optimasi skalabilitas pencarian
) ENGINE=InnoDB;

-- 3. Tabel Simpanan - Aturan Bisnis #3
CREATE TABLE simpanan (
    id_simpanan INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_user INT NOT NULL,
    jenis_simpanan ENUM('Pokok', 'Wajib', 'Sukarela') NOT NULL,
    nominal DECIMAL(15, 2) NOT NULL,
    tgl_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- POIN 4: OPERASI RELASIONAL (Foreign Key)
    CONSTRAINT fk_simpanan_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    CONSTRAINT fk_simpanan_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- 4. Tabel Pinjaman - Aturan Bisnis #4
CREATE TABLE pinjaman (
    id_pinjaman INT AUTO_INCREMENT PRIMARY KEY,
    no_pinjaman VARCHAR(20) NOT NULL UNIQUE, -- Contoh: PJM-001
    id_anggota INT NOT NULL,
    id_user INT NOT NULL,
    pokok_pinjaman DECIMAL(15, 2) NOT NULL,
    bunga_persen DECIMAL(5, 2) NOT NULL, -- Bunga tetap per bulan (%)
    tenor_bulan INT NOT NULL,
    total_tagihan DECIMAL(15, 2) NOT NULL, -- Pokok + Total Bunga
    sisa_tagihan DECIMAL(15, 2) NOT NULL,  -- Berkurang otomatis saat angsuran dibayar
    tgl_pengajuan DATE NOT NULL,
    status_pinjaman ENUM('Berjalan', 'Lunas') DEFAULT 'Berjalan',
    CONSTRAINT fk_pinjaman_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    CONSTRAINT fk_pinjaman_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- 5. Tabel Angsuran - Aturan Bisnis #5
CREATE TABLE angsuran (
    id_angsuran INT AUTO_INCREMENT PRIMARY KEY,
    id_pinjaman INT NOT NULL,
    id_user INT NOT NULL,
    angsuran_ke INT NOT NULL,
    jumlah_bayar DECIMAL(15, 2) NOT NULL,
    tgl_bayar DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_angsuran_pinjaman FOREIGN KEY (id_pinjaman) REFERENCES pinjaman(id_pinjaman) ON DELETE CASCADE,
    CONSTRAINT fk_angsuran_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- ============================================================================
-- POIN 3: MENGISI TABEL (DATA DUMMY AWAL - DML)
-- ============================================================================
-- Password default untuk kedua akun di bawah adalah: admin123
INSERT INTO users (username, password, nama_petugas, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso (Admin)', 'admin'),
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah (Kasir)', 'kasir');

INSERT INTO anggota (no_anggota, nama_anggota, alamat, no_hp, tgl_daftar, total_saldo_simpanan) VALUES
('AGT-001', 'Andi Wijaya', 'Jl. Merdeka No. 10, Jakarta', '081234567890', '2026-01-10', 0.00),
('AGT-002', 'Rina Kusuma', 'Jl. Melati No. 5, Tangerang', '081298765432', '2026-02-15', 0.00);

-- ============================================================================
-- POIN 6: MEMBUAT FUNCTION SQL
-- Fungsi menghitung total pinjaman keseluruhan (Pokok + Bunga Flat)
-- ============================================================================
DELIMITER //
CREATE FUNCTION fn_hitung_total_tagihan(
    p_pokok DECIMAL(15,2), 
    p_bunga_persen DECIMAL(5,2), 
    p_tenor INT
) RETURNS DECIMAL(15,2)
DETERMINISTIC
BEGIN
    DECLARE v_total_bunga DECIMAL(15,2);
    DECLARE v_total_tagihan DECIMAL(15,2);
    
    -- Rumus Bunga Flat: Pokok * (Bunga/100) * Tenor
    SET v_total_bunga = p_pokok * (p_bunga_persen / 100) * p_tenor;
    SET v_total_tagihan = p_pokok + v_total_bunga;
    
    RETURN v_total_tagihan;
END //
DELIMITER ;

-- ============================================================================
-- POIN 5: MEMBUAT STORED PROCEDURE SQL
-- Prosedur untuk mengajukan pinjaman baru dengan memanggil Function di atas
-- ============================================================================
DELIMITER //
CREATE PROCEDURE sp_ajukan_pinjaman(
    IN p_no_pinjaman VARCHAR(20),
    IN p_id_anggota INT,
    IN p_id_user INT,
    IN p_pokok DECIMAL(15,2),
    IN p_bunga DECIMAL(5,2),
    IN p_tenor INT,
    IN p_tgl DATE
)
BEGIN
    DECLARE v_tagihan DECIMAL(15,2);
    
    -- Memanggil Function untuk menghitung total tagihan
    SET v_tagihan = fn_hitung_total_tagihan(p_pokok, p_bunga, p_tenor);
    
    INSERT INTO pinjaman (
        no_pinjaman, id_anggota, id_user, pokok_pinjaman, 
        bunga_persen, tenor_bulan, total_tagihan, sisa_tagihan, tgl_pengajuan, status_pinjaman
    ) VALUES (
        p_no_pinjaman, p_id_anggota, p_id_user, p_pokok, 
        p_bunga, p_tenor, v_tagihan, v_tagihan, p_tgl, 'Berjalan'
    );
END //
DELIMITER ;

-- ============================================================================
-- POIN 7: MEMBUAT TRIGGER SQL (INTI ATURAN BISNIS #3 & #5)
-- ============================================================================
DELIMITER //

-- Trigger 1: Otomatis menambah total_saldo_simpanan di tabel anggota saat ada setoran
CREATE TRIGGER trg_after_insert_simpanan
AFTER INSERT ON simpanan
FOR EACH ROW
BEGIN
    UPDATE anggota 
    SET total_saldo_simpanan = total_saldo_simpanan + NEW.nominal
    WHERE id_anggota = NEW.id_anggota;
END //

-- Trigger 2: Otomatis mengurangi sisa_tagihan & ubah status 'Lunas' jika sisa <= 0
CREATE TRIGGER trg_after_insert_angsuran
AFTER INSERT ON angsuran
FOR EACH ROW
BEGIN
    DECLARE v_sisa_baru DECIMAL(15,2);
    
    -- Kurangi sisa tagihan
    UPDATE pinjaman 
    SET sisa_tagihan = sisa_tagihan - NEW.jumlah_bayar
    WHERE id_pinjaman = NEW.id_pinjaman;
    
    -- Cek sisa tagihan terbaru
    SELECT sisa_tagihan INTO v_sisa_baru 
    FROM pinjaman WHERE id_pinjaman = NEW.id_pinjaman;
    
    -- Jika sudah lunas (<= 0), ubah status menjadi 'Lunas' dan pastikan sisa tidak minus
    IF v_sisa_baru <= 0 THEN
        UPDATE pinjaman 
        SET status_pinjaman = 'Lunas', sisa_tagihan = 0 
        WHERE id_pinjaman = NEW.id_pinjaman;
    END IF;
END //

DELIMITER ;

-- ============================================================================
-- POIN 8 & 9: CONTOH PENGUJIAN TRANSACTION (COMMIT & ROLLBACK) DI SQL
-- ============================================================================
START TRANSACTION;
INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal) VALUES (1, 1, 'Pokok', 500000);
-- Jika valid, permanenkan perubahan (Trigger otomatis berjalan):
COMMIT;

-- Contoh pengujian Rollback (batal simpan):
START TRANSACTION;
INSERT INTO simpanan (id_anggota, id_user, jenis_simpanan, nominal) VALUES (1, 1, 'Sukarela', 100000);
ROLLBACK; -- Data 100.000 dibatalkan dan saldo anggota tidak bertambah