<?php
/**
 * Paramètres métier de l'application
 */

return [
    // Statuts des commandes
    'order_statuses' => [
        'draft' => 'Brouillon',
        'pending' => 'En attente AR',
        'confirmed' => 'AR reçu',
        'planned' => 'Planifiée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée'
    ],

    // Types de diagnostics
    'diagnostic_types' => [
        'amiante' => 'Amiante',
        'dpe' => 'DPE (Performance Énergétique)',
        'plomb' => 'Plomb (CREP)',
        'termites' => 'Termites',
        'gaz' => 'Gaz',
        'electricite' => 'Électricité',
        'erp' => 'ERP (État des Risques)',
        'mesurage' => 'Mesurage Loi Carrez',
        'assainissement' => 'Assainissement'
    ],

    // Codes couleur pour les types de diagnostics (cartographie)
    'diagnostic_colors' => [
        'amiante' => '#e74c3c',        // Rouge
        'dpe' => '#2ecc71',            // Vert
        'plomb' => '#9b59b6',          // Violet
        'termites' => '#f39c12',       // Orange
        'gaz' => '#3498db',            // Bleu
        'electricite' => '#e67e22',    // Orange foncé
        'erp' => '#1abc9c',            // Turquoise
        'mesurage' => '#34495e',       // Gris foncé
        'assainissement' => '#95a5a6'  // Gris
    ],

    // Types de bâtiments
    'building_types' => [
        'residential' => 'Résidentiel',
        'commercial' => 'Commercial',
        'industrial' => 'Industriel',
        'public' => 'Public'
    ],

    // Priorités des commandes
    'priorities' => [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
        'urgent' => 'Urgente'
    ],

    // Statuts des interventions
    'intervention_statuses' => [
        'scheduled' => 'Planifiée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée'
    ],

    // Types de fichiers autorisés pour les rapports
    'allowed_report_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'max_report_size' => 10485760, // 10 MB

    // Types de fichiers autorisés pour les pièces jointes
    'allowed_attachment_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
    'max_attachment_size' => 5242880, // 5 MB

    // Pagination
    'items_per_page' => 20,
    'map_markers_limit' => 500,

    // Conservation des données (RGPD)
    'data_retention' => [
        'orders_completed' => 365 * 10,      // 10 ans (obligations légales diagnostics)
        'orders_cancelled' => 365 * 2,       // 2 ans
        'audit_logs' => 365 * 5,             // 5 ans
        'user_sessions' => 30,               // 30 jours
        'temp_files' => 7                    // 7 jours
    ],

    // Notifications email
    'email_notifications' => [
        'order_created' => true,
        'order_confirmed' => true,
        'order_assigned' => true,
        'order_completed' => true,
        'report_uploaded' => true,
        'message_received' => true,
        'appointment_reminder' => true
    ],

    // Délai de rappel pour les rendez-vous (heures avant)
    'appointment_reminder_hours' => 24,

    // Formats d'export
    'export_formats' => ['xlsx', 'csv', 'pdf'],

    // Configuration de la cartographie
    'map_config' => [
        'default_center' => [48.8566, 2.3522],  // Paris par défaut
        'default_zoom' => 13,
        'max_zoom' => 18,
        'min_zoom' => 5,
        'cluster_radius' => 80,
        'show_satellite' => true
    ],

    // Codes postaux français (validation basique)
    'postal_code_pattern' => '/^[0-9]{5}$/',

    // Rôles système (non modifiables)
    'system_roles' => [
        'admin' => 'Administrateur',
        'secretary' => 'Secrétariat',
        'technician' => 'Technicien',
        'client' => 'Client'
    ],

    // Permissions par rôle
    'role_permissions' => [
        'admin' => [
            'manage_users',
            'manage_clients',
            'manage_orders',
            'manage_sites',
            'manage_diagnostics',
            'view_audit_logs',
            'export_data',
            'manage_settings'
        ],
        'secretary' => [
            'create_orders',
            'update_orders',
            'assign_orders',
            'manage_appointments',
            'view_clients',
            'view_sites',
            'send_messages'
        ],
        'technician' => [
            'view_assigned_orders',
            'update_interventions',
            'upload_reports',
            'import_diagnostics',
            'send_messages'
        ],
        'client' => [
            'view_own_orders',
            'view_own_sites',
            'view_map',
            'download_reports',
            'send_messages',
            'create_orders'
        ]
    ],

    // Durée de validité des diagnostics (en années)
    'diagnostic_validity' => [
        'amiante' => null,              // Illimitée (si négatif)
        'dpe' => 10,
        'plomb' => null,                // Illimitée (si négatif)
        'termites' => 6 / 12,           // 6 mois
        'gaz' => 3,
        'electricite' => 3,
        'erp' => 6 / 12,                // 6 mois
        'mesurage' => null,             // Illimitée
        'assainissement' => 3
    ],

    // Jours fériés français (format MM-DD)
    'french_holidays' => [
        '01-01', // Nouvel An
        '05-01', // Fête du Travail
        '05-08', // Victoire 1945
        '07-14', // Fête Nationale
        '08-15', // Assomption
        '11-01', // Toussaint
        '11-11', // Armistice 1918
        '12-25'  // Noël
    ],

    // Horaires de travail par défaut
    'working_hours' => [
        'start' => '08:00',
        'end' => '18:00',
        'lunch_break_start' => '12:00',
        'lunch_break_end' => '14:00'
    ],

    // Jours ouvrés (1 = lundi, 7 = dimanche)
    'working_days' => [1, 2, 3, 4, 5], // Lundi à vendredi

    // Durée moyenne des interventions par type (en heures)
    'intervention_duration' => [
        'amiante' => 4,
        'dpe' => 3,
        'plomb' => 2,
        'termites' => 2,
        'gaz' => 1.5,
        'electricite' => 1.5,
        'erp' => 1,
        'mesurage' => 2,
        'assainissement' => 2
    ]
];
