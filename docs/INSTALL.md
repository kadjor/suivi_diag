# Guide d'Installation - Plateforme de Suivi de Diagnostics

## 📋 Prérequis

### Serveur
- **PHP** : 7.4 ou supérieur (8.0+ recommandé)
- **MySQL** / **MariaDB** : 5.7+ / 10.3+
- **Serveur Web** : Apache 2.4+ ou Nginx
- **Espace disque** : 500 MB minimum (extensible selon volume de fichiers)

### Extensions PHP requises
- `pdo`
- `pdo_mysql`
- `mbstring`
- `json`
- `fileinfo`
- `gd` (optionnel, pour manipulation d'images)
- `zip` (optionnel, pour exports)
- `curl` (optionnel, pour géocodage)

### Vérification des prérequis
```bash
php -v  # Vérifier version PHP
php -m  # Lister les extensions installées
```

## 🚀 Installation sur Virtualmin

### Méthode 1 : Installation via Script Automatique (Recommandé)

#### 1. Créer un domaine virtuel dans Virtualmin

1. Connectez-vous à Virtualmin
2. **Create Virtual Server**
   - Domain name: `suivi-diagnostics.votredomaine.com`
   - Administration username: choisir
   - Administration password: choisir
   - Cocher **Create MySQL database**
3. Cliquer sur **Create Server**

#### 2. Uploader les fichiers

**Via FTP/SFTP :**
```bash
# Connectez-vous en SFTP à votre serveur
# Uploadez tous les fichiers dans :
/home/username/public_html/

# Ou si sous-dossier :
/home/username/public_html/suivi-diag/
```

**Via SSH (si accès disponible) :**
```bash
cd /home/username
git clone https://votre-repo.git suivi-diag
# Ou uploadez l'archive et décompressez :
tar -xzf suivi-diag.tar.gz
```

#### 3. Configurer la base de données

Éditez le fichier `config/database.php` :

```php
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'username_suividiag',  // Nom BDD créée par Virtualmin
    'username' => 'username_suividiag',  // User BDD Virtualmin
    'password' => 'VOTRE_MOT_DE_PASSE', // Mot de passe BDD
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // ... reste de la config
];
```

> **Astuce** : Dans Virtualmin, allez dans **Edit Databases** pour voir les informations de connexion MySQL.

#### 4. Configurer l'application

Éditez le fichier `config/app.php` :

```php
return [
    'name' => 'Plateforme de Suivi de Diagnostics',
    'url' => 'https://suivi-diagnostics.votredomaine.com', // Votre URL
    'environment' => 'production', // 'development' pour débug

    'email' => [
        'from_address' => 'noreply@votredomaine.com',
        'from_name' => 'Suivi Diagnostics',
        'smtp_host' => 'localhost',
        'smtp_port' => 25,
        // ... config email
    ],
    // ... reste de la config
];
```

#### 5. Ajuster les permissions

```bash
chmod 770 logs
chmod 770 public/uploads
chmod 770 -R public/uploads/*

# Ou via Virtualmin : File Manager > clic droit > Change Permissions
```

#### 6. Configurer le Document Root

Dans **Virtualmin** :
1. **Server Configuration** → **Website Options**
2. **Document Root** : `/home/username/public_html/suivi-diag/public`
3. Sauvegarder

> **Important** : Le document root DOIT pointer vers le dossier `/public` pour la sécurité.

#### 7. Activer mod_rewrite (Apache)

Si Apache, vérifier que `.htaccess` est actif :

Dans **Virtualmin** :
1. **Services** → **Apache Webserver**
2. Vérifier que **mod_rewrite** est activé
3. Dans la config du virtual host, s'assurer que `AllowOverride All` est défini

#### 8. Lancer le script d'installation

**Méthode A - Via navigateur (plus simple) :**

Accédez à : `https://votredomaine.com/scripts/install.php`

Le script va :
- ✓ Vérifier les prérequis
- ✓ Créer la base de données
- ✓ Créer les tables
- ✓ Insérer les données de démonstration
- ✓ Vérifier les permissions

**Méthode B - Via SSH (si accès disponible) :**

```bash
cd /home/username/public_html/suivi-diag
php scripts/install.php
```

#### 9. Sécuriser l'installation

**Supprimer/protéger le script d'installation :**
```bash
rm scripts/install.php
# Ou le déplacer hors du web root
```

**Activer HTTPS :**

Dans Virtualmin :
1. **Server Configuration** → **SSL Certificate**
2. **Let's Encrypt** → Request Certificate
3. Cocher **Redirect HTTP to HTTPS**

**Mettre en production :**

Dans `config/app.php` :
```php
'environment' => 'production',
```

#### 10. Accéder à la plateforme

🎉 **C'est prêt !**

Accédez à : `https://votredomaine.com`

### Comptes de démonstration

Tous les comptes ont le mot de passe : **Demo2024!**

| Rôle | Identifiant | Accès |
|------|-------------|-------|
| **Administrateur** | `admin` | Accès complet, gestion système |
| **Secrétariat** | `secretariat` | Gestion commandes, assignations |
| **Technicien** | `tech1` | Interventions assignées, rapports |
| **Client PCH** | `client.pch` | Commandes, rapports, cartographie |

### Configuration post-installation

#### A. Email (Notifications)

Éditez `config/app.php` section `email` :

**Utiliser SMTP local (Postfix sur Virtualmin) :**
```php
'email' => [
    'smtp_host' => 'localhost',
    'smtp_port' => 25,
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => '',
    'smtp_auth' => false
],
```

**Utiliser SMTP externe (Gmail, SendGrid, etc.) :**
```php
'email' => [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'votre-email@gmail.com',
    'smtp_password' => 'votre-mot-de-passe-app',
    'smtp_encryption' => 'tls',
    'smtp_auth' => true
],
```

#### B. Tâches planifiées (Cron)

Dans **Virtualmin** → **Scheduled Cron Jobs** → **Create**

**Nettoyage des fichiers temporaires (quotidien) :**
```
0 2 * * * cd /home/username/public_html/suivi-diag && php scripts/cleanup.php
```

**Envoi des notifications (toutes les 15 min) :**
```
*/15 * * * * cd /home/username/public_html/suivi-diag && php scripts/send_notifications.php
```

**Sauvegarde base de données (quotidien) :**
```
0 3 * * * cd /home/username/public_html/suivi-diag && php scripts/backup.php
```

#### C. Géocodage

Par défaut, le système utilise **Nominatim** (OpenStreetMap) pour le géocodage gratuit.

Pour utiliser un service payant (Google Maps, Mapbox), éditez `config/app.php` :

```php
'map' => [
    'geocoding_service' => 'google', // 'google', 'mapbox', ou 'nominatim'
    'geocoding_api_key' => 'VOTRE_CLE_API',
],
```

## 🛠️ Dépannage

### Erreur "500 Internal Server Error"

**Causes possibles :**
- Permissions incorrectes sur les fichiers
- `.htaccess` non lu (AllowOverride)
- PHP errors

**Solutions :**
```bash
# Vérifier les logs
tail -f logs/error.log

# Vérifier permissions
chmod 770 logs
chmod 770 public/uploads -R

# Activer display_errors temporairement (development)
# Dans config/app.php : 'environment' => 'development'
```

### Erreur "Database connection failed"

- Vérifier identifiants dans `config/database.php`
- Vérifier que MySQL est démarré
- Tester la connexion :
```bash
mysql -h localhost -u username_suividiag -p
```

### Page de login ne s'affiche pas

- Vérifier que mod_rewrite est actif
- Vérifier le Document Root (doit pointer vers `/public`)
- Tester l'accès direct : `https://votredomaine.com/index.php`

### Uploads de fichiers échouent

```bash
# Vérifier permissions
ls -la public/uploads

# Corriger si nécessaire
chmod 770 public/uploads -R
chown username:username public/uploads -R
```

### Cartographie ne s'affiche pas

- Vérifier que les sites ont des coordonnées GPS
- Vérifier la console JavaScript du navigateur (F12)
- Tester le géocodage manuellement

## 📊 Migration de données existantes

Si vous avez des données existantes dans Excel :

1. Connectez-vous en tant que **Secrétariat** ou **Admin**
2. Allez dans **Cartographie** → **Import Excel**
3. Téléchargez le modèle Excel fourni
4. Remplissez avec vos données
5. Importez le fichier

Format Excel attendu : voir `docs/modele_import_cartographie.xlsx`

## 🔄 Mises à jour

```bash
# Sauvegarder la base de données
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Uploader les nouveaux fichiers (en écrasant les anciens)
# Exécuter les migrations si nécessaire
php scripts/migrate.php
```

## 📞 Support

- **Documentation** : `/docs`
- **Logs applicatifs** : `/logs/app.log`
- **Logs d'erreurs** : `/logs/error.log`
- **Audit** : Accessible via interface admin

## ✅ Checklist post-installation

- [ ] ✓ Installation terminée sans erreurs
- [ ] ✓ Connexion avec compte admin réussie
- [ ] ✓ Email de test envoyé
- [ ] ✓ Upload de fichier testé
- [ ] ✓ Cartographie affichée
- [ ] ✓ Compte démo client testé
- [ ] ✓ HTTPS activé
- [ ] ✓ Script install.php supprimé
- [ ] ✓ Environnement = 'production'
- [ ] ✓ Mots de passe démo changés
- [ ] ✓ Cron jobs configurés
- [ ] ✓ Sauvegarde automatique configurée

---

**Version** : 1.0.0
**Dernière mise à jour** : 2024-03-15
