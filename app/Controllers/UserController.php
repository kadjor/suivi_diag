<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\User;
use Helpers\Validator;

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
        $this->userModel = new User();
    }

    public function index()
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $users = $this->userModel->getAll();
        View::render('users.index', ['users' => $users]);
    }

    public function profile()
    {
        $user = Auth::user();
        View::render('users.profile', ['user' => $user]);
    }

    public function updateProfile()
    {
        $userId = Auth::id();
        $data = $_POST;

        $validator = new Validator($data);
        $validator->required(['first_name', 'last_name', 'email']);
        $validator->email('email');

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/profile');
        }

        try {
            $this->userModel->update($userId, [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null
            ]);

            Session::flash('success', 'Profil mis à jour');
            View::redirect('/profile');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour');
            View::redirect('/profile');
        }
    }

    public function changePassword()
    {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            Session::flash('error', 'Tous les champs sont requis');
            View::redirect('/profile');
        }

        if ($new !== $confirm) {
            Session::flash('error', 'Les mots de passe ne correspondent pas');
            View::redirect('/profile');
        }

        $user = $this->userModel->find(Auth::id());

        if (!password_verify($current, $user['password'])) {
            Session::flash('error', 'Mot de passe actuel incorrect');
            View::redirect('/profile');
        }

        try {
            $this->userModel->update(Auth::id(), [
                'password' => password_hash($new, PASSWORD_DEFAULT)
            ]);

            Session::flash('success', 'Mot de passe modifié');
            View::redirect('/profile');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la modification');
            View::redirect('/profile');
        }
    }
}
