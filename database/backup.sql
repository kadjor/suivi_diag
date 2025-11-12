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
-- Dumping data for table `acknowledgments`
--

LOCK TABLES `acknowledgments` WRITE;
/*!40000 ALTER TABLE `acknowledgments` DISABLE KEYS */;
/*!40000 ALTER TABLE `acknowledgments` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES
(1,1,1,4,1,'DTA Bâtiment A','2024-01-22 09:00:00','2024-01-22 17:00:00','15 Rue de Rivoli, 75001 Paris',NULL,'completed',2,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(2,2,2,5,2,'RAAT Immeuble Opéra','2024-02-28 08:30:00','2024-03-01 16:00:00','8 Boulevard des Capucines, 75009 Paris',NULL,'completed',2,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,3,3,6,3,'Multi-diagnostics Tour Montparnasse','2024-03-12 09:00:00','2024-03-13 17:00:00','25 Avenue du Maine, 75015 Paris',NULL,'confirmed',2,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(4,4,4,4,4,'DPE Résidence Les Lilas','2024-03-18 14:00:00','2024-03-18 17:00:00','45 Avenue Gambetta, 75020 Paris',NULL,'scheduled',2,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES
(1,2,'create','order',1,'2024-01-15 08:15:00','192.168.1.10',NULL,'{\"order_number\": \"CMD-20240115-0001\"}','success'),
(2,4,'upload','report',1,'2024-01-24 15:30:00','192.168.1.25',NULL,'{\"order_id\": 1, \"filename\": \"Rapport_DTA_BAT_A.pdf\"}','success'),
(3,7,'download','report',1,'2024-01-29 09:00:00','82.64.123.45',NULL,'{\"order_id\": 1}','success'),
(4,2,'create','order',2,'2024-02-20 09:30:00','192.168.1.10',NULL,'{\"order_number\": \"CMD-20240220-0002\"}','success'),
(5,1,'login',NULL,NULL,'2025-11-07 23:43:58','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(6,1,'login',NULL,NULL,'2025-11-07 23:49:36','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(7,1,'login',NULL,NULL,'2025-11-07 23:53:42','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(8,1,'login',NULL,NULL,'2025-11-07 23:57:45','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(9,1,'login',NULL,NULL,'2025-11-08 00:34:09','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(10,1,'login',NULL,NULL,'2025-11-08 01:15:17','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(11,1,'login',NULL,NULL,'2025-11-08 08:37:55','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(12,NULL,'login',NULL,NULL,'2025-11-08 12:09:18','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'failure'),
(13,1,'login',NULL,NULL,'2025-11-08 12:09:22','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(14,1,'login',NULL,NULL,'2025-11-09 18:45:18','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(15,7,'login',NULL,NULL,'2025-11-09 19:01:27','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(16,1,'login',NULL,NULL,'2025-11-10 12:09:27','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(17,1,'login',NULL,NULL,'2025-11-11 12:07:36','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(18,NULL,'login',NULL,NULL,'2025-11-11 19:46:12','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'failure'),
(19,1,'login',NULL,NULL,'2025-11-11 19:46:44','82.67.18.45','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success'),
(20,1,'login',NULL,NULL,'2025-11-12 18:23:57','212.114.16.12','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0',NULL,'success');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cession_documents`
--

LOCK TABLES `cession_documents` WRITE;
/*!40000 ALTER TABLE `cession_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `cession_documents` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cession_events`
--

LOCK TABLES `cession_events` WRITE;
/*!40000 ALTER TABLE `cession_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `cession_events` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cession_notifications`
--

LOCK TABLES `cession_notifications` WRITE;
/*!40000 ALTER TABLE `cession_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `cession_notifications` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cession_sites`
--

LOCK TABLES `cession_sites` WRITE;
/*!40000 ALTER TABLE `cession_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `cession_sites` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `cessions`
--

LOCK TABLES `cessions` WRITE;
/*!40000 ALTER TABLE `cessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cessions` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES
(1,'PCH Immobilier','Pierre Charron','p.charron@pch-immobilier.fr','01 23 45 67 89','123 Avenue des Champs-Élysées','Paris','75008','France','12345678900012','Client principal - patrimoine important à Paris et région parisienne',1,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(2,'Société Foncière Lyon','Marie Dubois','m.dubois@fonciere-lyon.fr','04 78 90 12 34','45 Rue de la République','Lyon','69002','France','98765432100023','Patrimoine tertiaire et résidentiel',1,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,'Immobilière Bordeaux','Jean Martin','j.martin@immo-bordeaux.fr','05 56 78 90 12','78 Cours de l\'Intendance','Bordeaux','33000','France','11223344550034','Spécialisé bâtiments historiques',1,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `custom_branches`
--

LOCK TABLES `custom_branches` WRITE;
/*!40000 ALTER TABLE `custom_branches` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_branches` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `diagnostic_types`
--

LOCK TABLES `diagnostic_types` WRITE;
/*!40000 ALTER TABLE `diagnostic_types` DISABLE KEYS */;
INSERT INTO `diagnostic_types` VALUES
(1,'DTA','Dossier Technique Amiante','Repérage des matériaux et produits contenant de l\'amiante','#e74c3c',1,1),
(2,'DAPP','Diagnostic Amiante Parties Privatives','Diagnostic amiante pour les parties privatives','#e67e22',1,2),
(3,'RAAT','Repérage Amiante Avant Travaux','Repérage amiante avant réalisation de travaux','#d35400',1,3),
(4,'RAAD','Repérage Amiante Avant Démolition','Repérage amiante avant démolition','#c0392b',1,4),
(5,'DPE','Diagnostic Performance Énergétique','Évaluation de la performance énergétique','#27ae60',1,5),
(6,'CREP','Constat de Risque d\'Exposition au Plomb','Diagnostic plomb','#9b59b6',1,6),
(7,'GAZ','État Installation Gaz','Contrôle de l\'installation gaz','#3498db',1,7),
(8,'ELEC','État Installation Électrique','Contrôle de l\'installation électrique','#f39c12',1,8),
(9,'TERMITES','État Parasitaire Termites','Recherche de termites','#95a5a6',1,9),
(10,'ERP','État des Risques et Pollutions','Information sur les risques naturels et technologiques','#34495e',1,10);
/*!40000 ALTER TABLE `diagnostic_types` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `diagnostics`
--

LOCK TABLES `diagnostics` WRITE;
/*!40000 ALTER TABLE `diagnostics` DISABLE KEYS */;
INSERT INTO `diagnostics` VALUES
(1,1,1,1,1,'2024-01-24','anomaly',2,'DIAG-2024-0001','2027-01-24',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(2,2,2,2,3,'2024-03-01','critical',4,'DIAG-2024-0002','2024-06-01',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,1,NULL,NULL,1,'2021-06-15','ok',0,'DIAG-2021-0045','2024-06-15',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(4,1,NULL,NULL,5,'2022-03-10','anomaly',2,'DIAG-2022-0123','2032-03-10',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(5,2,NULL,NULL,1,'2020-11-20','anomaly',3,'DIAG-2020-0312','2023-11-20',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(6,3,NULL,NULL,1,'2019-05-12','critical',4,'DIAG-2019-0087','2022-05-12',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(7,3,NULL,NULL,5,'2023-01-18','ok',0,'DIAG-2023-0022','2033-01-18',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(8,4,NULL,NULL,1,'2018-09-25','anomaly',2,'DIAG-2018-0455','2021-09-25',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(9,5,NULL,NULL,1,'2022-07-08','ok',0,'DIAG-2022-0267','2025-07-08',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(10,5,NULL,NULL,5,'2023-11-14','anomaly',1,'DIAG-2023-0489','2033-11-14',NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `diagnostics` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `email_notifications`
--

LOCK TABLES `email_notifications` WRITE;
/*!40000 ALTER TABLE `email_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_notifications` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `excel_imports`
--

LOCK TABLES `excel_imports` WRITE;
/*!40000 ALTER TABLE `excel_imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `excel_imports` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `interventions`
--

LOCK TABLES `interventions` WRITE;
/*!40000 ALTER TABLE `interventions` DISABLE KEYS */;
INSERT INTO `interventions` VALUES
(1,1,1,4,'[\"DTA\"]','2024-01-22','2024-01-24','completed','Intervention réalisée dans les délais. Accès complet au bâtiment.',NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(2,2,2,5,'[\"RAAT\"]','2024-02-28','2024-03-01','completed','Repérage avant travaux de rénovation. Quelques zones inaccessibles.',NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,3,3,6,'[\"DTA\", \"DPE\", \"ELEC\"]','2024-03-12',NULL,'in_progress','Multi-diagnostics en cours. Accès prévu sur 2 jours.',NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(4,4,4,4,'[\"DPE\"]','2024-03-18',NULL,'scheduled',NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `interventions` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES
(1,1,7,NULL,'Bonjour, pouvez-vous confirmer la disponibilité du site pour l\'intervention prévue ?',0,NULL,NULL,'2024-01-17 09:00:00'),
(2,1,2,7,'Bonjour M. Charron, la disponibilité est confirmée. Le technicien Jean Dupont interviendra le 22/01 à 9h.',0,NULL,NULL,'2024-01-17 13:30:00'),
(3,2,5,2,'Attention : zones au 3ème étage difficilement accessibles. Prévoir échafaudage.',1,NULL,NULL,'2024-02-28 15:00:00'),
(4,2,2,5,'Bien noté. Échafaudage commandé pour demain matin.',0,NULL,NULL,'2024-02-28 15:45:00'),
(5,3,6,2,'Début de l\'intervention. Diagnostic électrique terminé, résultats OK.',0,NULL,NULL,'2024-03-12 11:00:00');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,1,'001_initial_schema.sql','2025-11-07 23:38:34'),
(2,6,'006_create_cessions_tables.sql','2025-11-11 21:39:49'),
(3,7,'007_update_permissions_for_cessions.sql','2025-11-11 21:39:49'),
(4,8,'008_create_custom_branches_table.sql','2025-11-11 21:39:50');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `order_diagnostics`
--

LOCK TABLES `order_diagnostics` WRITE;
/*!40000 ALTER TABLE `order_diagnostics` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_diagnostics` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `order_events`
--

LOCK TABLES `order_events` WRITE;
/*!40000 ALTER TABLE `order_events` DISABLE KEYS */;
INSERT INTO `order_events` VALUES
(1,1,'created',2,'2024-01-15 08:15:00','{\"priority\": \"normal\", \"deadline\": \"2024-01-30\"}',NULL,0),
(2,1,'acknowledged',2,'2024-01-15 09:00:00','{\"ar_type\": \"ar_commande\"}',NULL,0),
(3,1,'assigned',2,'2024-01-15 10:30:00','{\"technician_id\": 4, \"technician_name\": \"Jean Dupont\"}',NULL,0),
(4,1,'scheduled',2,'2024-01-16 13:00:00','{\"appointment_date\": \"2024-01-22\"}',NULL,0),
(5,1,'in_progress',4,'2024-01-22 08:00:00','{\"site\": \"Bâtiment A - Siège Social\"}',NULL,0),
(6,1,'report_uploaded',4,'2024-01-24 15:30:00','{\"filename\": \"Rapport_DTA_BAT_A.pdf\"}',NULL,0),
(7,1,'report_validated',2,'2024-01-25 09:00:00','{\"validated_by\": \"Sophie Leroux\"}',NULL,0),
(8,1,'completed',2,'2024-01-25 09:05:00',NULL,NULL,0),
(9,1,'closed',2,'2024-01-28 15:30:00','{\"closure_notes\": \"Intervention terminée avec succès\"}',NULL,0),
(10,2,'created',2,'2024-02-20 09:30:00','{\"priority\": \"high\", \"deadline\": \"2024-03-10\"}',NULL,0),
(11,2,'acknowledged',2,'2024-02-20 10:00:00','{\"ar_type\": \"ar_commande\"}',NULL,0),
(12,2,'assigned',2,'2024-02-20 13:00:00','{\"technician_id\": 5, \"technician_name\": \"Marc Bernard\"}',NULL,0),
(13,2,'scheduled',2,'2024-02-21 08:00:00','{\"appointment_date\": \"2024-02-28\"}',NULL,0),
(14,2,'in_progress',5,'2024-02-28 07:30:00','{\"site\": \"Immeuble Haussmannien - Opéra\"}',NULL,0),
(15,2,'report_uploaded',5,'2024-03-01 16:00:00','{\"filename\": \"Rapport_RAAT_Opera.pdf\", \"has_excel\": true}',NULL,0),
(16,3,'created',2,'2024-03-05 13:00:00','{\"priority\": \"urgent\", \"deadline\": \"2024-03-15\"}',NULL,0),
(17,3,'acknowledged',2,'2024-03-05 13:30:00','{\"ar_type\": \"ar_commande\"}',NULL,0),
(18,3,'assigned',2,'2024-03-05 14:00:00','{\"technician_id\": 6, \"technician_name\": \"Luc Petit\"}',NULL,0),
(19,3,'scheduled',2,'2024-03-06 09:00:00','{\"appointment_date\": \"2024-03-12\"}',NULL,0),
(20,3,'in_progress',6,'2024-03-12 08:00:00','{\"site\": \"Tour Montparnasse Annexe\"}',NULL,0),
(21,4,'created',2,'2024-03-10 10:20:00','{\"priority\": \"normal\", \"deadline\": \"2024-03-25\"}',NULL,0),
(22,4,'acknowledged',2,'2024-03-10 11:00:00','{\"ar_type\": \"ar_commande\"}',NULL,0),
(23,4,'assigned',2,'2024-03-10 14:30:00','{\"technician_id\": 4, \"technician_name\": \"Jean Dupont\"}',NULL,0),
(24,4,'scheduled',2,'2024-03-11 08:00:00','{\"appointment_date\": \"2024-03-18\"}',NULL,0),
(25,5,'created',2,'2024-03-12 08:00:00','{\"priority\": \"normal\", \"deadline\": \"2024-03-30\"}',NULL,0);
/*!40000 ALTER TABLE `order_events` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `order_reports`
--

LOCK TABLES `order_reports` WRITE;
/*!40000 ALTER TABLE `order_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_reports` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,'CMD-20240115-0001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,NULL,8,'normal','2024-01-15','2024-01-30','DTA complet Bâtiment A - Siège Social',NULL,2,4,'2024-01-28 15:30:00',2,'2024-01-15 08:15:00','2025-11-07 23:38:34'),
(2,'CMD-20240220-0002',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,NULL,6,'high','2024-02-20','2024-03-10','Diagnostic amiante avant travaux - Immeuble Opéra',NULL,2,5,NULL,NULL,'2024-02-20 09:30:00','2025-11-07 23:38:34'),
(3,'CMD-20240305-0003',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,NULL,5,'urgent','2024-03-05','2024-03-15','Multi-diagnostics Tour Montparnasse (DTA + DPE + ELEC)',NULL,2,6,NULL,NULL,'2024-03-05 13:00:00','2025-11-07 23:38:34'),
(4,'CMD-20240310-0004',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,NULL,4,'normal','2024-03-10','2024-03-25','DPE Résidence Les Lilas',NULL,2,4,NULL,NULL,'2024-03-10 10:20:00','2025-11-07 23:38:34'),
(5,'CMD-20240312-0005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,2,NULL,1,'normal','2024-03-12','2024-03-30','DTA Immeuble Part-Dieu Lyon',NULL,2,NULL,NULL,NULL,'2024-03-12 08:00:00','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'admin','Administrateur','{\"users\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"clients\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"sites\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"orders\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"close\": true}, \"interventions\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"reports\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"download\": true}, \"messages\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"diagnostics\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"map\": {\"read\": true, \"export\": true}, \"appointments\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"settings\": {\"read\": true, \"update\": true}, \"audit\": {\"read\": true, \"export\": true}, \"exports\": {\"all\": true}}','2025-11-07 23:38:34'),
(2,'secretariat','Secrétariat','{\"clients\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": false}, \"sites\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": false}, \"orders\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": false, \"close\": true, \"assign\": true}, \"interventions\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": false}, \"reports\": {\"create\": true, \"read\": true, \"download\": true}, \"messages\": {\"create\": true, \"read\": true, \"update\": false, \"delete\": false}, \"diagnostics\": {\"read\": true, \"update\": false}, \"map\": {\"read\": true}, \"appointments\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true}, \"exports\": {\"orders\": true, \"interventions\": true}}','2025-11-07 23:38:34'),
(3,'technicien','Technicien','{\"orders\": {\"read\": \"assigned\", \"update\": \"assigned\"}, \"interventions\": {\"read\": \"assigned\", \"update\": \"assigned\"}, \"reports\": {\"create\": true, \"read\": \"assigned\", \"download\": \"assigned\", \"upload_excel\": true}, \"messages\": {\"create\": true, \"read\": \"assigned\"}, \"diagnostics\": {\"read\": \"assigned\"}, \"map\": {\"read\": true}, \"appointments\": {\"read\": \"assigned\", \"update\": \"assigned\"}}','2025-11-07 23:38:34'),
(4,'client','Client','{\"orders\": {\"create\": true, \"read\": \"own\", \"update\": false}, \"reports\": {\"read\": \"own\", \"download\": \"own\"}, \"messages\": {\"create\": true, \"read\": \"own\"}, \"diagnostics\": {\"read\": \"own\"}, \"sites\": {\"read\": \"own\"}, \"map\": {\"read\": \"own\"}, \"appointments\": {\"read\": \"own\"}}','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
(1,'general','company_name','DiagPro Services','string','Nom de l\'entreprise','Nom affiché dans l\'interface',NULL,'2025-11-07 23:38:34'),
(2,'general','company_address','10 Rue des Diagnostics, 75000 Paris','string','Adresse','Adresse complète',NULL,'2025-11-07 23:38:34'),
(3,'general','company_phone','01 23 45 67 89','string','Téléphone',NULL,NULL,'2025-11-07 23:38:34'),
(4,'general','company_email','contact@diagpro.fr','string','Email',NULL,NULL,'2025-11-07 23:38:34'),
(5,'email','notification_order_created','1','boolean','Notification création commande','Envoyer email à la création',NULL,'2025-11-07 23:38:34'),
(6,'email','notification_report_uploaded','1','boolean','Notification dépôt rapport','Envoyer email au dépôt de rapport',NULL,'2025-11-07 23:38:34'),
(7,'map','default_map_center_lat','48.856614','string','Latitude centre carte',NULL,NULL,'2025-11-07 23:38:34'),
(8,'map','default_map_center_lng','2.341198','string','Longitude centre carte',NULL,NULL,'2025-11-07 23:38:34'),
(9,'map','default_zoom','6','int','Zoom par défaut',NULL,NULL,'2025-11-07 23:38:34');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `site_imports`
--

LOCK TABLES `site_imports` WRITE;
/*!40000 ALTER TABLE `site_imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_imports` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
INSERT INTO `sites` VALUES
(1,1,'Bâtiment A - Siège Social','15 Rue de Rivoli','Paris','75001','France',48.8566140,2.3411980,'PCH-BAT-001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bureaux',1890,2500,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(2,1,'Immeuble Haussmannien - Opéra','8 Boulevard des Capucines','Paris','75009','France',48.8710940,2.3339780,'PCH-BAT-002',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Résidentiel',1875,3200,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,1,'Tour Montparnasse Annexe','25 Avenue du Maine','Paris','75015','France',48.8423920,2.3215410,'PCH-BAT-003',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bureaux',1985,5000,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(4,1,'Résidence Les Lilas','45 Avenue Gambetta','Paris','75020','France',48.8673210,2.3978540,'PCH-BAT-004',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Résidentiel',1960,4200,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(5,1,'Centre Commercial Bercy','12 Cour Saint-Émilion','Paris','75012','France',48.8350290,2.3858490,'PCH-BAT-005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Commercial',1995,8000,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(6,2,'Immeuble Part-Dieu','28 Rue de la République','Lyon','69002','France',45.7634200,4.8356590,'LYN-BAT-001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bureaux',1970,3500,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(7,2,'Résidence Confluence','15 Quai Rambaud','Lyon','69002','France',45.7403700,4.8148400,'LYN-BAT-002',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Résidentiel',2010,2800,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(8,3,'Hôtel Particulier Centre','78 Cours de l\'Intendance','Bordeaux','33000','France',44.8412250,-0.5738920,'BDX-BAT-001',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Historique',1780,1200,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
INSERT INTO `statuses` VALUES
(1,'order','new','Nouvelle','#3498db','fa-plus-circle',1,1),
(2,'order','acknowledged','AR Émis','#9b59b6','fa-check-circle',2,1),
(3,'order','assigned','Assignée','#f39c12','fa-user-check',3,1),
(4,'order','scheduled','Planifiée','#e67e22','fa-calendar-check',4,1),
(5,'order','in_progress','En Cours','#3498db','fa-spinner',5,1),
(6,'order','report_pending','Rapport Déposé','#16a085','fa-file-upload',6,1),
(7,'order','completed','Terminée','#27ae60','fa-check-double',7,1),
(8,'order','closed','Clôturée','#2c3e50','fa-lock',8,1),
(9,'order','cancelled','Annulée','#e74c3c','fa-times-circle',9,1),
(10,'intervention','pending','En Attente','#95a5a6','fa-clock',1,1),
(11,'intervention','scheduled','Planifiée','#f39c12','fa-calendar',2,1),
(12,'intervention','in_progress','En Cours','#3498db','fa-play-circle',3,1),
(13,'intervention','completed','Terminée','#27ae60','fa-check',4,1),
(14,'intervention','cancelled','Annulée','#e74c3c','fa-ban',5,1),
(15,'diagnostic','ok','Conforme','#27ae60','fa-check-circle',1,1),
(16,'diagnostic','anomaly','Anomalie','#f39c12','fa-exclamation-triangle',2,1),
(17,'diagnostic','critical','Critique','#e74c3c','fa-exclamation-circle',3,1);
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','$2y$10$PuD51W/y0Xf4oYU4BiU5p.N11C1SYAX3/SVkDM2XGzIok6mUZom9q','aly@d-evidences.fr','Administrateur','Aly DIOP',1,NULL,'06 79 92 49 07',1,'2025-11-12 18:23:57',0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-12 18:23:57'),
(2,'secretariat','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','secretariat@suivi-diag.fr','Sophie','Leroux',2,NULL,'01 11 11 11 11',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(3,'julie.blanc','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','j.blanc@suivi-diag.fr','Julie','Blanc',2,NULL,'01 22 22 22 22',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(4,'tech1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tech1@suivi-diag.fr','Jean','Dupont',3,NULL,'06 11 11 11 11',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(5,'tech2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tech2@suivi-diag.fr','Marc','Bernard',3,NULL,'06 22 22 22 22',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(6,'tech3','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','tech3@suivi-diag.fr','Luc','Petit',3,NULL,'06 33 33 33 33',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(7,'client.pch','$2y$10$asYKLx1apY.tWMyyBFjPzunOt1i.CzsOMcDSnxF0JuESaN4wfJb6a','p.charron@pch-immobilier.fr','Pierre','Charron',4,1,'01 23 45 67 89',1,'2025-11-09 19:01:27',0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-09 19:01:27'),
(8,'client.lyon','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','m.dubois@fonciere-lyon.fr','Marie','Dubois',4,2,'04 78 90 12 34',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34'),
(9,'client.bdx','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','j.martin@immo-bordeaux.fr','Jean','Martin',4,3,'05 56 78 90 12',1,NULL,0,NULL,NULL,NULL,'2025-11-07 23:38:34','2025-11-07 23:38:34');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'gestion'
--

--
-- Dumping routines for database 'gestion'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-12 19:34:35
