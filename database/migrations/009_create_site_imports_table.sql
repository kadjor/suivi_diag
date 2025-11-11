-- Migration 009: Création de la table site_imports
-- Date: 2025-11-11
-- Description: Table pour l'historique des imports de sites depuis Excel

CREATE TABLE IF NOT EXISTS `site_imports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `filepath` TEXT NULL,
    `imported_by` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `rows_total` INT UNSIGNED DEFAULT 0,
    `rows_processed` INT UNSIGNED DEFAULT 0,
    `rows_success` INT UNSIGNED DEFAULT 0,
    `rows_errors` INT UNSIGNED DEFAULT 0,
    `column_mapping` JSON NULL COMMENT 'Mappage des colonnes Excel vers les champs de la base',
    `log_json` JSON NULL COMMENT 'Logs détaillés de l\'import (erreurs, avertissements)',
    `imported_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Index
    INDEX `idx_client_id` (`client_id`),
    INDEX `idx_imported_by` (`imported_by`),
    INDEX `idx_status` (`status`),
    INDEX `idx_imported_at` (`imported_at`),

    -- Clés étrangères
    CONSTRAINT `fk_site_imports_client`
        FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `fk_site_imports_user`
        FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historique des imports de sites depuis fichiers Excel';
