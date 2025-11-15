# Instructions pour corriger le problème "Accès refusé" des clients

## Problème
Les clients reçoivent "Accès refusé" quand ils essaient de créer une commande via `/orders/create`.

## Cause
Les permissions du rôle client ne sont pas correctement configurées dans la base de données.

## Solution en 3 étapes

### Étape 1: Vérifier les permissions actuelles

**En tant qu'utilisateur client connecté**, aller sur:
```
https://gestion.d-evidences.fr/debug-permissions.php
```

Cette page affiche:
- Les informations de l'utilisateur
- Les permissions actuelles du rôle
- Les tests de permissions (create_orders, orders.create, etc.)

**Captures à vérifier:**
- `Auth::can('create_orders')` doit être **OUI** (vert)
- `Auth::can('orders.create')` doit être **OUI** (vert)

---

### Étape 2: Corriger les permissions

**En tant qu'administrateur**, aller sur:
```
https://gestion.d-evidences.fr/fix-client-permissions.php
```

Cette page va:
1. Afficher les permissions AVANT correction
2. Mettre à jour les permissions au bon format RBAC
3. Afficher les permissions APRÈS correction

**Format des permissions appliquées:**
```json
{
    "orders": {
        "create": true,
        "read": "own"
    },
    "sites": {
        "read": "own"
    },
    "reports": {
        "read": "own",
        "download": true
    },
    "messages": {
        "create": true,
        "read": "own"
    },
    "map": {
        "read": true
    }
}
```

---

### Étape 3: Tester

**IMPORTANT:** Les clients doivent se **déconnecter et se reconnecter** pour que les nouvelles permissions soient prises en compte (le cache de session doit être rafraîchi).

Ensuite, tester:
1. Aller sur https://gestion.d-evidences.fr/debug-permissions.php
2. Vérifier que toutes les permissions sont vertes
3. Aller sur https://gestion.d-evidences.fr/orders/create
4. Le formulaire devrait s'afficher avec les informations du client pré-remplies

---

## Nettoyage (après résolution)

Une fois le problème résolu, **supprimer les fichiers de debug**:
```bash
rm /home/gestion/public_html/public/debug-permissions.php
rm /home/gestion/public_html/public/fix-client-permissions.php
```

Ou via SSH:
```bash
cd /chemin/vers/votre/projet
rm public/debug-permissions.php
rm public/fix-client-permissions.php
```

---

## Alternative: Migration SQL directe

Si vous préférez exécuter la migration manuellement via SSH:

```bash
cd /chemin/vers/votre/projet
mysql -u [utilisateur] -p [base_de_données] < database/migrations/013_update_client_permissions.sql
```

---

## Support

Si le problème persiste après ces étapes:
1. Vérifier les logs Apache/PHP pour voir l'erreur exacte
2. Vérifier que la table `roles` contient bien un rôle avec `name = 'client'`
3. Vérifier que l'utilisateur client a bien un `role_id` correspondant au rôle client
4. Contacter le support technique avec les captures d'écran de debug-permissions.php
