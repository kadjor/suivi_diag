-- =================================================================
-- Migration 005: Gestion destinataires rapports et notifications
-- Description: Ajout destinataires rapports, notifications système
-- =================================================================

-- Ajout champ destinataires rapports dans orders
ALTER TABLE `orders`
    ADD COLUMN `report_recipients` TEXT NULL COMMENT 'Emails destinataires des rapports (JSON)' AFTER `bon_de_commande_pdf`,
    ADD COLUMN `notification_sent` BOOLEAN DEFAULT FALSE COMMENT 'Notification secrétariat envoyée' AFTER `report_recipients`,
    ADD COLUMN `notification_sent_at` TIMESTAMP NULL COMMENT 'Date notification envoyée' AFTER `notification_sent`;

-- Table pour les rapports uploadés par les techniciens
CREATE TABLE IF NOT EXISTS `order_reports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `diagnostic_type_id` INT UNSIGNED NULL COMMENT 'Type de diagnostic concerné',
    `report_file` VARCHAR(500) NOT NULL COMMENT 'Chemin du fichier PDF',
    `original_filename` VARCHAR(255) NOT NULL,
    `uploaded_by` INT UNSIGNED NOT NULL COMMENT 'Technicien qui a uploadé',
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `file_size` INT UNSIGNED NULL COMMENT 'Taille en octets',
    `client_notified` BOOLEAN DEFAULT FALSE COMMENT 'Client notifié par email',
    `client_notified_at` TIMESTAMP NULL,
    `notes` TEXT NULL,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_diagnostic_type` (`diagnostic_type_id`),
    INDEX `idx_uploaded_by` (`uploaded_by`),
    INDEX `idx_uploaded_at` (`uploaded_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rapports uploadés par les techniciens';

-- Table pour les notifications par email
CREATE TABLE IF NOT EXISTS `email_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL COMMENT 'order_created, report_uploaded, etc.',
    `recipient_email` VARCHAR(255) NOT NULL,
    `recipient_name` VARCHAR(255) NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `related_order_id` INT UNSIGNED NULL,
    `related_report_id` INT UNSIGNED NULL,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `error_message` TEXT NULL,
    `retry_count` INT DEFAULT 0,
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_order` (`related_order_id`),
    INDEX `idx_sent_at` (`sent_at`),
    FOREIGN KEY (`related_order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historique notifications email';
