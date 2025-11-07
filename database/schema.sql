-- =================================================================
-- Schéma de base de données - Plateforme de Suivi de Diagnostics
-- Version: 1.0.0
-- =================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- TABLE: roles
-- Description: Rôles utilisateurs (admin, secretariat, technicien, client)
-- =================================================================
CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'admin, secretariat, technicien, client',
    `label` VARCHAR(100) NOT NULL,
    `permissions` JSON NOT NULL COMMENT 'Structure: {resource: {action: boolean}}',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: clients
-- Description: Organisations clientes (PCH, etc.)
-- =================================================================
CREATE TABLE `clients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `organization_name` VARCHAR(255) NOT NULL,
    `contact_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `address` TEXT,
    `city` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `country` VARCHAR(100) DEFAULT 'France',
    `siret` VARCHAR(14),
    `notes` TEXT,
    `active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_organization` (`organization_name`),
    INDEX `idx_email` (`email`),
    INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: users
-- Description: Utilisateurs de la plateforme
-- =================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `client_id` INT UNSIGNED NULL COMMENT 'Null pour non-clients (admin, secrétariat, technicien)',
    `phone` VARCHAR(20),
    `active` BOOLEAN DEFAULT TRUE,
    `last_login` TIMESTAMP NULL,
    `login_attempts` INT DEFAULT 0,
    `locked_until` TIMESTAMP NULL,
    `password_reset_token` VARCHAR(255) NULL,
    `password_reset_expires` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role_id`),
    INDEX `idx_client` (`client_id`),
    INDEX `idx_active` (`active`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: sites
-- Description: Bâtiments/sites du patrimoine client
-- =================================================================
CREATE TABLE `sites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `address` VARCHAR(500) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) DEFAULT 'France',
    `latitude` DECIMAL(10, 7) NULL,
    `longitude` DECIMAL(10, 7) NULL,
    `reference_pch` VARCHAR(100) NULL COMMENT 'Identifiant interne client',
    `building_type` VARCHAR(100) NULL COMMENT 'Type de bâtiment',
    `construction_year` INT NULL,
    `surface` INT NULL COMMENT 'Surface en m²',
    `metadata` JSON NULL COMMENT 'Champs personnalisés',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_client` (`client_id`),
    INDEX `idx_reference_pch` (`reference_pch`),
    INDEX `idx_location` (`latitude`, `longitude`),
    INDEX `idx_city` (`city`),
    FULLTEXT INDEX `ft_address` (`address`, `city`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: diagnostic_types
-- Description: Référentiel des types de diagnostics
-- =================================================================
CREATE TABLE `diagnostic_types` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'DTA, DAPP, RAAT, RAAD, DPE, etc.',
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `color` VARCHAR(7) DEFAULT '#3498db' COMMENT 'Couleur hex pour UI',
    `active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 0,
    INDEX `idx_code` (`code`),
    INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: statuses
-- Description: Référentiel des statuts (commandes, interventions, diagnostics)
-- =================================================================
CREATE TABLE `statuses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `category` ENUM('order', 'intervention', 'diagnostic') NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#95a5a6',
    `icon` VARCHAR(50) NULL,
    `sort_order` INT DEFAULT 0,
    `active` BOOLEAN DEFAULT TRUE,
    UNIQUE KEY `uk_category_code` (`category`, `code`),
    INDEX `idx_category` (`category`),
    INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: orders
-- Description: Bons de commande
-- =================================================================
CREATE TABLE `orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: CMD-YYYYMMDD-XXXX',
    `client_id` INT UNSIGNED NOT NULL,
    `status_id` INT UNSIGNED NOT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `requested_date` DATE NULL,
    `deadline_date` DATE NULL,
    `description` TEXT,
    `custom_fields` JSON NULL COMMENT 'Champs paramétrables',
    `created_by` INT UNSIGNED NOT NULL,
    `assigned_to` INT UNSIGNED NULL COMMENT 'Technicien assigné',
    `closed_at` TIMESTAMP NULL,
    `closed_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_client` (`client_id`),
    INDEX `idx_status` (`status_id`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_priority` (`priority`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`status_id`) REFERENCES `statuses`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: order_events
-- Description: Timeline horodatée des événements de commande (IMMUABLE)
-- =================================================================
CREATE TABLE `order_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(50) NOT NULL COMMENT 'created, acknowledged, assigned, scheduled, in_progress, report_uploaded, closed, etc.',
    `user_id` INT UNSIGNED NULL COMMENT 'Null si événement système',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `data` JSON NULL COMMENT 'Données contextuelles de l\'événement',
    `ip_address` VARCHAR(45) NULL,
    `is_system` BOOLEAN DEFAULT FALSE COMMENT 'TRUE si événement automatique',
    INDEX `idx_order_timestamp` (`order_id`, `timestamp`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_user` (`user_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: interventions
-- Description: Interventions techniques sur les sites
-- =================================================================
CREATE TABLE `interventions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `site_id` INT UNSIGNED NOT NULL,
    `technician_id` INT UNSIGNED NOT NULL,
    `diagnostic_types` JSON NOT NULL COMMENT 'Array des types de diagnostics à réaliser',
    `scheduled_date` DATE NULL,
    `completed_date` DATE NULL,
    `status` ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT NULL COMMENT 'Notes visibles par le client',
    `internal_notes` TEXT NULL COMMENT 'Notes internes non visibles client',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_site` (`site_id`),
    INDEX `idx_technician` (`technician_id`),
    INDEX `idx_scheduled_date` (`scheduled_date`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: diagnostics
-- Description: Diagnostics réalisés sur les sites
-- =================================================================
CREATE TABLE `diagnostics` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `site_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `intervention_id` INT UNSIGNED NULL,
    `diagnostic_type_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('ok', 'anomaly', 'critical') DEFAULT 'ok',
    `criticality` TINYINT DEFAULT 0 COMMENT '0-5',
    `reference_number` VARCHAR(100) NULL COMMENT 'Numéro de rapport',
    `valid_until` DATE NULL,
    `report_id` INT UNSIGNED NULL,
    `excel_import_id` INT UNSIGNED NULL COMMENT 'Import Excel dont provient ce diagnostic',
    `metadata` JSON NULL COMMENT 'Données complémentaires',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_site_date` (`site_id`, `date`),
    INDEX `idx_order` (`order_id`),
    INDEX `idx_intervention` (`intervention_id`),
    INDEX `idx_diagnostic_type` (`diagnostic_type_id`),
    INDEX `idx_date` (`date`),
    INDEX `idx_status` (`status`),
    INDEX `idx_excel_import` (`excel_import_id`),
    FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`intervention_id`) REFERENCES `interventions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: reports
-- Description: Rapports PDF
-- =================================================================
CREATE TABLE `reports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `intervention_id` INT UNSIGNED NULL,
    `filename` VARCHAR(255) NOT NULL COMMENT 'Nom stockage interne',
    `original_filename` VARCHAR(255) NOT NULL COMMENT 'Nom original',
    `filepath` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL COMMENT 'Taille en octets',
    `mime_type` VARCHAR(100) NOT NULL,
    `version` INT DEFAULT 1 COMMENT 'Versionnage des rapports',
    `uploaded_by` INT UNSIGNED NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `download_count` INT DEFAULT 0,
    INDEX `idx_order_version` (`order_id`, `version`),
    INDEX `idx_intervention` (`intervention_id`),
    INDEX `idx_uploaded_by` (`uploaded_by`),
    INDEX `idx_uploaded_at` (`uploaded_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`intervention_id`) REFERENCES `interventions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: attachments
-- Description: Pièces jointes (commandes, messages)
-- =================================================================
CREATE TABLE `attachments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NULL,
    `message_id` INT UNSIGNED NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT UNSIGNED NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_message` (`message_id`),
    INDEX `idx_uploaded_by` (`uploaded_by`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: messages
-- Description: Messagerie par commande
-- =================================================================
CREATE TABLE `messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `recipient_id` INT UNSIGNED NULL COMMENT 'Null = visible par tous',
    `content` TEXT NOT NULL,
    `requires_response` BOOLEAN DEFAULT FALSE,
    `response_to` INT UNSIGNED NULL COMMENT 'ID message parent',
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_created` (`order_id`, `created_at`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_recipient` (`recipient_id`),
    INDEX `idx_response_to` (`response_to`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`response_to`) REFERENCES `messages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: appointments
-- Description: Rendez-vous planifiés
-- =================================================================
CREATE TABLE `appointments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `intervention_id` INT UNSIGNED NULL,
    `technician_id` INT UNSIGNED NOT NULL,
    `site_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `location` VARCHAR(500) NULL,
    `notes` TEXT NULL,
    `status` ENUM('scheduled', 'confirmed', 'completed', 'cancelled') DEFAULT 'scheduled',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_technician_date` (`technician_id`, `start_datetime`),
    INDEX `idx_order` (`order_id`),
    INDEX `idx_intervention` (`intervention_id`),
    INDEX `idx_site` (`site_id`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`intervention_id`) REFERENCES `interventions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`site_id`) REFERENCES `sites`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: acknowledgments
-- Description: Accusés de réception
-- =================================================================
CREATE TABLE `acknowledgments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `type` ENUM('ar_commande', 'ar_rapport', 'ar_cloture') NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `generated_by` INT UNSIGNED NOT NULL,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `sent_at` TIMESTAMP NULL,
    INDEX `idx_order_type` (`order_id`, `type`),
    INDEX `idx_generated_by` (`generated_by`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: excel_imports
-- Description: Historique des imports Excel pour cartographie
-- =================================================================
CREATE TABLE `excel_imports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `report_id` INT UNSIGNED NULL,
    `order_id` INT UNSIGNED NULL,
    `filename` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(500) NOT NULL,
    `imported_by` INT UNSIGNED NOT NULL,
    `imported_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `rows_total` INT DEFAULT 0,
    `rows_processed` INT DEFAULT 0,
    `rows_success` INT DEFAULT 0,
    `rows_errors` INT DEFAULT 0,
    `log_json` JSON NULL COMMENT 'Détails erreurs/warnings',
    `column_mapping` JSON NULL COMMENT 'Correspondance colonnes Excel <-> champs DB',
    `completed_at` TIMESTAMP NULL,
    INDEX `idx_report` (`report_id`),
    INDEX `idx_order` (`order_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_imported_by` (`imported_by`),
    FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`imported_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: audit_log
-- Description: Journal d'audit immuable (INSERT ONLY)
-- =================================================================
CREATE TABLE `audit_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL COMMENT 'Null si action système',
    `action` VARCHAR(50) NOT NULL COMMENT 'login, logout, create, update, delete, view, download, export, etc.',
    `entity_type` VARCHAR(50) NULL COMMENT 'order, report, site, user, etc.',
    `entity_id` INT UNSIGNED NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `payload` JSON NULL COMMENT 'Données pertinentes non sensibles',
    `result` ENUM('success', 'failure') DEFAULT 'success',
    INDEX `idx_timestamp` (`timestamp` DESC),
    INDEX `idx_user_timestamp` (`user_id`, `timestamp`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_action` (`action`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE: settings
-- Description: Paramètres de l'application
-- =================================================================
CREATE TABLE `settings` (
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

-- =================================================================
-- TABLE: migrations
-- Description: Suivi des migrations de schéma
-- =================================================================
CREATE TABLE `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `version` INT NOT NULL UNIQUE,
    `filename` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
