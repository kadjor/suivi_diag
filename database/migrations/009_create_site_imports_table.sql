-- Migration 009: Création de la table site_imports (conforme schema.sql officiel)
-- Date: 2025-11-12
-- Description: Table pour l'historique des imports de sites depuis Excel

CREATE TABLE IF NOT EXISTS `site_imports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL COMMENT 'Client concerné par l\'import',
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `imported_by` INT UNSIGNED NOT NULL,
    `imported_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `rows_total` INT DEFAULT 0,
    `rows_processed` INT DEFAULT 0,
    `rows_success` INT DEFAULT 0,
    `rows_errors` INT DEFAULT 0,
    `log_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Détails erreurs/warnings ligne par ligne' CHECK (json_valid(`log_json`)),
    `column_mapping` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Correspondance colonnes Excel <-> champs DB' CHECK (json_valid(`column_mapping`)),
    `completed_at` TIMESTAMP NULL DEFAULT NULL,

    -- Index
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_imported_by` (`imported_by`),
    INDEX `idx_imported_at` (`imported_at`),

    -- Clés étrangères
    CONSTRAINT `site_imports_ibfk_1`
        FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
        ON DELETE CASCADE,

    CONSTRAINT `site_imports_ibfk_2`
        FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
