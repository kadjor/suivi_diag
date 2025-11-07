<?php

namespace Core;

/**
 * Gestionnaire de routes
 */
class Router
{
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * Enregistre une route GET
     */
    public function get($uri, $handler)
    {
        $this->routes['GET'][$uri] = $handler;
    }

    /**
     * Enregistre une route POST
     */
    public function post($uri, $handler)
    {
        $this->routes['POST'][$uri] = $handler;
    }

    /**
     * Distribue la requête vers le bon contrôleur
     */
    public function dispatch($uri, $method)
    {
        // Normalisation de l'URI
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // Recherche de route exacte
        if (isset($this->routes[$method][$uri])) {
            return $this->executeHandler($this->routes[$method][$uri], []);
        }

        // Recherche de route avec paramètres
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->routeToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Supprimer le match complet
                return $this->executeHandler($handler, $matches);
            }
        }

        // Route non trouvée
        $this->handleNotFound();
    }

    /**
     * Convertit une route en regex
     */
    private function routeToRegex($route)
    {
        // Remplace {id} par un pattern de capture
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Exécute un handler (contrôleur@méthode)
     */
    private function executeHandler($handler, $params)
    {
        // Format: "Controllers\OrderController@index"
        list($controllerClass, $method) = explode('@', $handler);

        // Vérifier que la classe existe
        $fullClass = '\\' . ltrim($controllerClass, '\\');
        if (!class_exists($fullClass)) {
            throw new \Exception("Controller not found: {$fullClass}");
        }

        // Instancier le contrôleur
        $controller = new $fullClass();

        // Vérifier que la méthode existe
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in {$fullClass}");
        }

        // Exécuter la méthode avec les paramètres
        return call_user_func_array([$controller, $method], $params);
    }

    /**
     * Gère les routes non trouvées
     */
    private function handleNotFound()
    {
        http_response_code(404);
        if (config('environment') === 'production') {
            if (file_exists(APP_PATH . '/Views/errors/404.php')) {
                require APP_PATH . '/Views/errors/404.php';
            } else {
                echo '404 - Page non trouvée';
            }
        } else {
            echo '404 - Route non trouvée : ' . $_SERVER['REQUEST_URI'];
        }
        exit;
    }
}
