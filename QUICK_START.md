# ⚡ Démarrage Rapide - 5 minutes

## 📦 Contenu du livrable

Vous avez reçu une plateforme complète de suivi de commandes et cartographie des diagnostics, prête à déployer sur Virtualmin.

**Stack** : PHP 8+ / MySQL 5.7+ / Architecture MVC custom
**Compatibilité** : 100% Virtualmin, aucune dépendance système complexe

## 🚀 Installation en 3 étapes

### 1️⃣ Uploadez les fichiers

Via FTP/SFTP, uploadez le contenu dans :
```
/home/username/public_html/suivi-diag/
```

### 2️⃣ Configurez la base de données

Éditez `config/database.php` :
```php
'database' => 'username_suividiag',  // Nom de votre base MySQL
'username' => 'username_suividiag',  // Utilisateur MySQL
'password' => 'VOTRE_MOT_DE_PASSE', // Mot de passe MySQL
```

💡 **Virtualmin** : Ces infos sont dans **Edit Databases**

### 3️⃣ Lancez l'installation

Accédez à : `https://votredomaine.com/scripts/install.php`

Le script :
- ✅ Vérifie les prérequis PHP
- ✅ Crée les tables
- ✅ Insère les données de démonstration
- ✅ Configure les permissions

**C'est prêt !** 🎉

## 🔑 Première connexion

Accédez à : `https://votredomaine.com`

**Comptes de démonstration** (mot de passe : `Demo2024!`) :

| Rôle | Identifiant |
|------|-------------|
| Administrateur | `admin` |
| Secrétariat | `secretariat` |
| Technicien | `tech1` |
| Client | `client.pch` |

## ✅ Checklist post-installation

- [ ] Connexion réussie avec le compte `admin`
- [ ] Changement des mots de passe par défaut
- [ ] Suppression/sécurisation du script `scripts/install.php`
- [ ] Configuration email dans `config/app.php`
- [ ] Activation HTTPS (Let's Encrypt Virtualmin)
- [ ] Test upload fichier
- [ ] Test cartographie
- [ ] Mise en production : `'environment' => 'production'` dans `config/app.php`

## 📚 Documentation complète

- **README.md** : vue d'ensemble complète
- **PERMISSIONS.md** : guide complet des permissions (chmod, chown, dépannage) 🔑
- **docs/INSTALL.md** : guide d'installation détaillé Virtualmin
- **COMPTES_DEMO.md** : tous les comptes de test
- **SPECIFICATION_TECHNIQUE.md** : architecture, modèle données
- **DIAGRAMMES.md** : ERD, workflows, diagrammes Mermaid

## 🎯 Fonctionnalités clés

✅ Gestion commandes : émission → AR → planification → réalisation → rapport → clôture
✅ Timeline horodatée immuable
✅ 4 profils : Admin, Secrétariat, Technicien, Client
✅ Cartographie interactive (Leaflet.js)
✅ Import Excel pour alimenter la carte
✅ Messagerie par commande
✅ Rapports PDF sécurisés
✅ Planning + export ICS
✅ Journal d'audit immuable
✅ Notifications email
✅ Exports CSV/JSON
✅ RGPD compliant

## 🛠️ Configuration rapide

### Email (notifications)

Éditez `config/app.php` section `email` :

**SMTP local Virtualmin (Postfix) :**
```php
'smtp_host' => 'localhost',
'smtp_port' => 25,
'smtp_auth' => false,
```

**SMTP externe (Gmail, SendGrid) :**
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_username' => 'votre-email@gmail.com',
'smtp_password' => 'mot-de-passe-application',
'smtp_encryption' => 'tls',
'smtp_auth' => true,
```

### Document Root (IMPORTANT)

Dans **Virtualmin** → **Server Configuration** → **Website Options** :
```
Document Root: /home/username/public_html/suivi-diag/public
```

⚠️ Le document root DOIT pointer vers `/public` pour la sécurité.

### mod_rewrite (Apache)

Vérifiez que `.htaccess` est actif :
- Virtualmin → Services → Apache Webserver
- Vérifier `mod_rewrite` activé
- S'assurer que `AllowOverride All` dans la config VirtualHost

### Permissions fichiers ⚠️ IMPORTANT

**Commandes complètes** (depuis la racine de l'application) :

```bash
# Dossiers d'écriture (770 = rwxrwx---)
chmod 770 logs cache sessions tmp backups
chmod 770 public/uploads
find public/uploads -type d -exec chmod 770 {} \;

# Fichiers de configuration sensibles (640 = rw-r-----)
chmod 640 config/database.php config/app.php config/settings.php

# Propriétaire (remplacer 'username' par votre utilisateur)
chown -R username:username .
```

📖 **Guide complet** : Consultez **PERMISSIONS.md** pour toutes les commandes, le script automatisé, et le dépannage des erreurs de permissions.

💡 Via Virtualmin File Manager : clic droit → Change Permissions

## 🧪 Tester le système

### Test Client

1. Connectez-vous : `client.pch` / `Demo2024!`
2. Créez une commande
3. Consultez la cartographie
4. Filtrez les diagnostics amiante
5. Téléchargez un rapport PDF

### Test Secrétariat

1. Connectez-vous : `secretariat` / `Demo2024!`
2. Voyez les nouvelles commandes
3. Générez un AR
4. Assignez un technicien
5. Planifiez un rendez-vous

### Test Technicien

1. Connectez-vous : `tech1` / `Demo2024!`
2. Consultez interventions assignées
3. Déposez un rapport PDF
4. Uploadez un fichier Excel
5. Marquez intervention comme terminée

### Test Administrateur

1. Connectez-vous : `admin` / `Demo2024!`
2. Créez un utilisateur
3. Consultez le journal d'audit
4. Exportez les commandes
5. Modifiez les paramètres

## 🐛 Problèmes courants

### Erreur 500

**1. Erreur "Invalid command 'php_value'" dans .htaccess**

Votre serveur utilise PHP-FPM/FastCGI. Le fichier `.user.ini` a été créé automatiquement.

Si le problème persiste, configurez PHP dans Virtualmin :
- Webmin → Servers → Apache → Edit PHP Configuration
- Ajoutez : `upload_max_filesize = 20M`, `post_max_size = 20M`, `memory_limit = 256M`

**2. Erreur de permissions**

```bash
# Vérifier logs :
tail -f logs/error.log

# Corriger permissions (voir PERMISSIONS.md) :
chmod 770 logs cache sessions tmp backups public/uploads -R
chown -R username:username .
```

### Base de données inaccessible

- Vérifiez `config/database.php`
- Testez : `mysql -h localhost -u user -p database`

### Page blanche

- Vérifiez Document Root (doit pointer vers `/public`)
- Vérifiez que `mod_rewrite` est actif
- Testez accès direct : `https://votredomaine.com/index.php`

### Uploads échouent

```bash
# Permissions :
chmod 770 public/uploads -R

# Vérifier limites PHP :
php -i | grep -E "upload_max_filesize|post_max_size"
```

## 📞 Support

- **Logs** : `logs/app.log`, `logs/error.log`
- **Documentation** : dossier `/docs`
- **Audit** : interface admin → Journal d'audit

## 🔐 Sécurité IMPORTANTE

⚠️ **Avant mise en production** :

1. **Changez TOUS les mots de passe démo**
2. **Supprimez `scripts/install.php`** : `rm scripts/install.php`
3. **Activez HTTPS** (Let's Encrypt Virtualmin)
4. **Passez en mode production** : `'environment' => 'production'` dans `config/app.php`
5. **Configurez les sauvegardes automatiques** (cron)

## 📁 Fichiers clés

```
config/database.php    # Configuration DB (À ÉDITER)
config/app.php         # Configuration app (email, environnement)
database/schema.sql    # Schéma complet tables
database/seeds/        # Données de démonstration
public/index.php       # Point d'entrée application
public/.htaccess       # Réécriture URLs Apache
scripts/install.php    # Installation automatique (À SUPPRIMER après install)
```

## 🎓 Prochaines étapes

1. Changez les mots de passe
2. Créez vos vrais utilisateurs
3. Ajoutez vos clients et sites
4. Importez vos diagnostics existants (via Excel)
5. Configurez les emails
6. Testez le workflow complet
7. Formez vos utilisateurs
8. Mettez en production

## 📖 Ressources

- **Modèle de données** : SPECIFICATION_TECHNIQUE.md
- **Workflows** : DIAGRAMMES.md
- **Guide installation** : docs/INSTALL.md
- **Comptes démo** : COMPTES_DEMO.md
- **Tests d'acceptation** : tests/acceptance/

---

**Besoin d'aide ?** Consultez `docs/INSTALL.md` section Dépannage

🚀 **Bon déploiement !**
