-- Migration 010: Rendre le champ 'name' nullable dans la table sites
-- Date: 2025-11-12
-- Description: Permet de mapper le champ name depuis Excel ou de le laisser vide

-- Modifier la colonne name pour accepter NULL
ALTER TABLE `sites`
    MODIFY COLUMN `name` VARCHAR(255) NULL COMMENT 'Nom du site (peut être mappé depuis Excel)';
