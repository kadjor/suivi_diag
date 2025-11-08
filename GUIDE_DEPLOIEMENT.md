# Guide de Déploiement

## 1. Téléchargement depuis GitHub

### ⚠️ Important : NE PAS utiliser de lien complet

Dans le formulaire de téléchargement GitHub (`/deploy`), utilisez **uniquement** les informations suivantes :

```
Utilisateur GitHub : kadjor
Dépôt : suivi_diag
Branche : main
```

**❌ Ne faites PAS :**
- Mettre le lien complet : `https://github.com/kadjor/suivi_diag`
- Ajouter `.git` à la fin
- Utiliser des slashes (`/`)

**✅ Faites :**
- Séparer chaque information dans son champ
- Vérifier que le dépôt est **public** (sinon erreur 404)
- Utiliser le nom exact de la branche

### Erreur 404 - Solutions

Si vous obtenez une erreur 404 :

1. **Vérifiez que le dépôt est public**
   - Allez sur https://github.com/kadjor/suivi_diag
   - Si vous ne pouvez pas y accéder sans vous connecter, le dépôt est privé
   - Rendez-le public dans Settings > General > Danger Zone

2. **Vérifiez le nom de la branche**
   - Le nom doit être exact : `main` ou `master`
   - Pas de majuscules si c'est en minuscules
   - Pas d'espaces

3. **Vérifiez l'orthographe**
   - Utilisateur : exactement `kadjor`
   - Dépôt : exactement `suivi_diag`

## 2. Migrations SQL

### Comment exécuter les migrations

1. Allez sur la page `/deploy`
2. Descendez à la section "🗄️ Migrations de base de données"
3. Cliquez sur "📋 Charger les migrations"
4. Vous verrez la liste des migrations (par ex: `002_create_settings_table.sql`)
5. Cliquez sur "▶️ Exécuter les migrations en attente"

### Erreur JSON.parse

Si vous obtenez l'erreur `JSON.parse: unexpected character at line 1 column 1`:

**Solutions appliquées :**
- ✅ Nettoyage du buffer de sortie PHP
- ✅ Meilleure gestion des erreurs
- ✅ Try-catch robuste

**Si l'erreur persiste :**

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
