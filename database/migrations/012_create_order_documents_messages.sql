-- Migration 012: Création des tables pour documents et messages des commandes
-- Date: 2025-11-13
-- Description: Permet d'ajouter des documents (plans, PDFs) et des messages avec notifications

-- Table des documents liés aux commandes
CREATE TABLE IF NOT EXISTS `order_documents` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL COMMENT 'Référence à la commande',
  `file_name` VARCHAR(255) NOT NULL COMMENT 'Nom du fichier',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Chemin du fichier',
  `file_type` VARCHAR(100) DEFAULT NULL COMMENT 'Type MIME',
  `file_size` INT(11) DEFAULT NULL COMMENT 'Taille en octets',
  `document_type` ENUM('plan', 'rapport', 'photo', 'bon_commande', 'autre') DEFAULT 'autre' COMMENT 'Type de document',
  `description` TEXT DEFAULT NULL COMMENT 'Description du document',
  `uploaded_by` INT(10) UNSIGNED NOT NULL COMMENT 'Utilisateur qui a uploadé',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_document_type` (`document_type`),
  CONSTRAINT `order_documents_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages/discussions sur les commandes
CREATE TABLE IF NOT EXISTS `order_messages` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL COMMENT 'Référence à la commande',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT 'Auteur du message',
  `message` TEXT NOT NULL COMMENT 'Contenu du message',
  `is_internal` TINYINT(1) DEFAULT 0 COMMENT 'Message interne (non visible par le client)',
  `notified_users` TEXT DEFAULT NULL COMMENT 'IDs des utilisateurs notifiés (JSON)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `order_messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des notifications pour les messages
CREATE TABLE IF NOT EXISTS `message_notifications` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` INT(10) UNSIGNED NOT NULL COMMENT 'Référence au message',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT 'Utilisateur à notifier',
  `read_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date de lecture',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_notification` (`message_id`, `user_id`),
  KEY `idx_user_unread` (`user_id`, `read_at`),
  CONSTRAINT `message_notifications_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `order_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
