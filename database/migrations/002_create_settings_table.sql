-- Migration: Création de la table settings
-- Date: 2025-11-08

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des paramètres par défaut
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('app_name', 'D-Evidences', 'Nom de l\'application'),
('app_timezone', 'Europe/Paris', 'Fuseau horaire'),
('items_per_page', '20', 'Nombre d\'éléments par page')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
