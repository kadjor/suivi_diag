-- Migration: Création de la table settings (conforme au schema.sql)
-- Date: 2025-11-12
-- Description: Structure officielle avec categories et types de données

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(50) NOT NULL COMMENT 'general, email, map, security, etc.',
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `data_type` ENUM('string', 'int', 'boolean', 'json') DEFAULT 'string',
    `label` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `updated_by` INT UNSIGNED NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_category_key` (`category`, `key`),
    INDEX `idx_category` (`category`),
    FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des paramètres par défaut
INSERT INTO `settings` (`category`, `key`, `value`, `data_type`, `label`, `description`) VALUES
('general', 'app_name', 'D-Evidences', 'string', 'Nom de l\'application', 'Nom affiché dans l\'interface'),
('general', 'app_timezone', 'Europe/Paris', 'string', 'Fuseau horaire', 'Fuseau horaire de l\'application'),
('general', 'items_per_page', '20', 'int', 'Éléments par page', 'Nombre d\'éléments affichés par page dans les listes'),
('email', 'smtp_host', 'localhost', 'string', 'Serveur SMTP', 'Adresse du serveur SMTP'),
('email', 'smtp_port', '587', 'int', 'Port SMTP', 'Port du serveur SMTP'),
('email', 'from_email', 'noreply@d-evidences.fr', 'string', 'Email expéditeur', 'Adresse email utilisée pour l\'envoi'),
('email', 'from_name', 'D-Evidences', 'string', 'Nom expéditeur', 'Nom affiché comme expéditeur')
ON DUPLICATE KEY UPDATE `key` = `key`;
