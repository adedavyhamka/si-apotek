-- =====================================================================
--  SI-APOTEK — Skema Database
--  Sistem Informasi Manajemen Apotek (Apotek Qilah Sehat)
--  Database : db_si_apotek
--  Engine   : InnoDB | Charset : utf8mb4
--  Dibuat   : 2026-11-10
--
--  CATATAN DESAIN (deviasi dari ERD dasar, terdokumentasi):
--  1. Kolom `status` (ENUM aktif/nonaktif) ditambahkan pada tabel master
--     untuk mendukung fitur "nonaktifkan" tanpa menghapus histori.
--  2. `tbl_obat` mendapat kolom `id_apotek` (FK) agar stok dapat dikelola
--     per cabang apotek, sesuai kebutuhan multi-cabang.
--  3. `tbl_obat.stok_minimum` untuk fitur peringatan stok menipis.
--  4. `tbl_item_transaksi.harga_satuan` menyimpan harga saat transaksi,
--     agar laporan historis tidak berubah jika harga obat diubah.
--  5. `tbl_transaksi_penjualan.no_transaksi` nomor struk unik.
--  6. `tbl_resep.id_pelanggan` & `id_apoteker` boleh NULL (pasien walk-in
--     tetap bisa dicatat via `nama_pasien`).
--
--  TABEL PENDUKUNG (di luar 9 tabel ERD):
--  - tbl_user         : autentikasi & role-based access control.
--  - tbl_stok_histori : riwayat mutasi stok masuk/keluar.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `db_si_apotek`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_si_apotek`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- A. tbl_pemilik_apotek
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_pemilik_apotek`;
CREATE TABLE `tbl_pemilik_apotek` (
  `id_pemilik` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `alamat`     VARCHAR(200) DEFAULT NULL,
  `no_telp`    VARCHAR(20)  DEFAULT NULL,
  `status`     ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_pemilik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- B. tbl_apotek (1 pemilik -> N apotek)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_apotek`;
CREATE TABLE `tbl_apotek` (
  `id_apotek`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_apotek`  VARCHAR(100) NOT NULL,
  `alamat`       VARCHAR(200) DEFAULT NULL,
  `no_telp`      VARCHAR(20)  DEFAULT NULL,
  `id_pemilik`   INT UNSIGNED NOT NULL,
  `status`       ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_apotek`),
  KEY `idx_apotek_pemilik` (`id_pemilik`),
  CONSTRAINT `fk_apotek_pemilik` FOREIGN KEY (`id_pemilik`)
    REFERENCES `tbl_pemilik_apotek` (`id_pemilik`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- C. tbl_apoteker (1 apotek -> N apoteker)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_apoteker`;
CREATE TABLE `tbl_apoteker` (
  `id_apoteker`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_apoteker` VARCHAR(100) NOT NULL,
  `no_sipa`       VARCHAR(50)  DEFAULT NULL,
  `no_telp`       VARCHAR(20)  DEFAULT NULL,
  `id_apotek`     INT UNSIGNED NOT NULL,
  `status`        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_apoteker`),
  KEY `idx_apoteker_apotek` (`id_apotek`),
  CONSTRAINT `fk_apoteker_apotek` FOREIGN KEY (`id_apotek`)
    REFERENCES `tbl_apotek` (`id_apotek`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- D. tbl_kasir (1 apotek -> N kasir)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_kasir`;
CREATE TABLE `tbl_kasir` (
  `id_kasir`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kasir` VARCHAR(100) NOT NULL,
  `shift`      VARCHAR(20)  DEFAULT NULL,
  `no_telp`    VARCHAR(20)  DEFAULT NULL,
  `id_apotek`  INT UNSIGNED NOT NULL,
  `status`     ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_kasir`),
  KEY `idx_kasir_apotek` (`id_apotek`),
  CONSTRAINT `fk_kasir_apotek` FOREIGN KEY (`id_apotek`)
    REFERENCES `tbl_apotek` (`id_apotek`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- E. tbl_pelanggan (1 pelanggan -> N transaksi)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_pelanggan`;
CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` VARCHAR(100) NOT NULL,
  `alamat`         VARCHAR(200) DEFAULT NULL,
  `no_telp`        VARCHAR(20)  DEFAULT NULL,
  `tanggal_daftar` DATE         DEFAULT NULL,
  `status`         ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_pelanggan`),
  KEY `idx_pelanggan_nama` (`nama_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- F. tbl_obat (1 apotek -> N obat; stok per cabang)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_obat`;
CREATE TABLE `tbl_obat` (
  `id_obat`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_obat`    VARCHAR(100) NOT NULL,
  `kategori`     VARCHAR(50)  DEFAULT NULL,
  `dosis`        VARCHAR(50)  DEFAULT NULL,
  `harga`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stok`         INT NOT NULL DEFAULT 0,
  `stok_minimum` INT NOT NULL DEFAULT 10,
  `expired_date` DATE         DEFAULT NULL,
  `id_apotek`    INT UNSIGNED NOT NULL,
  `status`       ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_obat`),
  KEY `idx_obat_apotek` (`id_apotek`),
  KEY `idx_obat_nama` (`nama_obat`),
  KEY `idx_obat_kategori` (`kategori`),
  CONSTRAINT `fk_obat_apotek` FOREIGN KEY (`id_apotek`)
    REFERENCES `tbl_apotek` (`id_apotek`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- I. tbl_resep (dibuat sebelum transaksi karena dirujuk transaksi)
--     Resep terhubung dengan pelanggan & apoteker.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_resep`;
CREATE TABLE `tbl_resep` (
  `id_resep`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_resep`     VARCHAR(50) NOT NULL,
  `nama_dokter`  VARCHAR(100) DEFAULT NULL,
  `nama_pasien`  VARCHAR(100) DEFAULT NULL,
  `tanggal_resep` DATE        DEFAULT NULL,
  `deskripsi`    TEXT,
  `id_pelanggan` INT UNSIGNED DEFAULT NULL,
  `id_apoteker`  INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_resep`),
  KEY `idx_resep_pelanggan` (`id_pelanggan`),
  KEY `idx_resep_apoteker` (`id_apoteker`),
  CONSTRAINT `fk_resep_pelanggan` FOREIGN KEY (`id_pelanggan`)
    REFERENCES `tbl_pelanggan` (`id_pelanggan`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_resep_apoteker` FOREIGN KEY (`id_apoteker`)
    REFERENCES `tbl_apoteker` (`id_apoteker`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- G. tbl_transaksi_penjualan
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_transaksi_penjualan`;
CREATE TABLE `tbl_transaksi_penjualan` (
  `id_transaksi`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_transaksi`      VARCHAR(30) NOT NULL,
  `tanggal`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `metode_pembayaran` VARCHAR(50) DEFAULT 'Tunai',
  `id_kasir`          INT UNSIGNED NOT NULL,
  `id_pelanggan`      INT UNSIGNED DEFAULT NULL,
  `id_resep`          INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `uq_no_transaksi` (`no_transaksi`),
  KEY `idx_trx_kasir` (`id_kasir`),
  KEY `idx_trx_pelanggan` (`id_pelanggan`),
  KEY `idx_trx_resep` (`id_resep`),
  KEY `idx_trx_tanggal` (`tanggal`),
  CONSTRAINT `fk_trx_kasir` FOREIGN KEY (`id_kasir`)
    REFERENCES `tbl_kasir` (`id_kasir`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_trx_pelanggan` FOREIGN KEY (`id_pelanggan`)
    REFERENCES `tbl_pelanggan` (`id_pelanggan`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_trx_resep` FOREIGN KEY (`id_resep`)
    REFERENCES `tbl_resep` (`id_resep`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- H. tbl_item_transaksi (1 transaksi -> N item)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_item_transaksi`;
CREATE TABLE `tbl_item_transaksi` (
  `id_item`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jumlah`       INT NOT NULL DEFAULT 1,
  `harga_satuan` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `id_transaksi` INT UNSIGNED NOT NULL,
  `id_obat`      INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_item_trx` (`id_transaksi`),
  KEY `idx_item_obat` (`id_obat`),
  CONSTRAINT `fk_item_trx` FOREIGN KEY (`id_transaksi`)
    REFERENCES `tbl_transaksi_penjualan` (`id_transaksi`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_obat` FOREIGN KEY (`id_obat`)
    REFERENCES `tbl_obat` (`id_obat`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABEL PENDUKUNG 1: tbl_user (autentikasi & RBAC)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE `tbl_user` (
  `id_user`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(50) NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) DEFAULT NULL,
  `role`         ENUM('super_admin','pemilik','apoteker','kasir') NOT NULL,
  `id_apotek`    INT UNSIGNED DEFAULT NULL,
  `id_kasir`     INT UNSIGNED DEFAULT NULL,
  `id_apoteker`  INT UNSIGNED DEFAULT NULL,
  `status`       ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_username` (`username`),
  KEY `idx_user_apotek` (`id_apotek`),
  KEY `idx_user_kasir` (`id_kasir`),
  KEY `idx_user_apoteker` (`id_apoteker`),
  CONSTRAINT `fk_user_apotek` FOREIGN KEY (`id_apotek`)
    REFERENCES `tbl_apotek` (`id_apotek`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_kasir` FOREIGN KEY (`id_kasir`)
    REFERENCES `tbl_kasir` (`id_kasir`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_apoteker` FOREIGN KEY (`id_apoteker`)
    REFERENCES `tbl_apoteker` (`id_apoteker`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TABEL PENDUKUNG 2: tbl_stok_histori (riwayat mutasi stok)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_stok_histori`;
CREATE TABLE `tbl_stok_histori` (
  `id_histori`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_obat`      INT UNSIGNED NOT NULL,
  `jenis`        ENUM('masuk','keluar') NOT NULL,
  `jumlah`       INT NOT NULL,
  `stok_sebelum` INT NOT NULL,
  `stok_sesudah` INT NOT NULL,
  `keterangan`   VARCHAR(255) DEFAULT NULL,
  `tanggal`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user`      INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_histori`),
  KEY `idx_histori_obat` (`id_obat`),
  KEY `idx_histori_tanggal` (`tanggal`),
  CONSTRAINT `fk_histori_obat` FOREIGN KEY (`id_obat`)
    REFERENCES `tbl_obat` (`id_obat`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_histori_user` FOREIGN KEY (`id_user`)
    REFERENCES `tbl_user` (`id_user`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
