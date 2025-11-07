<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use Core\View;
use Models\User;
use Helpers\Validator;

/**
 * Contrôleur d'authentification
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Affiche la page de connexion
     */
    public function showLogin()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            View::redirect('/dashboard');
        }

        View::setLayout('auth');
        View::render('auth.login');
    }

    /**
     * Traite la connexion
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/login');
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validation
        $errors = [];

        if (empty($username)) {
            $errors[] = "Le nom d'utilisateur est requis";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis";
        }

        if (!empty($errors)) {
            Session::flash('error', implode(', ', $errors));
            View::redirect('/login');
        }

        // Tentative de connexion
        if (Auth::attempt($username, $password, $remember)) {
            // Rediriger selon le rôle
            $user = Auth::user();

            log_message("User logged in: {$user['username']}", 'info');

            Session::flash('success', "Bienvenue {$user['first_name']} !");
            View::redirect('/dashboard');
        } else {
            log_message("Failed login attempt for: {$username}", 'warning');
            Session::flash('error', 'Identifiants incorrects');
            View::redirect('/login');
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            log_message("User logged out: {$user['username']}", 'info');
        }

        Auth::logout();
        Session::flash('info', 'Vous êtes déconnecté');
        View::redirect('/login');
    }

    /**
     * Affiche la page d'inscription (si activé)
     */
    public function showRegister()
    {
        $config = require __DIR__ . '/../../config/app.php';

        if (!($config['allow_registration'] ?? false)) {
            Session::flash('error', "L'inscription n'est pas autorisée");
            View::redirect('/login');
        }

        View::setLayout('auth');
        View::render('auth.register');
    }

    /**
     * Traite l'inscription
     */
    public function register()
    {
        $config = require __DIR__ . '/../../config/app.php';

        if (!($config['allow_registration'] ?? false)) {
            Session::flash('error', "L'inscription n'est pas autorisée");
            View::redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/register');
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? ''
        ];

        // Validation
        $validator = new Validator($data);

        $validator->required(['username', 'email', 'password', 'password_confirm', 'first_name', 'last_name']);
        $validator->email('email');
        $validator->minLength('username', 3);
        $validator->minLength('password', 8);
        $validator->match('password', 'password_confirm', 'Les mots de passe ne correspondent pas');

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/register');
        }

        // Vérifier si l'utilisateur existe déjà
        if ($this->userModel->findByUsername($data['username'])) {
            Session::flash('error', "Ce nom d'utilisateur existe déjà");
            View::redirect('/register');
        }

        if ($this->userModel->findByEmail($data['email'])) {
            Session::flash('error', 'Cette adresse email est déjà utilisée');
            View::redirect('/register');
        }

        // Créer l'utilisateur
        try {
            $userId = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role_id' => 4, // Client par défaut
                'is_active' => 1
            ]);

            log_message("New user registered: {$data['username']}", 'info');

            // Connexion automatique
            Auth::attempt($data['username'], $data['password']);

            Session::flash('success', 'Votre compte a été créé avec succès !');
            View::redirect('/dashboard');

        } catch (\Exception $e) {
            log_message("Registration failed: " . $e->getMessage(), 'error');
            Session::flash('error', "Erreur lors de la création du compte");
            View::redirect('/register');
        }
    }

    /**
     * Affiche la page de mot de passe oublié
     */
    public function showForgotPassword()
    {
        View::setLayout('auth');
        View::render('auth.forgot_password');
    }

    /**
     * Traite la demande de réinitialisation
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/forgot-password');
        }

        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            Session::flash('error', "L'adresse email est requise");
            View::redirect('/forgot-password');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // Ne pas révéler si l'email existe ou non
            Session::flash('info', 'Si cette adresse email existe, vous recevrez un lien de réinitialisation');
            View::redirect('/forgot-password');
        }

        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);

        // Envoyer l'email (TODO: implémenter l'envoi d'email)
        $resetLink = $this->getBaseUrl() . "/reset-password?token={$token}";

        log_message("Password reset requested for: {$email}", 'info');

        Session::flash('info', 'Si cette adresse email existe, vous recevrez un lien de réinitialisation');
        View::redirect('/forgot-password');
    }

    /**
     * Affiche la page de réinitialisation
     */
    public function showResetPassword()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Session::flash('error', 'Token invalide');
            View::redirect('/login');
        }

        // Vérifier le token
        $user = $this->userModel->findByResetToken($token);

        if (!$user || strtotime($user['reset_token_expiry']) < time()) {
            Session::flash('error', 'Token invalide ou expiré');
            View::redirect('/login');
        }

        View::setLayout('auth');
        View::render('auth.reset_password', ['token' => $token]);
    }

    /**
     * Traite la réinitialisation du mot de passe
     */
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/login');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token) || empty($password) || empty($passwordConfirm)) {
            Session::flash('error', 'Tous les champs sont requis');
            View::redirect("/reset-password?token={$token}");
        }

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Les mots de passe ne correspondent pas');
            View::redirect("/reset-password?token={$token}");
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Le mot de passe doit contenir au moins 8 caractères');
            View::redirect("/reset-password?token={$token}");
        }

        // Vérifier le token
        $user = $this->userModel->findByResetToken($token);

        if (!$user || strtotime($user['reset_token_expiry']) < time()) {
            Session::flash('error', 'Token invalide ou expiré');
            View::redirect('/login');
        }

        // Mettre à jour le mot de passe
        $this->userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);

        log_message("Password reset completed for: {$user['username']}", 'info');

        Session::flash('success', 'Votre mot de passe a été réinitialisé avec succès');
        View::redirect('/login');
    }

    /**
     * Récupère l'URL de base
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}";
    }
}
