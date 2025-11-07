-- =================================================================
-- Données initiales et de démonstration
-- Version: 1.0.0
-- =================================================================

SET NAMES utf8mb4;
SET sql_mode = '';

-- =================================================================
-- ROLES
-- =================================================================
INSERT INTO `roles` (`id`, `name`, `label`, `permissions`) VALUES
(1, 'admin', 'Administrateur', '{
    "users": {"create": true, "read": true, "update": true, "delete": true},
    "clients": {"create": true, "read": true, "update": true, "delete": true},
    "sites": {"create": true, "read": true, "update": true, "delete": true},
    "orders": {"create": true, "read": true, "update": true, "delete": true, "close": true},
    "interventions": {"create": true, "read": true, "update": true, "delete": true},
    "reports": {"create": true, "read": true, "update": true, "delete": true, "download": true},
    "messages": {"create": true, "read": true, "update": true, "delete": true},
    "diagnostics": {"create": true, "read": true, "update": true, "delete": true},
    "map": {"read": true, "export": true},
    "appointments": {"create": true, "read": true, "update": true, "delete": true},
    "settings": {"read": true, "update": true},
    "audit": {"read": true, "export": true},
    "exports": {"all": true}
}'),
(2, 'secretariat', 'Secrétariat', '{
    "clients": {"create": true, "read": true, "update": true, "delete": false},
    "sites": {"create": true, "read": true, "update": true, "delete": false},
    "orders": {"create": true, "read": true, "update": true, "delete": false, "close": true, "assign": true},
    "interventions": {"create": true, "read": true, "update": true, "delete": false},
    "reports": {"create": true, "read": true, "download": true},
    "messages": {"create": true, "read": true, "update": false, "delete": false},
    "diagnostics": {"read": true, "update": false},
    "map": {"read": true},
    "appointments": {"create": true, "read": true, "update": true, "delete": true},
    "exports": {"orders": true, "interventions": true}
}'),
(3, 'technicien', 'Technicien', '{
    "orders": {"read": "assigned", "update": "assigned"},
    "interventions": {"read": "assigned", "update": "assigned"},
    "reports": {"create": true, "read": "assigned", "download": "assigned", "upload_excel": true},
    "messages": {"create": true, "read": "assigned"},
    "diagnostics": {"read": "assigned"},
    "map": {"read": true},
    "appointments": {"read": "assigned", "update": "assigned"}
}'),
(4, 'client', 'Client', '{
    "orders": {"create": true, "read": "own", "update": false},
    "reports": {"read": "own", "download": "own"},
    "messages": {"create": true, "read": "own"},
    "diagnostics": {"read": "own"},
    "sites": {"read": "own"},
    "map": {"read": "own"},
    "appointments": {"read": "own"}
}');

-- =================================================================
-- DIAGNOSTIC TYPES
-- =================================================================
INSERT INTO `diagnostic_types` (`code`, `name`, `description`, `color`, `active`, `sort_order`) VALUES
('DTA', 'Dossier Technique Amiante', 'Repérage des matériaux et produits contenant de l\'amiante', '#e74c3c', 1, 1),
('DAPP', 'Diagnostic Amiante Parties Privatives', 'Diagnostic amiante pour les parties privatives', '#e67e22', 1, 2),
('RAAT', 'Repérage Amiante Avant Travaux', 'Repérage amiante avant réalisation de travaux', '#d35400', 1, 3),
('RAAD', 'Repérage Amiante Avant Démolition', 'Repérage amiante avant démolition', '#c0392b', 1, 4),
('DPE', 'Diagnostic Performance Énergétique', 'Évaluation de la performance énergétique', '#27ae60', 1, 5),
('CREP', 'Constat de Risque d\'Exposition au Plomb', 'Diagnostic plomb', '#9b59b6', 1, 6),
('GAZ', 'État Installation Gaz', 'Contrôle de l\'installation gaz', '#3498db', 1, 7),
('ELEC', 'État Installation Électrique', 'Contrôle de l\'installation électrique', '#f39c12', 1, 8),
('TERMITES', 'État Parasitaire Termites', 'Recherche de termites', '#95a5a6', 1, 9),
('ERP', 'État des Risques et Pollutions', 'Information sur les risques naturels et technologiques', '#34495e', 1, 10);

-- =================================================================
-- STATUSES - Orders
-- =================================================================
INSERT INTO `statuses` (`category`, `code`, `label`, `color`, `icon`, `sort_order`) VALUES
('order', 'new', 'Nouvelle', '#3498db', 'fa-plus-circle', 1),
('order', 'acknowledged', 'AR Émis', '#9b59b6', 'fa-check-circle', 2),
('order', 'assigned', 'Assignée', '#f39c12', 'fa-user-check', 3),
('order', 'scheduled', 'Planifiée', '#e67e22', 'fa-calendar-check', 4),
('order', 'in_progress', 'En Cours', '#3498db', 'fa-spinner', 5),
('order', 'report_pending', 'Rapport Déposé', '#16a085', 'fa-file-upload', 6),
('order', 'completed', 'Terminée', '#27ae60', 'fa-check-double', 7),
('order', 'closed', 'Clôturée', '#2c3e50', 'fa-lock', 8),
('order', 'cancelled', 'Annulée', '#e74c3c', 'fa-times-circle', 9);

-- =================================================================
-- STATUSES - Interventions
-- =================================================================
INSERT INTO `statuses` (`category`, `code`, `label`, `color`, `icon`, `sort_order`) VALUES
('intervention', 'pending', 'En Attente', '#95a5a6', 'fa-clock', 1),
('intervention', 'scheduled', 'Planifiée', '#f39c12', 'fa-calendar', 2),
('intervention', 'in_progress', 'En Cours', '#3498db', 'fa-play-circle', 3),
('intervention', 'completed', 'Terminée', '#27ae60', 'fa-check', 4),
('intervention', 'cancelled', 'Annulée', '#e74c3c', 'fa-ban', 5);

-- =================================================================
-- STATUSES - Diagnostics
-- =================================================================
INSERT INTO `statuses` (`category`, `code`, `label`, `color`, `icon`, `sort_order`) VALUES
('diagnostic', 'ok', 'Conforme', '#27ae60', 'fa-check-circle', 1),
('diagnostic', 'anomaly', 'Anomalie', '#f39c12', 'fa-exclamation-triangle', 2),
('diagnostic', 'critical', 'Critique', '#e74c3c', 'fa-exclamation-circle', 3);

-- =================================================================
-- CLIENTS DE DÉMONSTRATION
-- =================================================================
INSERT INTO `clients` (`organization_name`, `contact_name`, `email`, `phone`, `address`, `city`, `postal_code`, `siret`, `notes`) VALUES
('PCH Immobilier', 'Pierre Charron', 'p.charron@pch-immobilier.fr', '01 23 45 67 89', '123 Avenue des Champs-Élysées', 'Paris', '75008', '12345678900012', 'Client principal - patrimoine important à Paris et région parisienne'),
('Société Foncière Lyon', 'Marie Dubois', 'm.dubois@fonciere-lyon.fr', '04 78 90 12 34', '45 Rue de la République', 'Lyon', '69002', '98765432100023', 'Patrimoine tertiaire et résidentiel'),
('Immobilière Bordeaux', 'Jean Martin', 'j.martin@immo-bordeaux.fr', '05 56 78 90 12', '78 Cours de l\'Intendance', 'Bordeaux', '33000', '11223344550034', 'Spécialisé bâtiments historiques');

-- =================================================================
-- UTILISATEURS DE DÉMONSTRATION
-- Mot de passe pour tous : Demo2024!
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =================================================================
INSERT INTO `users` (`username`, `password_hash`, `email`, `first_name`, `last_name`, `role_id`, `client_id`, `phone`) VALUES
-- Administrateur
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@suivi-diag.fr', 'Administrateur', 'Système', 1, NULL, '01 00 00 00 00'),

-- Secrétariat
('secretariat', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretariat@suivi-diag.fr', 'Sophie', 'Leroux', 2, NULL, '01 11 11 11 11'),
('julie.blanc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'j.blanc@suivi-diag.fr', 'Julie', 'Blanc', 2, NULL, '01 22 22 22 22'),

-- Techniciens
('tech1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tech1@suivi-diag.fr', 'Jean', 'Dupont', 3, NULL, '06 11 11 11 11'),
('tech2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tech2@suivi-diag.fr', 'Marc', 'Bernard', 3, NULL, '06 22 22 22 22'),
('tech3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tech3@suivi-diag.fr', 'Luc', 'Petit', 3, NULL, '06 33 33 33 33'),

-- Clients
('client.pch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'p.charron@pch-immobilier.fr', 'Pierre', 'Charron', 4, 1, '01 23 45 67 89'),
('client.lyon', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'm.dubois@fonciere-lyon.fr', 'Marie', 'Dubois', 4, 2, '04 78 90 12 34'),
('client.bdx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'j.martin@immo-bordeaux.fr', 'Jean', 'Martin', 4, 3, '05 56 78 90 12');

-- =================================================================
-- SITES DE DÉMONSTRATION (Patrimoine PCH)
-- =================================================================
INSERT INTO `sites` (`client_id`, `name`, `address`, `city`, `postal_code`, `latitude`, `longitude`, `reference_pch`, `building_type`, `construction_year`, `surface`) VALUES
(1, 'Bâtiment A - Siège Social', '15 Rue de Rivoli', 'Paris', '75001', 48.856614, 2.341198, 'PCH-BAT-001', 'Bureaux', 1890, 2500),
(1, 'Immeuble Haussmannien - Opéra', '8 Boulevard des Capucines', 'Paris', '75009', 48.871094, 2.333978, 'PCH-BAT-002', 'Résidentiel', 1875, 3200),
(1, 'Tour Montparnasse Annexe', '25 Avenue du Maine', 'Paris', '75015', 48.842392, 2.321541, 'PCH-BAT-003', 'Bureaux', 1985, 5000),
(1, 'Résidence Les Lilas', '45 Avenue Gambetta', 'Paris', '75020', 48.867321, 2.397854, 'PCH-BAT-004', 'Résidentiel', 1960, 4200),
(1, 'Centre Commercial Bercy', '12 Cour Saint-Émilion', 'Paris', '75012', 48.835029, 2.385849, 'PCH-BAT-005', 'Commercial', 1995, 8000),
(2, 'Immeuble Part-Dieu', '28 Rue de la République', 'Lyon', '69002', 45.763420, 4.835659, 'LYN-BAT-001', 'Bureaux', 1970, 3500),
(2, 'Résidence Confluence', '15 Quai Rambaud', 'Lyon', '69002', 45.740370, 4.814840, 'LYN-BAT-002', 'Résidentiel', 2010, 2800),
(3, 'Hôtel Particulier Centre', '78 Cours de l\'Intendance', 'Bordeaux', '33000', 44.841225, -0.573892, 'BDX-BAT-001', 'Historique', 1780, 1200);

-- =================================================================
-- COMMANDES DE DÉMONSTRATION
-- =================================================================

-- Commande 1 : Clôturée
INSERT INTO `orders` (`order_number`, `client_id`, `status_id`, `priority`, `requested_date`, `deadline_date`, `description`, `created_by`, `assigned_to`, `closed_at`, `closed_by`, `created_at`) VALUES
('CMD-20240115-0001', 1, 8, 'normal', '2024-01-15', '2024-01-30', 'DTA complet Bâtiment A - Siège Social', 2, 4, '2024-01-28 16:30:00', 2, '2024-01-15 09:15:00');

-- Commande 2 : En cours (rapport déposé)
INSERT INTO `orders` (`order_number`, `client_id`, `status_id`, `priority`, `requested_date`, `deadline_date`, `description`, `created_by`, `assigned_to`, `created_at`) VALUES
('CMD-20240220-0002', 1, 6, 'high', '2024-02-20', '2024-03-10', 'Diagnostic amiante avant travaux - Immeuble Opéra', 2, 5, '2024-02-20 10:30:00');

-- Commande 3 : En cours d'intervention
INSERT INTO `orders` (`order_number`, `client_id`, `status_id`, `priority`, `requested_date`, `deadline_date`, `description`, `created_by`, `assigned_to`, `created_at`) VALUES
('CMD-20240305-0003', 1, 5, 'urgent', '2024-03-05', '2024-03-15', 'Multi-diagnostics Tour Montparnasse (DTA + DPE + ELEC)', 2, 6, '2024-03-05 14:00:00');

-- Commande 4 : Planifiée
INSERT INTO `orders` (`order_number`, `client_id`, `status_id`, `priority`, `requested_date`, `deadline_date`, `description`, `created_by`, `assigned_to`, `created_at`) VALUES
('CMD-20240310-0004', 1, 4, 'normal', '2024-03-10', '2024-03-25', 'DPE Résidence Les Lilas', 2, 4, '2024-03-10 11:20:00');

-- Commande 5 : Nouvelle (client Lyon)
INSERT INTO `orders` (`order_number`, `client_id`, `status_id`, `priority`, `requested_date`, `deadline_date`, `description`, `created_by`, `assigned_to`, `created_at`) VALUES
('CMD-20240312-0005', 2, 1, 'normal', '2024-03-12', '2024-03-30', 'DTA Immeuble Part-Dieu Lyon', 2, NULL, '2024-03-12 09:00:00');

-- =================================================================
-- ORDER EVENTS (Timeline)
-- =================================================================

-- Événements Commande 1 (Clôturée)
INSERT INTO `order_events` (`order_id`, `event_type`, `user_id`, `timestamp`, `data`) VALUES
(1, 'created', 2, '2024-01-15 09:15:00', '{"priority": "normal", "deadline": "2024-01-30"}'),
(1, 'acknowledged', 2, '2024-01-15 10:00:00', '{"ar_type": "ar_commande"}'),
(1, 'assigned', 2, '2024-01-15 11:30:00', '{"technician_id": 4, "technician_name": "Jean Dupont"}'),
(1, 'scheduled', 2, '2024-01-16 14:00:00', '{"appointment_date": "2024-01-22"}'),
(1, 'in_progress', 4, '2024-01-22 09:00:00', '{"site": "Bâtiment A - Siège Social"}'),
(1, 'report_uploaded', 4, '2024-01-24 16:30:00', '{"filename": "Rapport_DTA_BAT_A.pdf"}'),
(1, 'report_validated', 2, '2024-01-25 10:00:00', '{"validated_by": "Sophie Leroux"}'),
(1, 'completed', 2, '2024-01-25 10:05:00', NULL),
(1, 'closed', 2, '2024-01-28 16:30:00', '{"closure_notes": "Intervention terminée avec succès"}');

-- Événements Commande 2 (Rapport déposé)
INSERT INTO `order_events` (`order_id`, `event_type`, `user_id`, `timestamp`, `data`) VALUES
(2, 'created', 2, '2024-02-20 10:30:00', '{"priority": "high", "deadline": "2024-03-10"}'),
(2, 'acknowledged', 2, '2024-02-20 11:00:00', '{"ar_type": "ar_commande"}'),
(2, 'assigned', 2, '2024-02-20 14:00:00', '{"technician_id": 5, "technician_name": "Marc Bernard"}'),
(2, 'scheduled', 2, '2024-02-21 09:00:00', '{"appointment_date": "2024-02-28"}'),
(2, 'in_progress', 5, '2024-02-28 08:30:00', '{"site": "Immeuble Haussmannien - Opéra"}'),
(2, 'report_uploaded', 5, '2024-03-01 17:00:00', '{"filename": "Rapport_RAAT_Opera.pdf", "has_excel": true}');

-- Événements Commande 3 (En cours)
INSERT INTO `order_events` (`order_id`, `event_type`, `user_id`, `timestamp`, `data`) VALUES
(3, 'created', 2, '2024-03-05 14:00:00', '{"priority": "urgent", "deadline": "2024-03-15"}'),
(3, 'acknowledged', 2, '2024-03-05 14:30:00', '{"ar_type": "ar_commande"}'),
(3, 'assigned', 2, '2024-03-05 15:00:00', '{"technician_id": 6, "technician_name": "Luc Petit"}'),
(3, 'scheduled', 2, '2024-03-06 10:00:00', '{"appointment_date": "2024-03-12"}'),
(3, 'in_progress', 6, '2024-03-12 09:00:00', '{"site": "Tour Montparnasse Annexe"}');

-- Événements Commande 4 (Planifiée)
INSERT INTO `order_events` (`order_id`, `event_type`, `user_id`, `timestamp`, `data`) VALUES
(4, 'created', 2, '2024-03-10 11:20:00', '{"priority": "normal", "deadline": "2024-03-25"}'),
(4, 'acknowledged', 2, '2024-03-10 12:00:00', '{"ar_type": "ar_commande"}'),
(4, 'assigned', 2, '2024-03-10 15:30:00', '{"technician_id": 4, "technician_name": "Jean Dupont"}'),
(4, 'scheduled', 2, '2024-03-11 09:00:00', '{"appointment_date": "2024-03-18"}');

-- Événements Commande 5 (Nouvelle)
INSERT INTO `order_events` (`order_id`, `event_type`, `user_id`, `timestamp`, `data`) VALUES
(5, 'created', 2, '2024-03-12 09:00:00', '{"priority": "normal", "deadline": "2024-03-30"}');

-- =================================================================
-- INTERVENTIONS
-- =================================================================
INSERT INTO `interventions` (`order_id`, `site_id`, `technician_id`, `diagnostic_types`, `scheduled_date`, `completed_date`, `status`, `notes`) VALUES
(1, 1, 4, '["DTA"]', '2024-01-22', '2024-01-24', 'completed', 'Intervention réalisée dans les délais. Accès complet au bâtiment.'),
(2, 2, 5, '["RAAT"]', '2024-02-28', '2024-03-01', 'completed', 'Repérage avant travaux de rénovation. Quelques zones inaccessibles.'),
(3, 3, 6, '["DTA", "DPE", "ELEC"]', '2024-03-12', NULL, 'in_progress', 'Multi-diagnostics en cours. Accès prévu sur 2 jours.'),
(4, 4, 4, '["DPE"]', '2024-03-18', NULL, 'scheduled', NULL);

-- =================================================================
-- DIAGNOSTICS
-- =================================================================
INSERT INTO `diagnostics` (`site_id`, `order_id`, `intervention_id`, `diagnostic_type_id`, `date`, `status`, `criticality`, `reference_number`, `valid_until`) VALUES
-- Diagnostics issus des interventions terminées
(1, 1, 1, 1, '2024-01-24', 'anomaly', 2, 'DIAG-2024-0001', '2027-01-24'),
(2, 2, 2, 3, '2024-03-01', 'critical', 4, 'DIAG-2024-0002', '2024-06-01');

-- Diagnostics historiques (avant la plateforme)
INSERT INTO `diagnostics` (`site_id`, `diagnostic_type_id`, `date`, `status`, `criticality`, `reference_number`, `valid_until`, `excel_import_id`) VALUES
(1, 1, '2021-06-15', 'ok', 0, 'DIAG-2021-0045', '2024-06-15', NULL),
(1, 5, '2022-03-10', 'anomaly', 2, 'DIAG-2022-0123', '2032-03-10', NULL),
(2, 1, '2020-11-20', 'anomaly', 3, 'DIAG-2020-0312', '2023-11-20', NULL),
(3, 1, '2019-05-12', 'critical', 4, 'DIAG-2019-0087', '2022-05-12', NULL),
(3, 5, '2023-01-18', 'ok', 0, 'DIAG-2023-0022', '2033-01-18', NULL),
(4, 1, '2018-09-25', 'anomaly', 2, 'DIAG-2018-0455', '2021-09-25', NULL),
(5, 1, '2022-07-08', 'ok', 0, 'DIAG-2022-0267', '2025-07-08', NULL),
(5, 5, '2023-11-14', 'anomaly', 1, 'DIAG-2023-0489', '2033-11-14', NULL);

-- =================================================================
-- MESSAGES
-- =================================================================
INSERT INTO `messages` (`order_id`, `user_id`, `recipient_id`, `content`, `requires_response`, `created_at`) VALUES
(1, 7, NULL, 'Bonjour, pouvez-vous confirmer la disponibilité du site pour l\'intervention prévue ?', FALSE, '2024-01-17 10:00:00'),
(1, 2, 7, 'Bonjour M. Charron, la disponibilité est confirmée. Le technicien Jean Dupont interviendra le 22/01 à 9h.', FALSE, '2024-01-17 14:30:00'),
(2, 5, 2, 'Attention : zones au 3ème étage difficilement accessibles. Prévoir échafaudage.', TRUE, '2024-02-28 16:00:00'),
(2, 2, 5, 'Bien noté. Échafaudage commandé pour demain matin.', FALSE, '2024-02-28 16:45:00'),
(3, 6, 2, 'Début de l\'intervention. Diagnostic électrique terminé, résultats OK.', FALSE, '2024-03-12 12:00:00');

-- =================================================================
-- APPOINTMENTS
-- =================================================================
INSERT INTO `appointments` (`order_id`, `intervention_id`, `technician_id`, `site_id`, `title`, `start_datetime`, `end_datetime`, `location`, `status`, `created_by`) VALUES
(1, 1, 4, 1, 'DTA Bâtiment A', '2024-01-22 09:00:00', '2024-01-22 17:00:00', '15 Rue de Rivoli, 75001 Paris', 'completed', 2),
(2, 2, 5, 2, 'RAAT Immeuble Opéra', '2024-02-28 08:30:00', '2024-03-01 16:00:00', '8 Boulevard des Capucines, 75009 Paris', 'completed', 2),
(3, 3, 6, 3, 'Multi-diagnostics Tour Montparnasse', '2024-03-12 09:00:00', '2024-03-13 17:00:00', '25 Avenue du Maine, 75015 Paris', 'confirmed', 2),
(4, 4, 4, 4, 'DPE Résidence Les Lilas', '2024-03-18 14:00:00', '2024-03-18 17:00:00', '45 Avenue Gambetta, 75020 Paris', 'scheduled', 2);

-- =================================================================
-- SETTINGS
-- =================================================================
INSERT INTO `settings` (`category`, `key`, `value`, `data_type`, `label`, `description`) VALUES
('general', 'company_name', 'DiagPro Services', 'string', 'Nom de l\'entreprise', 'Nom affiché dans l\'interface'),
('general', 'company_address', '10 Rue des Diagnostics, 75000 Paris', 'string', 'Adresse', 'Adresse complète'),
('general', 'company_phone', '01 23 45 67 89', 'string', 'Téléphone', NULL),
('general', 'company_email', 'contact@diagpro.fr', 'string', 'Email', NULL),
('email', 'notification_order_created', '1', 'boolean', 'Notification création commande', 'Envoyer email à la création'),
('email', 'notification_report_uploaded', '1', 'boolean', 'Notification dépôt rapport', 'Envoyer email au dépôt de rapport'),
('map', 'default_map_center_lat', '48.856614', 'string', 'Latitude centre carte', NULL),
('map', 'default_map_center_lng', '2.341198', 'string', 'Longitude centre carte', NULL),
('map', 'default_zoom', '6', 'int', 'Zoom par défaut', NULL);

-- =================================================================
-- AUDIT LOG (Exemples)
-- =================================================================
INSERT INTO `audit_log` (`user_id`, `action`, `entity_type`, `entity_id`, `timestamp`, `ip_address`, `payload`) VALUES
(2, 'create', 'order', 1, '2024-01-15 09:15:00', '192.168.1.10', '{"order_number": "CMD-20240115-0001"}'),
(4, 'upload', 'report', 1, '2024-01-24 16:30:00', '192.168.1.25', '{"order_id": 1, "filename": "Rapport_DTA_BAT_A.pdf"}'),
(7, 'download', 'report', 1, '2024-01-29 10:00:00', '82.64.123.45', '{"order_id": 1}'),
(2, 'create', 'order', 2, '2024-02-20 10:30:00', '192.168.1.10', '{"order_number": "CMD-20240220-0002"}');

-- =================================================================
-- MIGRATION TRACKING
-- =================================================================
INSERT INTO `migrations` (`version`, `filename`) VALUES
(1, '001_initial_schema.sql');
