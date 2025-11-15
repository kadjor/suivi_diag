<?php
// Script pour corriger les permissions du rôle client
// Exécute la migration 013

require_once __DIR__ . '/../bootstrap.php';

use Core\Auth;
use Models\Role;

// Vérifier si l'utilisateur est admin
if (!Auth::check() || !Auth::can('manage_settings')) {
    die("Accès refusé - Réservé aux administrateurs");
}

echo "<h1>Correction des permissions du rôle client</h1>";
echo "<hr>";

$roleModel = new Role();

// Récupérer le rôle client
$clientRole = $roleModel->queryOne("SELECT * FROM roles WHERE name = 'client'");

if (!$clientRole) {
    die("Erreur: Rôle client introuvable");
}

echo "<h2>État AVANT correction</h2>";
echo "<pre>";
echo "ID: " . $clientRole['id'] . "\n";
echo "Name: " . $clientRole['name'] . "\n";
echo "Permissions actuelles:\n";
echo $clientRole['permissions'] . "\n";
echo "</pre>";

// Définir les nouvelles permissions au format RBAC
$newPermissions = [
    'orders' => [
        'create' => true,
        'read' => 'own'
    ],
    'sites' => [
        'read' => 'own'
    ],
    'reports' => [
        'read' => 'own',
        'download' => true
    ],
    'messages' => [
        'create' => true,
        'read' => 'own'
    ],
    'map' => [
        'read' => true
    ]
];

// Mettre à jour les permissions
$success = $roleModel->update($clientRole['id'], [
    'permissions' => json_encode($newPermissions)
]);

if ($success) {
    echo "<h2 style='color: green;'>✅ Permissions mises à jour avec succès !</h2>";

    // Récupérer le rôle mis à jour
    $updatedRole = $roleModel->find($clientRole['id']);

    echo "<h2>État APRÈS correction</h2>";
    echo "<pre>";
    echo "Permissions:\n";
    echo $updatedRole['permissions'] . "\n\n";
    echo "Décodé:\n";
    print_r(json_decode($updatedRole['permissions'], true));
    echo "</pre>";

    // Effacer le cache de session pour tous les utilisateurs clients
    echo "<h2>Effacement du cache de session</h2>";
    echo "<p>⚠️ Les clients devront se reconnecter pour que les nouvelles permissions soient prises en compte.</p>";

} else {
    echo "<h2 style='color: red;'>❌ Erreur lors de la mise à jour</h2>";
}

echo "<hr>";
echo "<p><a href='/debug-permissions.php'>Vérifier les permissions</a></p>";
echo "<p><a href='/dashboard'>Retour au dashboard</a></p>";
