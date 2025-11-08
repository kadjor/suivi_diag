<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\User;
use Models\Role;
use Models\Client;
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

    public function create()
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $roleModel = new Role();
        $clientModel = new Client();

        View::render('users.create', [
            'roles' => $roleModel->all(),
            'clients' => $clientModel->getAll()
        ]);
    }

    public function store()
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $data = $_POST;

        $validator = new Validator($data);
        $validator->required(['username', 'email', 'password', 'first_name', 'last_name', 'role_id']);
        $validator->email('email');

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/users/create');
        }

        // Vérifier unicité username et email
        if ($this->userModel->findByUsername($data['username'])) {
            Session::flash('error', 'Ce nom d\'utilisateur existe déjà');
            View::redirect('/users/create');
        }

        if ($this->userModel->findByEmail($data['email'])) {
            Session::flash('error', 'Cet email est déjà utilisé');
            View::redirect('/users/create');
        }

        try {
            $userId = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role_id' => $data['role_id'],
                'client_id' => !empty($data['client_id']) ? $data['client_id'] : null,
                'phone' => $data['phone'] ?? null,
                'active' => isset($data['active']) ? 1 : 0
            ]);

            Session::flash('success', 'Utilisateur créé avec succès');
            View::redirect('/users');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la création : ' . $e->getMessage());
            View::redirect('/users/create');
        }
    }

    public function edit($id)
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            Session::flash('error', 'Utilisateur introuvable');
            View::redirect('/users');
        }

        $roleModel = new Role();
        $clientModel = new Client();

        View::render('users.edit', [
            'user' => $user,
            'roles' => $roleModel->all(),
            'clients' => $clientModel->getAll()
        ]);
    }

    public function update($id)
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            Session::flash('error', 'Utilisateur introuvable');
            View::redirect('/users');
        }

        $data = $_POST;

        $validator = new Validator($data);
        $validator->required(['username', 'email', 'first_name', 'last_name', 'role_id']);
        $validator->email('email');

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/users/' . $id . '/edit');
        }

        // Vérifier unicité username et email (sauf pour l'utilisateur courant)
        $existingUsername = $this->userModel->findByUsername($data['username']);
        if ($existingUsername && $existingUsername['id'] != $id) {
            Session::flash('error', 'Ce nom d\'utilisateur existe déjà');
            View::redirect('/users/' . $id . '/edit');
        }

        $existingEmail = $this->userModel->findByEmail($data['email']);
        if ($existingEmail && $existingEmail['id'] != $id) {
            Session::flash('error', 'Cet email est déjà utilisé');
            View::redirect('/users/' . $id . '/edit');
        }

        try {
            $updateData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role_id' => $data['role_id'],
                'client_id' => !empty($data['client_id']) ? $data['client_id'] : null,
                'phone' => $data['phone'] ?? null,
                'active' => isset($data['active']) ? 1 : 0
            ];

            // Mise à jour du mot de passe seulement si fourni
            if (!empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $this->userModel->update($id, $updateData);

            Session::flash('success', 'Utilisateur mis à jour avec succès');
            View::redirect('/users');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            View::redirect('/users/' . $id . '/edit');
        }
    }

    public function delete($id)
    {
        if (!Auth::can('manage_users')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        // Ne pas supprimer son propre compte
        if ($id == Auth::id()) {
            Session::flash('error', 'Vous ne pouvez pas supprimer votre propre compte');
            View::redirect('/users');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            Session::flash('error', 'Utilisateur introuvable');
            View::redirect('/users');
        }

        try {
            $this->userModel->delete($id);

            Session::flash('success', 'Utilisateur supprimé avec succès');
            View::redirect('/users');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            View::redirect('/users');
        }
    }
}
