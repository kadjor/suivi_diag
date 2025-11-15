# Script de Déploiement Automatique

## Description

Le script `deploy.sh` automatise le déploiement de l'application en :
- Téléchargeant la dernière version de la branche Git actuelle
- Configurant les permissions pour l'utilisateur `gestion`
- Nettoyant le cache
- Créant un log détaillé de chaque déploiement

## Prérequis

- Accès root ou sudo sur le serveur
- L'utilisateur `gestion` doit exister sur le système
- Le répertoire doit être un dépôt Git valide

## Utilisation

### Méthode 1 : Depuis le serveur

```bash
# Se connecter au serveur
ssh votre_utilisateur@gestion.d-evidences.fr

# Aller dans le répertoire de l'application
cd /home/gestion/public_html

# Exécuter le script avec sudo
sudo ./deploy.sh
```

### Méthode 2 : En une seule commande

```bash
ssh votre_utilisateur@gestion.d-evidences.fr "cd /home/gestion/public_html && sudo ./deploy.sh"
```

## Ce que fait le script

1. **Vérifications initiales**
   - Vérifie que le script est exécuté en tant que root
   - Vérifie que l'utilisateur `gestion` existe
   - Vérifie que c'est un dépôt Git

2. **Sauvegarde des changements locaux**
   - Si des modifications locales existent, crée un stash automatique
   - Permet de récupérer les changements plus tard si nécessaire

3. **Mise à jour du code**
   - Exécute `git fetch origin`
   - Exécute `git pull origin <branche_actuelle>`
   - Affiche le dernier commit téléchargé

4. **Configuration des permissions**
   - Change le propriétaire de tous les fichiers : `gestion:gestion`
   - Répertoires : `755` (rwxr-xr-x)
   - Fichiers : `644` (rw-r--r--)
   - Dossiers spéciaux en écriture : `775`
     - `storage/`
     - `storage/logs/`
     - `storage/cache/`
     - `storage/uploads/`
     - `storage/backups/`

5. **Nettoyage**
   - Vide le cache dans `storage/cache/`

6. **Logs**
   - Crée un fichier de log horodaté dans `storage/logs/deploy_YYYYMMDD_HHMMSS.log`
   - Affiche les résultats en temps réel et dans le log

## Exemples d'utilisation

### Déploiement standard

```bash
sudo ./deploy.sh
```

**Sortie attendue :**
```
[2025-11-11 20:30:15] ==========================================
[2025-11-11 20:30:15] Début du déploiement
[2025-11-11 20:30:15] ==========================================
[2025-11-11 20:30:15] INFO: Répertoire de l'application: /home/gestion/public_html
[2025-11-11 20:30:15] INFO: Branche actuelle: claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv
[2025-11-11 20:30:15] Récupération des dernières modifications...
[2025-11-11 20:30:16] ✓ Fetch réussi
[2025-11-11 20:30:16] Téléchargement de la branche claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv...
[2025-11-11 20:30:17] ✓ Pull réussi
[2025-11-11 20:30:17] INFO: Dernier commit: bc31150 - fix: Correction gestion table custom_branches manquante
[2025-11-11 20:30:17] Configuration des permissions pour l'utilisateur 'gestion'...
[2025-11-11 20:30:17] ✓ Propriétaire changé
[2025-11-11 20:30:18] ✓ Permissions des répertoires: 755
[2025-11-11 20:30:18] ✓ Permissions des fichiers: 644
[2025-11-11 20:30:18] ✓ storage: 775
[2025-11-11 20:30:18] Nettoyage du cache...
[2025-11-11 20:30:18] ✓ Cache nettoyé
[2025-11-11 20:30:18] ==========================================
[2025-11-11 20:30:18] Déploiement terminé avec succès !
[2025-11-11 20:30:18] ==========================================

✓ Déploiement réussi !
```

### Récupérer les changements mis en stash

Si le script a créé un stash de vos changements locaux :

```bash
# Voir la liste des stash
git stash list

# Récupérer le dernier stash
git stash pop
```

## Gestion des erreurs

### Erreur : "Ce script doit être exécuté en tant que root"
**Solution :** Utiliser `sudo` :
```bash
sudo ./deploy.sh
```

### Erreur : "L'utilisateur 'gestion' n'existe pas"
**Solution :** Créer l'utilisateur ou modifier la variable `USER_OWNER` dans le script

### Erreur : "Ce répertoire n'est pas un dépôt Git"
**Solution :** Vérifier que vous êtes dans le bon répertoire avec `pwd`

### Erreur lors du git pull
**Causes possibles :**
- Problème de connexion réseau
- Conflits de merge
- Branche distante supprimée

**Solution :** Vérifier manuellement avec `git status` et `git pull`

## Permissions expliquées

| Élément | Permissions | Signification |
|---------|-------------|---------------|
| Répertoires | 755 | Propriétaire: lecture/écriture/exécution<br>Groupe: lecture/exécution<br>Autres: lecture/exécution |
| Fichiers PHP | 644 | Propriétaire: lecture/écriture<br>Groupe: lecture<br>Autres: lecture |
| storage/* | 775 | Propriétaire: lecture/écriture/exécution<br>Groupe: lecture/écriture/exécution<br>Autres: lecture/exécution |
| Scripts .sh | 755 | Exécutable par tous |

## Logs

Les logs de déploiement sont stockés dans :
```
storage/logs/deploy_YYYYMMDD_HHMMSS.log
```

Pour consulter le dernier log :
```bash
ls -lt storage/logs/deploy_*.log | head -1 | xargs cat
```

## Workflow complet de déploiement

1. **Développement local**
   ```bash
   git add .
   git commit -m "Description des modifications"
   git push origin ma-branche
   ```

2. **Déploiement sur le serveur**
   ```bash
   ssh user@gestion.d-evidences.fr
   cd /home/gestion/public_html
   sudo ./deploy.sh
   ```

3. **Exécuter les migrations si nécessaire**
   - Aller sur https://gestion.d-evidences.fr/deploy
   - Cliquer sur "Exécuter les migrations en attente"

4. **Vérifier le déploiement**
   - Tester les fonctionnalités modifiées
   - Vérifier les logs d'erreur si problème

## Personnalisation

### Changer l'utilisateur propriétaire

Éditer le script et modifier :
```bash
USER_OWNER="votre_utilisateur"
GROUP_OWNER="votre_groupe"
```

### Ajouter des actions post-déploiement

Ajouter vos commandes avant le message final :
```bash
# Exemple: redémarrer un service
systemctl restart php-fpm

# Exemple: exécuter des migrations automatiquement
php artisan migrate --force
```

## Sécurité

- ✓ Le script vérifie les permissions root
- ✓ Crée des logs détaillés de chaque action
- ✓ Sauvegarde automatique des changements locaux (stash)
- ✓ Ne supprime jamais de fichiers (sauf cache)
- ✓ Permissions restrictives (644/755)

## Support

En cas de problème :
1. Consulter le log dans `storage/logs/deploy_*.log`
2. Vérifier `git status`
3. Vérifier les permissions avec `ls -la`
4. Contacter l'administrateur système

## Auteur

Script créé pour le projet Suivi Diagnostics (gestion.d-evidences.fr)
