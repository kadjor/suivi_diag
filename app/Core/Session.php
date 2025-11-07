<?php

namespace Core;

/**
 * Gestionnaire de sessions
 */
class Session
{
    /**
     * Démarre la session si elle n'est pas déjà démarrée
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration sécurisée de la session
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Lax');

            session_start();
        }
    }

    /**
     * Définit une valeur en session
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de la session
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe en session
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une valeur de la session
     */
    public static function delete($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Vide toute la session
     */
    public static function flush()
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Détruit complètement la session
     */
    public static function destroy()
    {
        self::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Regénère l'ID de session (sécurité)
     */
    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Messages flash (disponibles une seule fois)
     */
    public static function flash($key, $value = null)
    {
        self::start();

        if ($value === null) {
            // Récupération du message flash
            $message = self::get("flash_{$key}");
            self::delete("flash_{$key}");
            return $message;
        } else {
            // Définition du message flash
            self::set("flash_{$key}", $value);
        }
    }

    /**
     * Message flash de succès
     */
    public static function success($message)
    {
        self::flash('success', $message);
    }

    /**
     * Message flash d'erreur
     */
    public static function error($message)
    {
        self::flash('error', $message);
    }

    /**
     * Message flash d'information
     */
    public static function info($message)
    {
        self::flash('info', $message);
    }

    /**
     * Message flash d'avertissement
     */
    public static function warning($message)
    {
        self::flash('warning', $message);
    }

    /**
     * Récupère tous les messages flash
     */
    public static function getFlashMessages()
    {
        return [
            'success' => self::flash('success'),
            'error' => self::flash('error'),
            'info' => self::flash('info'),
            'warning' => self::flash('warning')
        ];
    }

    /**
     * Token CSRF
     */
    public static function getCsrfToken()
    {
        self::start();

        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }

        return self::get('csrf_token');
    }

    /**
     * Vérifie le token CSRF
     */
    public static function verifyCsrfToken($token)
    {
        return hash_equals(self::getCsrfToken(), $token);
    }

    /**
     * Génère un nouveau token CSRF
     */
    public static function regenerateCsrfToken()
    {
        self::set('csrf_token', bin2hex(random_bytes(32)));
        return self::get('csrf_token');
    }
}
