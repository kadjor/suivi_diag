<?php
/**
 * Configuration Email
 * Copier ce fichier vers email.php et configurer les paramètres
 */

return [
    // Configuration SMTP
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls', // 'tls' ou 'ssl'
    'smtp_username' => 'votre-email@example.com',
    'smtp_password' => 'votre-mot-de-passe',

    // Expéditeur par défaut
    'from_email' => 'noreply@d-evidences.fr',
    'from_name' => 'D-Evidences',

    // Email de l'administrateur (pour notifications système)
    'admin_email' => 'admin@d-evidences.fr',

    // Activer/désactiver les emails
    'enabled' => true,

    // Mode debug (affiche les erreurs détaillées)
    'debug' => false
];
