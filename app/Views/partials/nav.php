<?php
use Core\Auth;
$user = Auth::user();
$role = $user['role_name'] ?? '';
?>
<nav class="main-nav">
    <ul>
        <li><a href="/dashboard" class="nav-link">Tableau de bord</a></li>

        <?php if ($role === 'admin' || $role === 'secretariat'): ?>
            <li><a href="/orders" class="nav-link">Commandes</a></li>
            <li><a href="/sites" class="nav-link">Sites</a></li>
            <li><a href="/sites/import" class="nav-link">📥 Import Patrimoine</a></li>
            <li><a href="/clients" class="nav-link">Clients</a></li>
        <?php endif; ?>

        <?php if ($role === 'client'): ?>
            <li><a href="/orders" class="nav-link">Mes Commandes</a></li>
            <li><a href="/sites" class="nav-link">Mes Sites</a></li>
        <?php endif; ?>

        <li><a href="/map" class="nav-link">Cartographie</a></li>

        <?php if ($role !== 'client'): ?>
            <li><a href="/calendar" class="nav-link">Planning</a></li>
        <?php endif; ?>

        <li><a href="/reports" class="nav-link">Rapports</a></li>
        <li><a href="/messages" class="nav-link">Messages</a></li>

        <?php if ($role === 'admin'): ?>
            <li><a href="/admin" class="nav-link">Administration</a></li>
            <li><a href="/deploy" class="nav-link">🚀 Déploiement</a></li>
        <?php endif; ?>

        <li><a href="/profile" class="nav-link">Mon Profil</a></li>
    </ul>
</nav>
