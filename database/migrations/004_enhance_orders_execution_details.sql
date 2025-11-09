-- =================================================================
-- Migration 004: Ajout détails d'exécution dans orders
-- Description: Ajout adresse, ville, diagnostics pour commandes
-- =================================================================

-- Ajout des champs d'exécution à la table orders
ALTER TABLE `orders`
    ADD COLUMN `execution_address` VARCHAR(255) NULL COMMENT 'Adresse d\'exécution de la commande' AFTER `numero_lot`,
    ADD COLUMN `execution_city` VARCHAR(100) NULL COMMENT 'Ville d\'exécution' AFTER `execution_address`,
    ADD COLUMN `execution_postal_code` VARCHAR(10) NULL COMMENT 'Code postal' AFTER `execution_city`,
    ADD COLUMN `execution_numero_porte` VARCHAR(20) NULL COMMENT 'Numéro de porte' AFTER `execution_postal_code`,
    ADD COLUMN `execution_niveau` VARCHAR(20) NULL COMMENT 'Niveau/Étage' AFTER `execution_numero_porte`,
    ADD COLUMN `site_id` INT UNSIGNED NULL COMMENT 'Référence au site du patrimoine (si applicable)' AFTER `client_id`,
    ADD COLUMN `bon_de_commande_pdf` VARCHAR(500) NULL COMMENT 'Chemin PDF bon de commande uploadé' AFTER `execution_niveau`;

-- Ajout d'index pour les recherches
ALTER TABLE `orders`
    ADD INDEX `idx_site_id` (`site_id`),
    ADD INDEX `idx_execution_city` (`execution_city`),
    ADD INDEX `idx_execution_postal_code` (`execution_postal_code`);

-- Ajout clé étrangère vers sites (optionnelle - permet NULL)
ALTER TABLE `orders`
    ADD CONSTRAINT `fk_orders_site`
    FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Table de liaison orders <-> diagnostics demandés
CREATE TABLE IF NOT EXISTS `order_diagnostics` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `diagnostic_type_id` INT UNSIGNED NOT NULL,
    `notes` TEXT NULL COMMENT 'Notes spécifiques pour ce diagnostic',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_diagnostic_type` (`diagnostic_type_id`),
    UNIQUE KEY `unique_order_diagnostic` (`order_id`, `diagnostic_type_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Diagnostics demandés pour chaque commande';
