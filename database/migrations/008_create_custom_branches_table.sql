-- =================================================================
-- Migration 008: Table pour les branches personnalisées
-- Description: Permet de sauvegarder des branches personnalisées pour le déploiement
-- Version: 1.4.1
-- Date: 2025-11-11
-- =================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- TABLE: custom_branches
-- Description: Branches personnalisées pour le déploiement
-- =================================================================
CREATE TABLE IF NOT EXISTS `custom_branches` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `branch_name` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nom de la branche',
    `description` TEXT NULL COMMENT 'Description de la branche',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_branch_name` (`branch_name`),
    INDEX `idx_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =================================================================
-- Insertion dans la table migrations
-- =================================================================
INSERT INTO `migrations` (`version`, `filename`, `executed_at`)
VALUES (8, '008_create_custom_branches_table.sql', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `executed_at` = CURRENT_TIMESTAMP;
