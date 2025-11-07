<?php
use Core\Auth;
$user = Auth::user();
?>
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="/dashboard">
                <h1>📋 Suivi Diagnostics</h1>
            </a>
        </div>

        <div class="header-right">
            <div class="user-menu">
                <span class="user-name"><?= htmlspecialchars($user['first_name'] ?? 'Utilisateur') ?></span>
                <span class="user-role"><?= htmlspecialchars($user['role_name'] ?? '') ?></span>
                <a href="/logout" class="btn-logout">Déconnexion</a>
            </div>
        </div>
    </div>
</header>
