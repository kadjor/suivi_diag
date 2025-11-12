-- Migration 001: Correction des tables existantes avec ancien schéma
-- Date: 2025-11-11
-- Description: Supprime les tables qui pourraient exister avec un ancien schéma
--              pour permettre leur recréation avec le bon schéma

-- Supprimer la table settings si elle existe avec un mauvais schéma
-- Elle sera recréée par migration 002 avec le bon schéma
DROP TABLE IF EXISTS `settings`;

-- Supprimer la table site_imports si elle existe
-- Elle sera recréée par migration 009 avec le bon schéma incluant created_at/updated_at
DROP TABLE IF EXISTS `site_imports`;

-- Note: Cette migration permet de repartir sur une base propre
-- Les anciennes données dans ces tables seront perdues
