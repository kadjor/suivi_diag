/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gestion
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acknowledgments`
--

DROP TABLE IF EXISTS `acknowledgments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `acknowledgments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `type` enum('ar_commande','ar_rapport','ar_cloture') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `generated_by` int(10) unsigned NOT NULL,
  `generated_at` timestamp NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_type` (`order_id`,`type`),
  KEY `idx_generated_by` (`generated_by`),
  CONSTRAINT `acknowledgments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `acknowledgments_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `intervention_id` int(10) unsigned DEFAULT NULL,
  `technician_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `location` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','confirmed','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_technician_date` (`technician_id`,`start_datetime`),
  KEY `idx_order` (`order_id`),
  KEY `idx_intervention` (`intervention_id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_status` (`status`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`intervention_id`) REFERENCES `interventions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned DEFAULT NULL,
  `message_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(10) unsigned NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_message` (`message_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'Null si action système',
  `action` varchar(50) NOT NULL COMMENT 'login, logout, create, update, delete, view, download, export, etc.',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'order, report, site, user, etc.',
  `entity_id` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données pertinentes non sensibles' CHECK (json_valid(`payload`)),
  `result` enum('success','failure') DEFAULT 'success',
  PRIMARY KEY (`id`),
  KEY `idx_timestamp` (`timestamp` DESC),
  KEY `idx_user_timestamp` (`user_id`,`timestamp`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_action` (`action`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cession_documents`
--

DROP TABLE IF EXISTS `cession_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cession_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cession_id` int(10) unsigned NOT NULL,
  `document_type` enum('contract','acknowledgment','inventory','other') NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT 'Nom stockage interne',
  `original_filename` varchar(255) NOT NULL COMMENT 'Nom original',
  `filepath` varchar(500) NOT NULL,
  `file_size` int(10) unsigned NOT NULL COMMENT 'Taille en octets',
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(10) unsigned NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `download_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_cession` (`cession_id`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `cession_documents_ibfk_1` FOREIGN KEY (`cession_id`) REFERENCES `cessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cession_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cession_events`
--

DROP TABLE IF EXISTS `cession_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cession_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cession_id` int(10) unsigned NOT NULL,
  `event_type` varchar(50) NOT NULL COMMENT 'created, submitted, validated, site_transferred, completed, cancelled, etc.',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'Null si événement système',
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données contextuelles de l''événement' CHECK (json_valid(`data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'TRUE si événement automatique',
  PRIMARY KEY (`id`),
  KEY `idx_cession_timestamp` (`cession_id`,`timestamp`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `cession_events_ibfk_1` FOREIGN KEY (`cession_id`) REFERENCES `cessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cession_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cession_notifications`
--

DROP TABLE IF EXISTS `cession_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cession_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cession_id` int(10) unsigned NOT NULL,
  `notification_type` enum('creation','validation','transfer','completion','cancellation') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cession` (`cession_id`),
  KEY `idx_status` (`status`),
  KEY `idx_notification_type` (`notification_type`),
  CONSTRAINT `cession_notifications_ibfk_1` FOREIGN KEY (`cession_id`) REFERENCES `cessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cession_sites`
--

DROP TABLE IF EXISTS `cession_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cession_sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cession_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  `transfer_status` enum('pending','transferred','failed') DEFAULT 'pending',
  `transfer_date` timestamp NULL DEFAULT NULL COMMENT 'Date effective du transfert',
  `transfer_notes` text DEFAULT NULL COMMENT 'Notes sur le transfert',
  `diagnostics_count` int(11) DEFAULT 0 COMMENT 'Nombre de diagnostics transférés avec le site',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cession_site` (`cession_id`,`site_id`),
  KEY `idx_cession` (`cession_id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_transfer_status` (`transfer_status`),
  CONSTRAINT `cession_sites_ibfk_1` FOREIGN KEY (`cession_id`) REFERENCES `cessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cession_sites_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cessions`
--

DROP TABLE IF EXISTS `cessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cession_number` varchar(50) NOT NULL COMMENT 'Format: CES-YYYYMMDD-XXXX',
  `from_client_id` int(10) unsigned NOT NULL COMMENT 'Client cédant',
  `to_client_id` int(10) unsigned NOT NULL COMMENT 'Client cessionnaire',
  `status` enum('draft','pending_validation','validated','in_progress','completed','cancelled') DEFAULT 'draft',
  `title` varchar(255) NOT NULL COMMENT 'Titre de la cession',
  `description` text DEFAULT NULL COMMENT 'Description détaillée',
  `effective_date` date DEFAULT NULL COMMENT 'Date effective du transfert',
  `completion_date` date DEFAULT NULL COMMENT 'Date de finalisation',
  `total_sites` int(11) DEFAULT 0 COMMENT 'Nombre total de sites à transférer',
  `transferred_sites` int(11) DEFAULT 0 COMMENT 'Nombre de sites déjà transférés',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données complémentaires' CHECK (json_valid(`metadata`)),
  `created_by` int(10) unsigned NOT NULL COMMENT 'Utilisateur créateur',
  `validated_by` int(10) unsigned DEFAULT NULL COMMENT 'Utilisateur validateur',
  `validated_at` timestamp NULL DEFAULT NULL COMMENT 'Date de validation',
  `cancelled_by` int(10) unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cession_number` (`cession_number`),
  KEY `idx_cession_number` (`cession_number`),
  KEY `idx_from_client` (`from_client_id`),
  KEY `idx_to_client` (`to_client_id`),
  KEY `idx_status` (`status`),
  KEY `idx_effective_date` (`effective_date`),
  KEY `idx_created_by` (`created_by`),
  KEY `validated_by` (`validated_by`),
  KEY `cancelled_by` (`cancelled_by`),
  CONSTRAINT `cessions_ibfk_1` FOREIGN KEY (`from_client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `cessions_ibfk_2` FOREIGN KEY (`to_client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `cessions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `cessions_ibfk_4` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cessions_ibfk_5` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_different_clients` CHECK (`from_client_id` <> `to_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_name` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'France',
  `siret` varchar(14) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_organization` (`organization_name`),
  KEY `idx_email` (`email`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custom_branches`
--

DROP TABLE IF EXISTS `custom_branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_branches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) NOT NULL COMMENT 'Nom de la branche',
  `description` text DEFAULT NULL COMMENT 'Description de la branche',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_name` (`branch_name`),
  KEY `idx_branch_name` (`branch_name`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `custom_branches_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diagnostic_types`
--

DROP TABLE IF EXISTS `diagnostic_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnostic_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL COMMENT 'DTA, DAPP, RAAT, RAAD, DPE, etc.',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3498db' COMMENT 'Couleur hex pour UI',
  `active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diagnostics`
--

DROP TABLE IF EXISTS `diagnostics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnostics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `intervention_id` int(10) unsigned DEFAULT NULL,
  `diagnostic_type_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('ok','anomaly','critical') DEFAULT 'ok',
  `criticality` tinyint(4) DEFAULT 0 COMMENT '0-5',
  `reference_number` varchar(100) DEFAULT NULL COMMENT 'Numéro de rapport',
  `valid_until` date DEFAULT NULL,
  `report_id` int(10) unsigned DEFAULT NULL,
  `excel_import_id` int(10) unsigned DEFAULT NULL COMMENT 'Import Excel dont provient ce diagnostic',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données complémentaires' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_site_date` (`site_id`,`date`),
  KEY `idx_order` (`order_id`),
  KEY `idx_intervention` (`intervention_id`),
  KEY `idx_diagnostic_type` (`diagnostic_type_id`),
  KEY `idx_date` (`date`),
  KEY `idx_status` (`status`),
  KEY `idx_excel_import` (`excel_import_id`),
  CONSTRAINT `diagnostics_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diagnostics_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `diagnostics_ibfk_3` FOREIGN KEY (`intervention_id`) REFERENCES `interventions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `diagnostics_ibfk_4` FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_notifications`
--

DROP TABLE IF EXISTS `email_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT 'order_created, report_uploaded, etc.',
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `related_order_id` int(10) unsigned DEFAULT NULL,
  `related_report_id` int(10) unsigned DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_order` (`related_order_id`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `email_notifications_ibfk_1` FOREIGN KEY (`related_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historique notifications email';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `excel_imports`
--

DROP TABLE IF EXISTS `excel_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `excel_imports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(10) unsigned DEFAULT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `imported_by` int(10) unsigned NOT NULL,
  `imported_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `rows_total` int(11) DEFAULT 0,
  `rows_processed` int(11) DEFAULT 0,
  `rows_success` int(11) DEFAULT 0,
  `rows_errors` int(11) DEFAULT 0,
  `log_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Détails erreurs/warnings' CHECK (json_valid(`log_json`)),
  `column_mapping` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Correspondance colonnes Excel <-> champs DB' CHECK (json_valid(`column_mapping`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_report` (`report_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_imported_by` (`imported_by`),
  CONSTRAINT `excel_imports_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `excel_imports_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `excel_imports_ibfk_3` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interventions`
--

DROP TABLE IF EXISTS `interventions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `interventions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  `technician_id` int(10) unsigned NOT NULL,
  `diagnostic_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array des types de diagnostics à réaliser' CHECK (json_valid(`diagnostic_types`)),
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','scheduled','in_progress','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL COMMENT 'Notes visibles par le client',
  `internal_notes` text DEFAULT NULL COMMENT 'Notes internes non visibles client',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_technician` (`technician_id`),
  KEY `idx_scheduled_date` (`scheduled_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `interventions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interventions_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interventions_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `recipient_id` int(10) unsigned DEFAULT NULL COMMENT 'Null = visible par tous',
  `content` text NOT NULL,
  `requires_response` tinyint(1) DEFAULT 0,
  `response_to` int(10) unsigned DEFAULT NULL COMMENT 'ID message parent',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_created` (`order_id`,`created_at`),
  KEY `idx_user` (`user_id`),
  KEY `idx_recipient` (`recipient_id`),
  KEY `idx_response_to` (`response_to`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`response_to`) REFERENCES `messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `executed_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_diagnostics`
--

DROP TABLE IF EXISTS `order_diagnostics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_diagnostics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `diagnostic_type_id` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL COMMENT 'Notes spécifiques pour ce diagnostic',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_diagnostic` (`order_id`,`diagnostic_type_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_diagnostic_type` (`diagnostic_type_id`),
  CONSTRAINT `order_diagnostics_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_diagnostics_ibfk_2` FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Diagnostics demandés pour chaque commande';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_events`
--

DROP TABLE IF EXISTS `order_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `event_type` varchar(50) NOT NULL COMMENT 'created, acknowledged, assigned, scheduled, in_progress, report_uploaded, closed, etc.',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'Null si événement système',
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données contextuelles de l''événement' CHECK (json_valid(`data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'TRUE si événement automatique',
  PRIMARY KEY (`id`),
  KEY `idx_order_timestamp` (`order_id`,`timestamp`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `order_events_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_reports`
--

DROP TABLE IF EXISTS `order_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `diagnostic_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Type de diagnostic concerné',
  `report_file` varchar(500) NOT NULL COMMENT 'Chemin du fichier PDF',
  `original_filename` varchar(255) NOT NULL,
  `uploaded_by` int(10) unsigned NOT NULL COMMENT 'Technicien qui a uploadé',
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `file_size` int(10) unsigned DEFAULT NULL COMMENT 'Taille en octets',
  `client_notified` tinyint(1) DEFAULT 0 COMMENT 'Client notifié par email',
  `client_notified_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_diagnostic_type` (`diagnostic_type_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  CONSTRAINT `order_reports_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_reports_ibfk_2` FOREIGN KEY (`diagnostic_type_id`) REFERENCES `diagnostic_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_reports_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rapports uploadés par les techniciens';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL COMMENT 'Format: CMD-YYYYMMDD-XXXX',
  `numero_lot` varchar(50) DEFAULT NULL COMMENT 'Numéro de lot pour la commande',
  `execution_address` varchar(255) DEFAULT NULL COMMENT 'Adresse d''exécution de la commande',
  `execution_city` varchar(100) DEFAULT NULL COMMENT 'Ville d''exécution',
  `execution_postal_code` varchar(10) DEFAULT NULL COMMENT 'Code postal',
  `execution_numero_porte` varchar(20) DEFAULT NULL COMMENT 'Numéro de porte',
  `execution_niveau` varchar(20) DEFAULT NULL COMMENT 'Niveau/Étage',
  `bon_de_commande_pdf` varchar(500) DEFAULT NULL COMMENT 'Chemin PDF bon de commande uploadé',
  `report_recipients` text DEFAULT NULL COMMENT 'Emails destinataires des rapports (JSON)',
  `notification_sent` tinyint(1) DEFAULT 0 COMMENT 'Notification secrétariat envoyée',
  `notification_sent_at` timestamp NULL DEFAULT NULL COMMENT 'Date notification envoyée',
  `client_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned DEFAULT NULL COMMENT 'Référence au site du patrimoine (si applicable)',
  `status_id` int(10) unsigned NOT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `requested_date` date DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Champs paramétrables' CHECK (json_valid(`custom_fields`)),
  `created_by` int(10) unsigned NOT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL COMMENT 'Technicien assigné',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_client` (`client_id`),
  KEY `idx_status` (`status_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_priority` (`priority`),
  KEY `closed_by` (`closed_by`),
  KEY `idx_numero_lot` (`numero_lot`),
  KEY `idx_site_id` (`site_id`),
  KEY `idx_execution_city` (`execution_city`),
  KEY `idx_execution_postal_code` (`execution_postal_code`),
  CONSTRAINT `fk_orders_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `intervention_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL COMMENT 'Nom stockage interne',
  `original_filename` varchar(255) NOT NULL COMMENT 'Nom original',
  `filepath` varchar(500) NOT NULL,
  `file_size` int(10) unsigned NOT NULL COMMENT 'Taille en octets',
  `mime_type` varchar(100) NOT NULL,
  `version` int(11) DEFAULT 1 COMMENT 'Versionnage des rapports',
  `uploaded_by` int(10) unsigned NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `download_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_order_version` (`order_id`,`version`),
  KEY `idx_intervention` (`intervention_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`intervention_id`) REFERENCES `interventions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'admin, secretariat, technicien, client',
  `label` varchar(100) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Structure: {resource: {action: boolean}}' CHECK (json_valid(`permissions`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL COMMENT 'general, email, map, security, etc.',
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `data_type` enum('string','int','boolean','json') DEFAULT 'string',
  `label` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_key` (`category`,`key`),
  KEY `idx_category` (`category`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_imports`
--

DROP TABLE IF EXISTS `site_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_imports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL COMMENT 'Client concerné par l''import',
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `imported_by` int(10) unsigned NOT NULL,
  `imported_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `rows_total` int(11) DEFAULT 0,
  `rows_processed` int(11) DEFAULT 0,
  `rows_success` int(11) DEFAULT 0,
  `rows_errors` int(11) DEFAULT 0,
  `log_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Détails erreurs/warnings ligne par ligne' CHECK (json_valid(`log_json`)),
  `column_mapping` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Correspondance colonnes Excel <-> champs DB' CHECK (json_valid(`column_mapping`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_status` (`status`),
  KEY `idx_imported_by` (`imported_by`),
  KEY `idx_imported_at` (`imported_at`),
  CONSTRAINT `site_imports_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `site_imports_ibfk_2` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'France',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `reference_pch` varchar(100) DEFAULT NULL COMMENT 'Identifiant interne client',
  `numero_groupe` varchar(50) DEFAULT NULL COMMENT 'Numéro de groupe (obligatoire pour import)',
  `numero_lot` varchar(50) DEFAULT NULL COMMENT 'Numéro de lot (obligatoire pour import)',
  `numero_porte` varchar(20) DEFAULT NULL COMMENT 'Numéro de porte',
  `niveau` varchar(20) DEFAULT NULL COMMENT 'Niveau/Étage',
  `numero_batiment` varchar(50) DEFAULT NULL COMMENT 'Numéro de bâtiment',
  `numero_entree` varchar(20) DEFAULT NULL COMMENT 'Numéro d''entrée',
  `identifiant_fiscal` varchar(100) DEFAULT NULL COMMENT 'Identifiant fiscal',
  `numero_batiment_brgm` varchar(100) DEFAULT NULL COMMENT 'Numéro bâtiment BRGM',
  `cadastre` varchar(100) DEFAULT NULL COMMENT 'Référence cadastrale',
  `nommage_rapport` varchar(255) DEFAULT NULL COMMENT 'Nommage pour rapport',
  `numero_gardien` varchar(50) DEFAULT NULL COMMENT 'Numéro de gardien',
  `nom_groupe` varchar(255) DEFAULT NULL COMMENT 'Nom du groupe',
  `building_type` varchar(100) DEFAULT NULL COMMENT 'Type de bâtiment',
  `construction_year` int(11) DEFAULT NULL,
  `surface` int(11) DEFAULT NULL COMMENT 'Surface en m²',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Champs personnalisés' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_reference_pch` (`reference_pch`),
  KEY `idx_location` (`latitude`,`longitude`),
  KEY `idx_city` (`city`),
  KEY `idx_numero_groupe` (`numero_groupe`),
  KEY `idx_numero_lot` (`numero_lot`),
  KEY `idx_cadastre` (`cadastre`),
  FULLTEXT KEY `ft_address` (`address`,`city`),
  CONSTRAINT `sites_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` enum('order','intervention','diagnostic') NOT NULL,
  `code` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `color` varchar(7) DEFAULT '#95a5a6',
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_code` (`category`,`code`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned DEFAULT NULL COMMENT 'Null pour non-clients (admin, secrétariat, technicien)',
  `phone` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role_id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_active` (`active`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-12 21:07:53
