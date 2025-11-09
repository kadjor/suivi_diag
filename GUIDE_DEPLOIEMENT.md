# Guide de Déploiement

## ✅ Dépôt GitHub confirmé PUBLIC

**Le dépôt https://github.com/kadjor/suivi_diag existe et est accessible !**

### Configuration du téléchargement GitHub

Pour télécharger depuis GitHub, utilisez **l'une de ces deux options** :

#### Option 1 : Utiliser la branche actuelle (FONCTIONNEL IMMÉDIATEMENT)

Dans le formulaire de téléchargement GitHub sur `/deploy`, utilisez :

```
Utilisateur GitHub : kadjor
Dépôt : suivi_diag
Branche : claude/order-tracking-diagnostic-platform-011CUu5gBr5EcamkxJVfZ6AW
```

⚠️ **Attention** : Cette branche a un nom très long. Copiez-la exactement !

#### Option 2 : Créer une branche `main` (RECOMMANDÉ pour la production)

Pour avoir un nom de branche plus simple :

1. **Créez une branche `main` depuis votre environnement local** :
   ```bash
   cd /home/user/suivi_diag
   git checkout -b main
   git push -u origin main
   ```

2. **Utilisez ensuite dans le formulaire** :
   ```
   Utilisateur GitHub : kadjor
   Dépôt : suivi_diag
   Branche : main
   ```

## 1. Téléchargement depuis GitHub

### ⚠️ Important : Format correct

Dans le formulaire de téléchargement GitHub (`/deploy`), utilisez **chaque champ séparément** :

**✅ Format CORRECT :**
```
Utilisateur GitHub : kadjor
Dépôt : suivi_diag
Branche : main
```

**❌ Format INCORRECT :**
- Mettre le lien complet : `https://github.com/kadjor/suivi_diag`
- Ajouter `.git` à la fin
- Utiliser des slashes (`/`)

### Erreur 404 - Solutions détaillées

Si vous obtenez l'erreur "Échec du téléchargement (HTTP 404)" :

1. **Le dépôt n'existe PAS**
   - Vérifiez en allant sur https://github.com/kadjor/suivi_diag
   - Si vous voyez "404", le dépôt n'existe pas
   - **Solution** : Créez le dépôt (voir Option 1 ci-dessus)

2. **Le dépôt est PRIVÉ**
   - Si le dépôt demande une connexion, il est privé
   - **Solution** : Rendez-le public dans Settings > General > Danger Zone > Change visibility > Make public

3. **Le nom est incorrect**
   - Vérifiez l'orthographe exacte
   - Utilisateur : `kadjor` (sans majuscules, sans espaces)
   - Dépôt : le nom EXACT visible sur GitHub

4. **La branche n'existe pas**
   - Vérifiez si c'est `main` ou `master`
   - Regardez dans l'interface GitHub, en haut à gauche

## 2. Migrations SQL

### Comment exécuter les migrations

1. Allez sur la page `/deploy`
2. Descendez à la section "🗄️ Migrations de base de données"
3. Cliquez sur "📋 Charger les migrations"
4. Vous verrez la liste des migrations (par ex: `002_create_settings_table.sql`)
5. Cliquez sur "▶️ Exécuter les migrations en attente"

### Erreur JSON.parse

Si vous obtenez l'erreur `JSON.parse: unexpected character at line 1 column 1`:

**✅ Solution appliquée (DÉFINITIVE) :**
- Interception précoce de `/deploy/migrations` dans `public/index.php`
- La requête est traitée AVANT `session_start()` et tout le bootstrap
- Aucune pollution de sortie possible
- JSON pur garanti

Cette erreur devrait maintenant être **complètement résolue**.

**Si l'erreur persiste malgré tout :**

1. **Vérifiez les logs**
   ```bash
   tail -f /home/user/suivi_diag/storage/logs/deploy.log
   ```

2. **Vérifiez les permissions**
   ```bash
   chmod 755 /home/user/suivi_diag/database/migrations
   chmod 644 /home/user/suivi_diag/database/migrations/*.sql
   ```

3. **Testez la route directement**
   - Ouvrez dans votre navigateur : `https://votre-domaine.com/deploy/migrations`
   - Vous devriez voir du JSON, pas du HTML

4. **Vérifiez la base de données**
   - Assurez-vous que la connexion à la base fonctionne
   - Vérifiez `config/database.php`

### Que font les migrations ?

Les migrations créent et mettent à jour les tables de la base de données :

- `002_create_settings_table.sql` : Crée la table `settings` avec les paramètres de l'application
- Chaque migration est exécutée **une seule fois**
- Le système garde une trace dans la table `migrations`

### Avantages du système de migration

✅ **Contourne les limitations de Virtualmin** qui ne permet pas d'exécuter plusieurs requêtes SQL à la fois

✅ **Exécution instruction par instruction** pour éviter les erreurs de syntaxe

✅ **Tracking automatique** : les migrations déjà exécutées sont ignorées

✅ **Logs détaillés** dans `storage/logs/deploy.log`

## 3. Dépannage général

### Vérifier les logs de déploiement

```bash
# Logs de déploiement
tail -50 /home/user/suivi_diag/storage/logs/deploy.log

# Logs d'erreur PHP
tail -50 /home/user/suivi_diag/storage/logs/error.log
```

### Restaurer les permissions

Si vous avez des problèmes de permissions après une mise à jour :

1. Allez sur `/deploy`
2. Cliquez sur "🔐 Restaurer les permissions"

### Créer un backup manuel

Les backups sont automatiques lors des mises à jour, mais vous pouvez aussi :

1. Allez sur `/deploy`
2. Cliquez sur "💾 Voir les backups"
3. Les 5 derniers backups sont conservés

## 4. Workflow de mise à jour recommandé

### Environnement avec Git

1. Cliquez sur "📋 Voir les différences" pour voir ce qui va changer
2. Cliquez sur "⬇️ Mettre à jour (Git Pull)"
3. Un backup est créé automatiquement
4. Les fichiers sont mis à jour
5. Les permissions sont restaurées
6. Exécutez les migrations si nécessaire

### Environnement sans Git (Virtualmin)

1. Remplissez le formulaire GitHub (kadjor / suivi_diag / main)
2. Cliquez sur "⬇️ Télécharger et déployer"
3. Un backup est créé automatiquement
4. Les fichiers sont téléchargés et extraits
5. Les permissions sont restaurées
6. Exécutez les migrations si nécessaire

## Support

Si vous rencontrez des problèmes non couverts par ce guide :

1. Vérifiez les logs (`storage/logs/`)
2. Vérifiez que tous les dossiers ont les bonnes permissions
3. Essayez de restaurer un backup si nécessaire
