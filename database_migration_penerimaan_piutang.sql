-- Migration: Create Penerimaan Piutang Module
-- Date: 2025-01-XX
-- Description: Membuat tabel headerpenerimaan dan detailpenerimaan untuk modul Penerimaan Piutang

-- Create headerpenerimaan table
CREATE TABLE IF NOT EXISTS `headerpenerimaan` (
  `nopenerimaan` VARCHAR(15) NOT NULL PRIMARY KEY,
  `tanggalpenerimaan` DATE NOT NULL,
  `statuspkp` ENUM('pkp', 'nonpkp') NULL DEFAULT NULL,
  `jenispenerimaan` ENUM('tunai', 'transfer', 'giro') NOT NULL,
  `kodesales` VARCHAR(10) NULL DEFAULT NULL,
  `kodecustomer` VARCHAR(10) NULL DEFAULT NULL,
  `totalpiutang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `totalpotongan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `totallainlain` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `totalnetto` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('belumproses', 'proses') NOT NULL DEFAULT 'belumproses',
  `noinkaso` VARCHAR(15) NULL DEFAULT NULL,
  `userid` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tanggalpenerimaan` (`tanggalpenerimaan`),
  INDEX `idx_status` (`status`),
  INDEX `idx_kodecustomer` (`kodecustomer`),
  INDEX `idx_kodesales` (`kodesales`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create detailpenerimaan table
CREATE TABLE IF NOT EXISTS `detailpenerimaan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nopenerimaan` VARCHAR(15) NOT NULL,
  `nopenjualan` VARCHAR(15) NOT NULL,
  `nogiro` VARCHAR(15) NULL DEFAULT NULL,
  `tanggalcair` DATE NULL DEFAULT NULL,
  `piutang` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `potongan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `lainlain` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `netto` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `nourut` INT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_nopenerimaan` (`nopenerimaan`),
  INDEX `idx_nopenjualan` (`nopenjualan`),
  FOREIGN KEY (`nopenerimaan`) REFERENCES `headerpenerimaan`(`nopenerimaan`) ON DELETE CASCADE,
  FOREIGN KEY (`nopenjualan`) REFERENCES `headerpenjualan`(`nopenjualan`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

