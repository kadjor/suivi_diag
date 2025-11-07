<?php
/**
 * Configuration générale de l'application
 */

return [
    // Informations application
    'name' => 'Plateforme de Suivi de Diagnostics',
    'version' => '1.0.0',
    'build_date' => '2024-03-15',

    // URL de base (à adapter selon votre domaine)
    'url' => 'http://localhost',
    'base_path' => '/',

    // Environnement : development, production
    'environment' => 'development',

    // Timezone
    'timezone' => 'Europe/Paris',

    // Langue par défaut
    'locale' => 'fr',

    // Session
    'session' => [
        'name' => 'SUIVI_DIAG_SESSION',
        'lifetime' => 7200, // 2 heures en secondes
        'path' => '/',
        'domain' => '',
        'secure' => false, // true si HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ],

    // Sécurité
    'security' => [
        'password_min_length' => 8,
        'login_max_attempts' => 5,
        'login_lockout_duration' => 900, // 15 minutes
        'csrf_token_name' => 'csrf_token',
        'session_regenerate_interval' => 1800 // 30 minutes
    ],

    // Upload de fichiers
    'upload' => [
        'max_size' => 20971520, // 20 MB en octets
        'allowed_extensions' => ['pdf', 'xlsx', 'xls', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        'allowed_mime_types' => [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'scan_antivirus' => false, // true si ClamAV disponible
    ],

    // Email
    'email' => [
        'from_address' => 'noreply@example.com',
        'from_name' => 'Plateforme Suivi Diagnostics',
        'smtp_host' => 'localhost',
        'smtp_port' => 25,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => '', // tls, ssl, ou vide
        'smtp_auth' => false
    ],

    // Logs
    'logging' => [
        'enabled' => true,
        'level' => 'debug', // debug, info, warning, error
        'app_log' => __DIR__ . '/../logs/app.log',
        'error_log' => __DIR__ . '/../logs/error.log',
        'audit_log' => __DIR__ . '/../logs/audit.log',
        'import_log' => __DIR__ . '/../logs/import.log',
        'max_size' => 10485760, // 10 MB
        'rotation' => true
    ],

    // Pagination
    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100
    ],

    // Cartographie
    'map' => [
        'default_latitude' => 46.603354,
        'default_longitude' => 1.888334,
        'default_zoom' => 6,
        'geocoding_service' => 'nominatim', // nominatim (gratuit), google, mapbox
        'geocoding_api_key' => '', // Si service payant
    ],

    // Chemins
    'paths' => [
        'uploads' => __DIR__ . '/../public/uploads',
        'reports' => __DIR__ . '/../public/uploads/reports',
        'attachments' => __DIR__ . '/../public/uploads/attachments',
        'acknowledgments' => __DIR__ . '/../public/uploads/acknowledgments',
        'imports' => __DIR__ . '/../public/uploads/imports',
        'temp' => __DIR__ . '/../public/uploads/temp',
        'logs' => __DIR__ . '/../logs',
        'libs' => __DIR__ . '/../libs'
    ],

    // Formats de date
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y H:i',
    'time_format' => 'H:i',

    // Excel import
    'excel_import' => [
        'batch_size' => 100, // Traitement par batch
        'max_rows' => 10000,
        'timeout' => 300, // 5 minutes
    ],

    // Notifications
    'notifications' => [
        'order_created' => true,
        'order_assigned' => true,
        'appointment_scheduled' => true,
        'report_uploaded' => true,
        'order_completed' => true,
        'order_closed' => true,
    ]
];
