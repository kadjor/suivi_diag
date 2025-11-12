-- Migration 011: Agrandir la colonne postal_code pour accepter des codes postaux plus longs
-- Date: 2025-11-12
-- Description: Corriger l'erreur "Data too long for column 'postal_code'"

-- Agrandir la colonne postal_code pour accepter jusqu'à 20 caractères
ALTER TABLE `sites`
    MODIFY COLUMN `postal_code` VARCHAR(20) NOT NULL COMMENT 'Code postal (jusqu''à 20 caractères)';
