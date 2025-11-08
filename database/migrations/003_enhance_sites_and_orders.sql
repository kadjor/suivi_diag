-- =================================================================
-- Migration 003: Amélioration tables sites et orders
-- Description: Ajout champs patrimoine et numéro de lot commande
-- =================================================================

-- Ajout des champs à la table sites pour gestion patrimoine
ALTER TABLE `sites`
    ADD COLUMN `numero_groupe` VARCHAR(50) NULL COMMENT 'Numéro de groupe (obligatoire pour import)' AFTER `reference_pch`,
    ADD COLUMN `numero_lot` VARCHAR(50) NULL COMMENT 'Numéro de lot (obligatoire pour import)' AFTER `numero_groupe`,
    ADD COLUMN `numero_porte` VARCHAR(20) NULL COMMENT 'Numéro de porte' AFTER `numero_lot`,
    ADD COLUMN `niveau` VARCHAR(20) NULL COMMENT 'Niveau/Étage' AFTER `numero_porte`,
    ADD COLUMN `numero_batiment` VARCHAR(50) NULL COMMENT 'Numéro de bâtiment' AFTER `niveau`,
    ADD COLUMN `numero_entree` VARCHAR(20) NULL COMMENT 'Numéro d\'entrée' AFTER `numero_batiment`,
    ADD COLUMN `identifiant_fiscal` VARCHAR(100) NULL COMMENT 'Identifiant fiscal' AFTER `numero_entree`,
    ADD COLUMN `numero_batiment_brgm` VARCHAR(100) NULL COMMENT 'Numéro bâtiment BRGM' AFTER `identifiant_fiscal`,
    ADD COLUMN `cadastre` VARCHAR(100) NULL COMMENT 'Référence cadastrale' AFTER `numero_batiment_brgm`,
    ADD COLUMN `nommage_rapport` VARCHAR(255) NULL COMMENT 'Nommage pour rapport' AFTER `cadastre`,
    ADD COLUMN `numero_gardien` VARCHAR(50) NULL COMMENT 'Numéro de gardien' AFTER `nommage_rapport`,
    ADD COLUMN `nom_groupe` VARCHAR(255) NULL COMMENT 'Nom du groupe' AFTER `numero_gardien`;

-- Ajout d'index pour les recherches fréquentes
ALTER TABLE `sites`
    ADD INDEX `idx_numero_groupe` (`numero_groupe`),
    ADD INDEX `idx_numero_lot` (`numero_lot`),
    ADD INDEX `idx_cadastre` (`cadastre`);

-- Ajout du numéro de lot dans la table orders
ALTER TABLE `orders`
    ADD COLUMN `numero_lot` VARCHAR(50) NULL COMMENT 'Numéro de lot pour la commande' AFTER `order_number`,
    ADD INDEX `idx_numero_lot` (`numero_lot`);

-- Création d'une table pour tracker les imports de patrimoine Excel
CREATE TABLE IF NOT EXISTS `site_imports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL COMMENT 'Client concerné par l\'import',
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `imported_by` INT UNSIGNED NOT NULL,
    `imported_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `rows_total` INT DEFAULT 0,
    `rows_processed` INT DEFAULT 0,
    `rows_success` INT DEFAULT 0,
    `rows_errors` INT DEFAULT 0,
    `log_json` JSON NULL COMMENT 'Détails erreurs/warnings ligne par ligne',
    `column_mapping` JSON NULL COMMENT 'Correspondance colonnes Excel <-> champs DB',
    `completed_at` TIMESTAMP NULL,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_imported_by` (`imported_by`),
    INDEX `idx_imported_at` (`imported_at`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`imported_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
