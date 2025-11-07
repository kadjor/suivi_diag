<?php

namespace Core;

/**
 * Gestion de l'authentification
 */
class Auth
{
    /**
     * Vérifie si un utilisateur est connecté
     */
    public static function check()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Récupère l'utilisateur connecté
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        // Cache l'utilisateur dans la session pour éviter les requêtes multiples
        if (!isset($_SESSION['user_data'])) {
            $userModel = new \Models\User();
            $user = $userModel->queryOne(
                "SELECT u.*, r.name as role_name, r.label as role_label, r.permissions as role_permissions
                 FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 WHERE u.id = ?",
                [$_SESSION['user_id']]
            );
            $_SESSION['user_data'] = $user;
        }

        return $_SESSION['user_data'];
    }

    /**
     * Récupère l'ID de l'utilisateur connecté
     */
    public static function id()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Connecte un utilisateur
     */
    public static function login($user, $remember = false)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;

        // Régénération de l'ID de session pour sécurité
        session_regenerate_id(true);

        // Mise à jour de la date de dernière connexion
        $userModel = new \Models\User();
        $userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s'),
            'login_attempts' => 0,
            'locked_until' => null
        ]);

        // Log audit
        self::logAudit('login', null, null, 'success');

        // Remember me (optionnel, à implémenter si besoin)
        if ($remember) {
            // Créer un token de remember me
            // À implémenter si nécessaire
        }

        return true;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public static function logout()
    {
        self::logAudit('logout', null, null, 'success');

        // Suppression des données de session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_data']);

        // Destruction complète de la session
        session_destroy();

        return true;
    }

    /**
     * Tente d'authentifier un utilisateur
     */
    public static function attempt($username, $password, $remember = false)
    {
        $userModel = new \Models\User();
        $user = $userModel->queryOne(
            "SELECT u.*, r.name as role_name, r.label as role_label, r.permissions as role_permissions
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.username = ?",
            [$username]
        );

        if (!$user) {
            self::logAudit('login', null, null, 'failure');
            return false;
        }

        // Vérifier si le compte est verrouillé
        if (self::isLocked($user)) {
            return false;
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user['password_hash'])) {
            self::incrementLoginAttempts($user['id']);
            self::logAudit('login', null, null, 'failure');
            return false;
        }

        // Vérifier si le compte est actif
        if (!$user['active']) {
            return false;
        }

        // Connexion réussie - mettre en session
        self::login($user, $remember);

        return true;
    }

    /**
     * Vérifie si un compte est verrouillé
     */
    private static function isLocked($user)
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        $lockExpiry = strtotime($user['locked_until']);
        $now = time();

        if ($now < $lockExpiry) {
            return true;
        }

        // Le verrouillage a expiré, on le réinitialise
        $userModel = new \Models\User();
        $userModel->update($user['id'], [
            'login_attempts' => 0,
            'locked_until' => null
        ]);

        return false;
    }

    /**
     * Incrémente les tentatives de connexion et verrouille si nécessaire
     */
    private static function incrementLoginAttempts($userId)
    {
        $userModel = new \Models\User();
        $user = $userModel->find($userId);

        $attempts = ($user['login_attempts'] ?? 0) + 1;
        $maxAttempts = config('security.login_max_attempts', 5);

        $updateData = ['login_attempts' => $attempts];

        if ($attempts >= $maxAttempts) {
            $lockoutDuration = config('security.login_lockout_duration', 900); // 15 min
            $updateData['locked_until'] = date('Y-m-d H:i:s', time() + $lockoutDuration);
        }

        $userModel->update($userId, $updateData);
    }

    /**
     * Vérifie si l'utilisateur a une permission
     */
    public static function can($permission)
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        // L'admin a tous les droits
        if ($user['role_id'] == 1) {
            return true;
        }

        // Récupérer les permissions du rôle
        $rbac = new RBAC();
        return $rbac->hasPermission($user['role_id'], $permission);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public static function hasRole($roleName)
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $roleModel = new \Models\Role();
        $role = $roleModel->find($user['role_id']);

        return $role && $role['name'] === $roleName;
    }

    /**
     * Vérifie si l'utilisateur est un client
     */
    public static function isClient()
    {
        return self::hasRole('client');
    }

    /**
     * Récupère l'ID client de l'utilisateur connecté (si client)
     */
    public static function clientId()
    {
        $user = self::user();
        return $user['client_id'] ?? null;
    }

    /**
     * Log une action dans l'audit
     */
    public static function logAudit($action, $entityType = null, $entityId = null, $result = 'success', $payload = null)
    {
        $auditModel = new \Models\AuditLog();
        $auditModel->log(
            self::id(),
            $action,
            $entityType,
            $entityId,
            $result,
            $payload
        );
    }

    /**
     * Régénère l'ID de session périodiquement
     */
    public static function refreshSession()
    {
        $interval = config('security.session_regenerate_interval', 1800); // 30 min

        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = time();
            return;
        }

        if (time() - $_SESSION['last_regenerate'] > $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }
}
