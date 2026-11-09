-- =====================================================================
--  SI-APOTEK — Data Awal (Seed)
--  Database : db_si_apotek
--  CATATAN : Seluruh data di bawah adalah DATA CONTOH untuk development.
--            Ganti dengan data riil sebelum dipakai produksi.
--  Password default (ganti setelah login pertama):
--    admin     : admin123      (role: super_admin)
--    pemilik   : pemilik123    (role: pemilik)
--    apoteker  : apoteker123   (role: apoteker, apotek #1)
--    kasir     : kasir123      (role: kasir, apotek #1)
-- =====================================================================

USE `db_si_apotek`;

-- ---------------------------------------------------------------------
-- Pemilik apotek
-- ---------------------------------------------------------------------
INSERT INTO `tbl_pemilik_apotek` (`nama`, `alamat`, `no_telp`, `status`) VALUES
('Budi Santoso', 'Jl. Melati No. 12, Jakarta Utara', '0812-3456-7890', 'aktif');

-- ---------------------------------------------------------------------
-- Apotek
-- ---------------------------------------------------------------------
INSERT INTO `tbl_apotek` (`nama_apotek`, `alamat`, `no_telp`, `id_pemilik`, `status`) VALUES
('Apotek Qilah Sehat', 'Jl. Sungai Bambu No.7, Tanjung Priok, Jakarta Utara', '021-4301234', 1, 'aktif');

-- ---------------------------------------------------------------------
-- Apoteker
-- ---------------------------------------------------------------------
INSERT INTO `tbl_apoteker` (`nama_apoteker`, `no_sipa`, `no_telp`, `id_apotek`, `status`) VALUES
('Siti Rahayu, S.Farm',   'SIPA/31.72/2024/0001', '0813-1111-2222', 1, 'aktif'),
('Dwi Lestari, S.Farm',   'SIPA/31.72/2024/0002', '0813-3333-4444', 1, 'aktif');

-- ---------------------------------------------------------------------
-- Kasir
-- ---------------------------------------------------------------------
INSERT INTO `tbl_kasir` (`nama_kasir`, `shift`, `no_telp`, `id_apotek`, `status`) VALUES
('Andi Saputra', 'Pagi', '0812-5555-6666', 1, 'aktif'),
('Rina Marlina', 'Sore', '0812-7777-8888', 1, 'aktif');

-- ---------------------------------------------------------------------
-- Pelanggan
-- ---------------------------------------------------------------------
INSERT INTO `tbl_pelanggan` (`nama_pelanggan`, `alamat`, `no_telp`, `tanggal_daftar`, `status`) VALUES
('Hendra Wijaya',   'Jl. Sungai Bambu No. 3, Tanjung Priok', '0812-9000-1000', '2026-10-01', 'aktif'),
('Maya Anggraini',  'Jl. Yos Sudarso No. 45, Jakarta Utara', '0812-9000-2000', '2026-10-15', 'aktif'),
('Rudi Hartono',    'Jl. Enggano No. 8, Tanjung Priok',      '0812-9000-3000', '2026-11-01', 'aktif');

-- ---------------------------------------------------------------------
-- Obat (stok per cabang apotek #1)
--   Variasi sengaja dibuat: ada yang stok menipis, mendekati
--   kedaluwarsa, sudah kedaluwarsa, dan habis — untuk menguji
--   fitur peringatan.
-- ---------------------------------------------------------------------
INSERT INTO `tbl_obat` (`nama_obat`, `kategori`, `dosis`, `harga`, `stok`, `stok_minimum`, `expired_date`, `id_apotek`, `status`) VALUES
('Paracetamol 500mg',       'Analgesik',        '500 mg',        3500.00,  50, 10, '2027-06-30', 1, 'aktif'),
('Amoxicillin 500mg',       'Antibiotik',       '500 mg',       15000.00,   8, 10, '2026-12-15', 1, 'aktif'),
('Vitamin C 500mg',         'Vitamin',          '500 mg',        5000.00, 120, 10, '2028-01-01', 1, 'aktif'),
('Ibuprofen 400mg',         'Analgesik',        '400 mg',        8000.00,   5, 10, '2027-03-01', 1, 'aktif'),
('Cetirizine 10mg',         'Antihistamin',     '10 mg',         6000.00,  40, 10, '2026-11-30', 1, 'aktif'),
('Omeprazole 20mg',         'Lambung',          '20 mg',        12000.00,  60, 10, '2027-08-15', 1, 'aktif'),
('OBH Combi Sirup',         'Batuk',            '100 ml',       18000.00,  25, 10, '2026-10-01', 1, 'aktif'),
('Betadine Antiseptik',     'Antiseptik',       '60 ml',        25000.00,  15, 10, '2027-05-20', 1, 'aktif'),
('Dexamethasone 0.5mg',     'Kortikosteroid',   '0.5 mg',        3000.00,   0, 10, '2027-01-10', 1, 'aktif'),
('Promag Tablet',           'Lambung',          '12 tablet',     9000.00,  80, 10, '2027-11-01', 1, 'aktif');

-- ---------------------------------------------------------------------
-- Resep (contoh)
-- ---------------------------------------------------------------------
INSERT INTO `tbl_resep` (`no_resep`, `nama_dokter`, `nama_pasien`, `tanggal_resep`, `deskripsi`, `id_pelanggan`, `id_apoteker`) VALUES
('RSP-2026-0001', 'dr. Indah Permata', 'Hendra Wijaya',  '2026-11-08', 'Amoxicillin 3x1 selama 5 hari; Paracetamol 3x1 bila demam.', 1, 1),
('RSP-2026-0002', 'dr. Bagus Santoso', 'Maya Anggraini', '2026-11-09', 'Omeprazole 1x1 sebelum makan selama 14 hari.', 2, 2);

-- ---------------------------------------------------------------------
-- Pengguna (password sudah di-hash dengan bcrypt) — paling akhir karena
-- tbl_user.id_apotek merujuk tbl_apotek.
-- ---------------------------------------------------------------------
INSERT INTO `tbl_user` (`username`, `password`, `nama_lengkap`, `role`, `id_apotek`, `id_kasir`, `id_apoteker`, `status`) VALUES
('admin',    '$2y$12$UsihhxrpACQ1boSSaLzDp.QTz8aCf12BpMWEqrN/prKEEQUV8Cymu', 'Administrator',      'super_admin', NULL, NULL, NULL, 'aktif'),
('pemilik',  '$2y$12$cFsdVZpoBwxjKxAwKSsA9OOpI0JMyd5y3iEkDz1h/QrsWahFeRtjS', 'Budi Santoso',       'pemilik',     NULL, NULL, NULL, 'aktif'),
('apoteker', '$2y$12$lrcb8VwMLpOCq6GRIfrB5eIifppiTGkJFC.QhRc05Pri2BeFxhPiy', 'Siti Rahayu, S.Farm','apoteker',    1,    NULL, 1,    'aktif'),
('kasir',    '$2y$12$S8cqAwA28kTUAm2vPX9adenpRrewHCYWbch0HlpQ.1h.K6/EhelwK', 'Andi Saputra',       'kasir',       1,    1,    NULL, 'aktif');
