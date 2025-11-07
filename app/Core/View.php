<?php

namespace Core;

/**
 * Moteur de templates simple
 */
class View
{
    private static $data = [];
    private static $layout = 'main';

    /**
     * Définit le layout à utiliser
     */
    public static function setLayout($layout)
    {
        self::$layout = $layout;
    }

    /**
     * Rend une vue avec des données
     */
    public static function render($view, $data = [])
    {
        self::$data = array_merge(self::$data, $data);

        // Extrait les variables pour les rendre disponibles dans la vue
        extract(self::$data);

        // Capture le contenu de la vue
        ob_start();
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View not found: {$viewPath}");
        }

        $content = ob_get_clean();

        // Si pas de layout, retourne juste le contenu
        if (self::$layout === null) {
            echo $content;
            return;
        }

        // Sinon, charge le layout
        $layoutPath = __DIR__ . '/../Views/layouts/' . self::$layout . '.php';

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content; // Fallback si le layout n'existe pas
        }
    }

    /**
     * Rend une vue sans layout (pour AJAX)
     */
    public static function renderPartial($view, $data = [])
    {
        self::$layout = null;
        self::render($view, $data);
    }

    /**
     * Retourne le contenu d'une vue sans l'afficher
     */
    public static function fetch($view, $data = [])
    {
        ob_start();
        self::renderPartial($view, $data);
        return ob_get_clean();
    }

    /**
     * Rend un JSON
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirige vers une URL
     */
    public static function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Définit des données globales pour toutes les vues
     */
    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            self::$data = array_merge(self::$data, $key);
        } else {
            self::$data[$key] = $value;
        }
    }

    /**
     * Échappe les caractères HTML
     */
    public static function e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Affiche une valeur échappée
     */
    public static function escape($value)
    {
        echo self::e($value);
    }
}
