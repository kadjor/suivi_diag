-- =================================================================
-- Migration 007: Mise à jour des permissions pour les cessions
-- Description: Ajoute les permissions de cessions aux rôles existants
-- Version: 1.4.0
-- Date: 2025-11-11
-- =================================================================

SET NAMES utf8mb4;

-- =================================================================
-- Mise à jour des permissions des rôles pour inclure les cessions
-- =================================================================

-- Admin: tous les droits sur les cessions
UPDATE `roles`
SET `permissions` = JSON_SET(
    `permissions`,
    '$.cessions.create', true,
    '$.cessions.read', true,
    '$.cessions.update', true,
    '$.cessions.delete', true,
    '$.cessions.validate', true
)
WHERE `name` = 'admin';

-- Secrétariat: création et gestion des cessions, mais pas validation
UPDATE `roles`
SET `permissions` = JSON_SET(
    `permissions`,
    '$.cessions.create', true,
    '$.cessions.read', true,
    '$.cessions.update', true,
    '$.cessions.delete', false,
    '$.cessions.validate', false
)
WHERE `name` = 'secretariat';

-- Technicien: pas d'accès aux cessions
UPDATE `roles`
SET `permissions` = JSON_SET(
    `permissions`,
    '$.cessions.read', false
)
WHERE `name` = 'technicien';

-- Client: consultation des cessions qui le concernent
UPDATE `roles`
SET `permissions` = JSON_SET(
    `permissions`,
    '$.cessions.read', 'own'
)
WHERE `name` = 'client';

-- =================================================================
-- Insertion dans la table migrations
-- =================================================================
INSERT INTO `migrations` (`version`, `filename`, `executed_at`)
VALUES (7, '007_update_permissions_for_cessions.sql', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `executed_at` = CURRENT_TIMESTAMP;
