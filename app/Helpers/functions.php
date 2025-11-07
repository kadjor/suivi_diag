<?php
/**
 * Fonctions helper globales
 */

/**
 * Récupère une valeur de configuration
 */
function config($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = require CONFIG_PATH . '/app.php';
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Échappe une chaîne pour affichage HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Génère une URL
 */
function url($path = '') {
    $base = rtrim(config('url'), '/') . rtrim(config('base_path'), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Génère une URL d'asset
 */
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirige vers une URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Retourne à la page précédente
 */
function back() {
    redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
}

/**
 * Affiche une vue
 */
function view($view, $data = []) {
    $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
    if (!file_exists($viewPath)) {
        throw new Exception("Vue non trouvée : {$view}");
    }

    extract($data);
    require $viewPath;
}

/**
 * Retourne une réponse JSON
 */
function json($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Récupère un paramètre POST
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Récupère un paramètre GET
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Récupère un paramètre de requête (POST ou GET)
 */
function request($key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Vérifie si la requête est POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si la requête est AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Génère un token CSRF
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Génère un champ input pour le token CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="' . config('security.csrf_token_name') . '" value="' . e(csrf_token()) . '">';
}

/**
 * Vérifie le token CSRF
 */
function csrf_verify() {
    $token = post(config('security.csrf_token_name'));
    return $token && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Définit un message flash
 */
function flash($key, $message = null) {
    if ($message === null) {
        // Récupération
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    // Définition
    $_SESSION['flash'][$key] = $message;
}

/**
 * Récupère l'utilisateur connecté
 */
function auth() {
    return Core\Auth::user();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isAuthenticated() {
    return Core\Auth::check();
}

/**
 * Vérifie si l'utilisateur a une permission
 */
function can($permission) {
    return Core\Auth::can($permission);
}

/**
 * Formatte une date
 */
function formatDate($date, $format = null) {
    if (empty($date)) return '';
    $format = $format ?? config('date_format');
    return date($format, strtotime($date));
}

/**
 * Formatte une date et heure
 */
function formatDateTime($datetime, $format = null) {
    if (empty($datetime)) return '';
    $format = $format ?? config('datetime_format');
    return date($format, strtotime($datetime));
}

/**
 * Formatte une taille de fichier
 */
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

/**
 * Génère un UUID v4
 */
function uuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Log une entrée
 */
function log_message($message, $level = 'info', $file = null) {
    if (!config('logging.enabled')) return;

    $file = $file ?? config('logging.app_log');
    $timestamp = date('Y-m-d H:i:s');
    $log = "[{$timestamp}] [{$level}] {$message}\n";

    file_put_contents($file, $log, FILE_APPEND);
}

/**
 * Traduction (i18n) - pour évolution future
 */
function __($key, $replacements = []) {
    // Pour l'instant, retourne la clé (à implémenter avec fichiers de langue)
    $translation = $key;

    foreach ($replacements as $search => $replace) {
        $translation = str_replace(':' . $search, $replace, $translation);
    }

    return $translation;
}

/**
 * Débug : dump et die
 */
function dd(...$vars) {
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

/**
 * Paginer un tableau de résultats
 */
function paginate($items, $perPage = null, $currentPage = 1) {
    $perPage = $perPage ?? config('pagination.per_page');
    $total = count($items);
    $lastPage = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $lastPage));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'data' => array_slice($items, $offset, $perPage),
        'current_page' => $currentPage,
        'last_page' => $lastPage,
        'per_page' => $perPage,
        'total' => $total,
        'from' => $offset + 1,
        'to' => min($offset + $perPage, $total)
    ];
}

/**
 * Nettoie un nom de fichier
 */
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return substr($filename, 0, 255);
}

/**
 * Génère un nom de fichier unique
 */
function uniqueFilename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uuid() . '_' . time() . '.' . $ext;
}

/**
 * Obtient l'adresse IP du client
 */
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Obtient le user agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Vérifie si un email est valide
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Génère un select HTML avec options
 */
function selectOptions($options, $selected = null, $valueKey = 'id', $labelKey = 'name') {
    $html = '';
    foreach ($options as $option) {
        $value = is_array($option) ? $option[$valueKey] : $option;
        $label = is_array($option) ? $option[$labelKey] : $option;
        $selectedAttr = ($value == $selected) ? ' selected' : '';
        $html .= '<option value="' . e($value) . '"' . $selectedAttr . '>' . e($label) . '</option>';
    }
    return $html;
}

/**
 * Tronque un texte
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}
