<?php
// Page de debug pour vérifier les permissions client
// À supprimer après résolution du problème

require_once __DIR__ . '/../bootstrap.php';

use Core\Auth;
use Models\Role;
use Core\RBAC;

// Vérifier si l'utilisateur est connecté
if (!Auth::check()) {
    die("Vous devez être connecté pour voir cette page");
}

$user = Auth::user();
$roleModel = new Role();
$rbac = new RBAC();

// Récupérer le rôle
$role = $roleModel->find($user['role_id']);

echo "<h1>Debug Permissions - Utilisateur: " . htmlspecialchars($user['username']) . "</h1>";
echo "<hr>";

echo "<h2>Informations utilisateur</h2>";
echo "<pre>";
echo "ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Email: " . $user['email'] . "\n";
echo "Role ID: " . $user['role_id'] . "\n";
echo "Role Name: " . $user['role_name'] . "\n";
echo "Client ID: " . ($user['client_id'] ?? 'NULL') . "\n";
echo "</pre>";

echo "<h2>Informations du rôle</h2>";
echo "<pre>";
echo "ID: " . $role['id'] . "\n";
echo "Name: " . $role['name'] . "\n";
echo "Label: " . $role['label'] . "\n";
echo "Permissions JSON:\n";
echo $role['permissions'] . "\n";
echo "</pre>";

echo "<h2>Permissions décodées</h2>";
echo "<pre>";
$permissions = json_decode($role['permissions'], true);
print_r($permissions);
echo "</pre>";

echo "<h2>Permissions depuis config/settings.php</h2>";
echo "<pre>";
$configPermissions = config("settings.role_permissions.{$role['name']}", []);
print_r($configPermissions);
echo "</pre>";

echo "<h2>Tests de permissions</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Permission</th><th>Auth::can()</th><th>RBAC->hasPermission()</th></tr>";

$testsPermissions = [
    'create_orders',
    'orders.create',
    'manage_orders',
    'view_own_orders',
    'orders.read'
];

foreach ($testsPermissions as $perm) {
    $can = Auth::can($perm);
    $rbacCan = $rbac->hasPermission($user['role_id'], $perm);

    echo "<tr>";
    echo "<td>" . htmlspecialchars($perm) . "</td>";
    echo "<td style='background: " . ($can ? 'lightgreen' : 'lightcoral') . "'>" . ($can ? 'OUI' : 'NON') . "</td>";
    echo "<td style='background: " . ($rbacCan ? 'lightgreen' : 'lightcoral') . "'>" . ($rbacCan ? 'OUI' : 'NON') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><a href='/orders/create'>Tester /orders/create</a></p>";
echo "<p><a href='/dashboard'>Retour au dashboard</a></p>";
