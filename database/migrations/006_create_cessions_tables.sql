-- =================================================================
-- Migration 006: Système de Gestion des Cessions
-- Description: Tables pour gérer la cession de sites entre clients
-- Version: 1.4.0
-- Date: 2025-11-11
-- =================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- TABLE: cessions
-- Description: Table principale pour les cessions de sites entre clients
-- =================================================================
CREATE TABLE IF NOT EXISTS `cessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cession_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: CES-YYYYMMDD-XXXX',
    `from_client_id` INT UNSIGNED NOT NULL COMMENT 'Client cédant',
    `to_client_id` INT UNSIGNED NOT NULL COMMENT 'Client cessionnaire',
    `status` ENUM('draft', 'pending_validation', 'validated', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    `title` VARCHAR(255) NOT NULL COMMENT 'Titre de la cession',
    `description` TEXT NULL COMMENT 'Description détaillée',
    `effective_date` DATE NULL COMMENT 'Date effective du transfert',
    `completion_date` DATE NULL COMMENT 'Date de finalisation',
    `total_sites` INT DEFAULT 0 COMMENT 'Nombre total de sites à transférer',
    `transferred_sites` INT DEFAULT 0 COMMENT 'Nombre de sites déjà transférés',
    `metadata` JSON NULL COMMENT 'Données complémentaires',
    `created_by` INT UNSIGNED NOT NULL COMMENT 'Utilisateur créateur',
    `validated_by` INT UNSIGNED NULL COMMENT 'Utilisateur validateur',
    `validated_at` TIMESTAMP NULL COMMENT 'Date de validation',
    `cancelled_by` INT UNSIGNED NULL,
    `cancelled_at` TIMESTAMP NULL,
    `cancellation_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cession_number` (`cession_number`),
    INDEX `idx_from_client` (`from_client_id`),
    INDEX `idx_to_client` (`to_client_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_effective_date` (`effective_date`),
    INDEX `idx_created_by` (`created_by`),
    FOREIGN KEY (`from_client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`to_client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`validated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`cancelled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `chk_different_clients` CHECK (`from_client_id` != `to_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: cession_sites
-- Description: Sites concernés par une cession
-- =================================================================
CREATE TABLE IF NOT EXISTS `cession_sites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cession_id` INT UNSIGNED NOT NULL,
    `site_id` INT UNSIGNED NOT NULL,
    `transfer_status` ENUM('pending', 'transferred', 'failed') DEFAULT 'pending',
    `transfer_date` TIMESTAMP NULL COMMENT 'Date effective du transfert',
    `transfer_notes` TEXT NULL COMMENT 'Notes sur le transfert',
    `diagnostics_count` INT DEFAULT 0 COMMENT 'Nombre de diagnostics transférés avec le site',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_cession_site` (`cession_id`, `site_id`),
    INDEX `idx_cession` (`cession_id`),
    INDEX `idx_site` (`site_id`),
    INDEX `idx_transfer_status` (`transfer_status`),
    FOREIGN KEY (`cession_id`) REFERENCES `cessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: cession_documents
-- Description: Documents liés aux cessions (contrats, AR, etc.)
-- =================================================================
CREATE TABLE IF NOT EXISTS `cession_documents` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cession_id` INT UNSIGNED NOT NULL,
    `document_type` ENUM('contract', 'acknowledgment', 'inventory', 'other') NOT NULL,
    `filename` VARCHAR(255) NOT NULL COMMENT 'Nom stockage interne',
    `original_filename` VARCHAR(255) NOT NULL COMMENT 'Nom original',
    `filepath` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL COMMENT 'Taille en octets',
    `mime_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT UNSIGNED NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `download_count` INT DEFAULT 0,
    INDEX `idx_cession` (`cession_id`),
    INDEX `idx_document_type` (`document_type`),
    INDEX `idx_uploaded_by` (`uploaded_by`),
    FOREIGN KEY (`cession_id`) REFERENCES `cessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: cession_events
-- Description: Timeline horodatée des événements de cession (IMMUABLE)
-- =================================================================
CREATE TABLE IF NOT EXISTS `cession_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cession_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(50) NOT NULL COMMENT 'created, submitted, validated, site_transferred, completed, cancelled, etc.',
    `user_id` INT UNSIGNED NULL COMMENT 'Null si événement système',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `data` JSON NULL COMMENT 'Données contextuelles de l\'événement',
    `ip_address` VARCHAR(45) NULL,
    `is_system` BOOLEAN DEFAULT FALSE COMMENT 'TRUE si événement automatique',
    INDEX `idx_cession_timestamp` (`cession_id`, `timestamp`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_user` (`user_id`),
    FOREIGN KEY (`cession_id`) REFERENCES `cessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: cession_notifications
-- Description: Notifications emails liées aux cessions
-- =================================================================
CREATE TABLE IF NOT EXISTS `cession_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cession_id` INT UNSIGNED NOT NULL,
    `notification_type` ENUM('creation', 'validation', 'transfer', 'completion', 'cancellation') NOT NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NOT NULL,
    `body` TEXT NOT NULL,
    `sent_at` TIMESTAMP NULL,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cession` (`cession_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_notification_type` (`notification_type`),
    FOREIGN KEY (`cession_id`) REFERENCES `cessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =================================================================
-- Insertion dans la table migrations
-- =================================================================
INSERT INTO `migrations` (`version`, `filename`, `executed_at`)
VALUES (6, '006_create_cessions_tables.sql', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `executed_at` = CURRENT_TIMESTAMP;
