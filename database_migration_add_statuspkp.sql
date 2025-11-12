-- Migration: Add statuspkp column to headerpenjualan table
-- Date: 2025-01-XX
-- Description: Menambahkan kolom statuspkp dengan enumerasi ('pkp', 'nonpkp') pada tabel headerpenjualan

-- Add statuspkp column to headerpenjualan table
ALTER TABLE `headerpenjualan` 
ADD COLUMN `statuspkp` ENUM('pkp', 'nonpkp') NULL DEFAULT NULL 
AFTER `tanggalpenjualan`;

-- Optional: Add index if needed for filtering
-- CREATE INDEX `idx_statuspkp` ON `headerpenjualan` (`statuspkp`);

-- Optional: Update existing records with default value (if needed)
-- UPDATE `headerpenjualan` SET `statuspkp` = 'nonpkp' WHERE `statuspkp` IS NULL;

