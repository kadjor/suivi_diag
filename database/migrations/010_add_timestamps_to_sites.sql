-- Migration 010: Ajout timestamps à la table sites
-- Date: 2025-11-12
-- Description: Ajoute les colonnes created_at et updated_at à la table sites

-- Vérifier et ajouter created_at si elle n'existe pas
SET @exist_created := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'sites'
    AND COLUMN_NAME = 'created_at'
);

SET @sql_created := IF(@exist_created = 0,
    'ALTER TABLE `sites` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT "Column created_at already exists" AS message'
);

PREPARE stmt_created FROM @sql_created;
EXECUTE stmt_created;
DEALLOCATE PREPARE stmt_created;

-- Vérifier et ajouter updated_at si elle n'existe pas
SET @exist_updated := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'sites'
    AND COLUMN_NAME = 'updated_at'
);

SET @sql_updated := IF(@exist_updated = 0,
    'ALTER TABLE `sites` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'SELECT "Column updated_at already exists" AS message'
);

PREPARE stmt_updated FROM @sql_updated;
EXECUTE stmt_updated;
DEALLOCATE PREPARE stmt_updated;
