<?php

namespace Core;

/**
 * Role-Based Access Control (RBAC)
 * Gestion des permissions basées sur les rôles
 */
class RBAC
{
    private $rolesCache = [];

    /**
     * Vérifie si un rôle a une permission donnée
     *
     * @param int $roleId ID du rôle
     * @param string $permission Permission à vérifier (format: "resource.action" ex: "orders.create")
     * @return bool
     */
    public function hasPermission($roleId, $permission)
    {
        $role = $this->getRole($roleId);

        if (!$role) {
            return false;
        }

        // Décoder les permissions JSON
        $permissions = json_decode($role['permissions'], true);

        if (!$permissions) {
            return false;
        }

        // Parser la permission (ex: "orders.create" -> resource: orders, action: create)
        $parts = explode('.', $permission);

        if (count($parts) !== 2) {
            return false;
        }

        list($resource, $action) = $parts;

        // Vérifier si la permission existe et est à true
        return isset($permissions[$resource][$action]) && $permissions[$resource][$action] === true;
    }

    /**
     * Vérifie si un rôle peut accéder à une ressource
     * Gère également les permissions contextuelles ("assigned", "own")
     *
     * @param int $roleId
     * @param string $resource
     * @param string $action
     * @param mixed $context Contexte pour vérifications (ex: owner_id, assigned_to)
     * @return bool
     */
    public function canAccess($roleId, $resource, $action, $context = null)
    {
        $role = $this->getRole($roleId);

        if (!$role) {
            return false;
        }

        $permissions = json_decode($role['permissions'], true);

        if (!isset($permissions[$resource][$action])) {
            return false;
        }

        $permission = $permissions[$resource][$action];

        // Permission simple (true/false)
        if (is_bool($permission)) {
            return $permission;
        }

        // Permission contextuelle (ex: "assigned", "own")
        if (is_string($permission) && $context) {
            return $this->checkContextualPermission($permission, $context);
        }

        return false;
    }

    /**
     * Vérifie une permission contextuelle
     */
    private function checkContextualPermission($permissionType, $context)
    {
        $userId = Auth::id();

        switch ($permissionType) {
            case 'assigned':
                // L'utilisateur doit être assigné à la ressource
                return isset($context['assigned_to']) && $context['assigned_to'] == $userId;

            case 'own':
                // L'utilisateur doit être propriétaire de la ressource
                // Ou bien appartenir au même client
                if (isset($context['created_by']) && $context['created_by'] == $userId) {
                    return true;
                }
                if (isset($context['user_id']) && $context['user_id'] == $userId) {
                    return true;
                }
                // Vérification du client_id pour les clients
                $user = Auth::user();
                if (isset($user['client_id']) && isset($context['client_id'])) {
                    return $user['client_id'] == $context['client_id'];
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * Récupère un rôle par son ID (avec cache)
     */
    private function getRole($roleId)
    {
        if (isset($this->rolesCache[$roleId])) {
            return $this->rolesCache[$roleId];
        }

        $roleModel = new \Models\Role();
        $role = $roleModel->find($roleId);

        if ($role) {
            $this->rolesCache[$roleId] = $role;
        }

        return $role;
    }

    /**
     * Récupère toutes les permissions d'un rôle
     */
    public function getRolePermissions($roleId)
    {
        $role = $this->getRole($roleId);
        return $role ? json_decode($role['permissions'], true) : [];
    }

    /**
     * Met à jour les permissions d'un rôle
     */
    public function updateRolePermissions($roleId, $permissions)
    {
        $roleModel = new \Models\Role();
        $success = $roleModel->update($roleId, [
            'permissions' => json_encode($permissions)
        ]);

        // Vider le cache
        unset($this->rolesCache[$roleId]);

        return $success;
    }

    /**
     * Vérifie les permissions pour une liste d'actions
     */
    public function canMultiple($roleId, array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($roleId, $permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retourne toutes les permissions disponibles dans le système
     */
    public function getAllPermissions()
    {
        return [
            'users' => ['create', 'read', 'update', 'delete'],
            'clients' => ['create', 'read', 'update', 'delete'],
            'sites' => ['create', 'read', 'update', 'delete'],
            'orders' => ['create', 'read', 'update', 'delete', 'close', 'assign'],
            'interventions' => ['create', 'read', 'update', 'delete'],
            'reports' => ['create', 'read', 'update', 'delete', 'download', 'upload_excel'],
            'messages' => ['create', 'read', 'update', 'delete'],
            'diagnostics' => ['create', 'read', 'update', 'delete'],
            'map' => ['read', 'export'],
            'appointments' => ['create', 'read', 'update', 'delete'],
            'settings' => ['read', 'update'],
            'audit' => ['read', 'export'],
            'exports' => ['orders', 'interventions', 'diagnostics', 'all']
        ];
    }
}
