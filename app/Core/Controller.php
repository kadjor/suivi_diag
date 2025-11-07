<?php

namespace Core;

/**
 * Contrôleur de base
 */
class Controller
{
    /**
     * Affiche une vue
     */
    protected function view($view, $data = [])
    {
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("Vue non trouvée : {$view}");
        }

        // Extraction des données pour les rendre disponibles dans la vue
        extract($data);

        // Capture du contenu de la vue
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Si la vue utilise un layout
        if (isset($data['layout']) && $data['layout']) {
            $layout = $data['layout'];
            $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';

            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Retourne une réponse JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirige vers une URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Vérifie l'authentification
     */
    protected function requireAuth()
    {
        if (!Auth::check()) {
            flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect(url('/login'));
        }
    }

    /**
     * Vérifie une permission
     */
    protected function requirePermission($permission)
    {
        $this->requireAuth();

        if (!Auth::can($permission)) {
            http_response_code(403);
            $this->view('errors.403', [
                'layout' => 'main',
                'title' => 'Accès refusé'
            ]);
            exit;
        }
    }

    /**
     * Vérifie le token CSRF
     */
    protected function verifyCsrf()
    {
        if (!csrf_verify()) {
            http_response_code(403);
            $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ], 403);
        }
    }

    /**
     * Valide les données d'une requête
     */
    protected function validate($data, $rules)
    {
        $validator = new \Helpers\Validator($data, $rules);

        if ($validator->fails()) {
            if (isAjax()) {
                $this->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            } else {
                flash('errors', $validator->errors());
                flash('old_input', $data);
                back();
            }
        }

        return $validator->validated();
    }
}
