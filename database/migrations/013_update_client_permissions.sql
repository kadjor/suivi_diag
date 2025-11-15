-- Migration: Mise à jour des permissions du rôle client
-- Les clients peuvent maintenant créer des commandes

-- Récupérer l'ID du rôle client
SET @client_role_id = (SELECT id FROM roles WHERE name = 'client' LIMIT 1);

-- Mettre à jour les permissions du rôle client
-- Format RBAC attendu: {"resource": {"action": true}}
UPDATE roles
SET permissions = JSON_SET(
    COALESCE(permissions, '{}'),
    '$.orders.create', true,
    '$.orders.read', 'own',
    '$.sites.read', 'own',
    '$.reports.read', 'own',
    '$.reports.download', true,
    '$.messages.create', true,
    '$.messages.read', 'own',
    '$.map.read', true
)
WHERE name = 'client';

-- Vérification
SELECT id, name, label, permissions
FROM roles
WHERE name = 'client';
