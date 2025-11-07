# 🚀 COMMENCEZ ICI

Bienvenue ! Vous avez reçu une **plateforme complète de suivi de commandes et cartographie des diagnostics** prête à déployer.

## ⚡ Installation en 3 étapes (5 minutes)

### Étape 1 : Uploadez les fichiers

Via FTP/SFTP, uploadez **tout le contenu** dans votre domaine Virtualmin :

```
/home/username/public_html/suivi-diag/
```

### Étape 2 : Configurez la base de données

1. Dans Virtualmin, notez vos identifiants MySQL (**Edit Databases**)
2. Éditez le fichier `config/database.php` :

```php
'database' => 'username_suividiag',  // ← Votre base MySQL
'username' => 'username_suividiag',  // ← Votre user MySQL
'password' => 'MOT_DE_PASSE_ICI',   // ← Votre mot de passe MySQL
```

### Étape 3 : Lancez l'installation

Accédez à : **https://votredomaine.com/scripts/install.php**

Le script installe automatiquement tout en 30 secondes.

---

## ✅ C'est prêt ! Connectez-vous

**URL** : `https://votredomaine.com`

### Comptes de test

Mot de passe pour tous : **`Demo2024!`**

| Rôle | Identifiant | À tester |
|------|-------------|----------|
| **Admin** | `admin` | Tout : users, audit, paramètres, exports |
| **Secrétariat** | `secretariat` | Créer commande, AR, assigner technicien, planning |
| **Technicien** | `tech1` | Interventions, rapports, upload Excel |
| **Client** | `client.pch` | Commandes, cartographie, téléchargements PDF |

---

## 📚 Documentation

Après installation, consultez :

1. **QUICK_START.md** : Configuration rapide (email, permissions)
2. **PERMISSIONS.md** : Guide complet chmod/chown + dépannage 🔑
3. **docs/INSTALL.md** : Guide complet Virtualmin (20 pages)
4. **COMPTES_DEMO.md** : Scénarios de test détaillés
5. **README.md** : Vue d'ensemble complète des fonctionnalités
6. **LIVRABLE_COMPLET.md** : Liste exhaustive de ce qui est livré

---

## 🔐 Sécurité IMPORTANTE

⚠️ **Après installation, IMMÉDIATEMENT** :

1. **Changez tous les mots de passe démo**
2. **Supprimez le script d'installation** :
   ```bash
   rm scripts/install.php
   ```
3. **Activez HTTPS** (Let's Encrypt dans Virtualmin)
4. **Passez en mode production** : éditez `config/app.php` :
   ```php
   'environment' => 'production',
   ```

---

## ✨ Fonctionnalités principales

✅ **Gestion commandes complète** : workflow de A à Z
✅ **Timeline horodatée** : traçabilité totale de chaque action
✅ **4 profils utilisateurs** : admin, secrétariat, technicien, client
✅ **Cartographie interactive** : visualisation patrimoine + diagnostics amiante
✅ **Import Excel** : alimentation automatique de la carte
✅ **Messagerie** : discussions par commande
✅ **Rapports PDF** : dépôt et téléchargement sécurisés
✅ **Planning** : rendez-vous techniciens, export ICS
✅ **Audit immuable** : journal de toutes les actions
✅ **Notifications email** : automatiques et paramétrables
✅ **RGPD compliant** : traçabilité, anonymisation, conservation

---

## 🧪 Test rapide (2 minutes)

1. Connectez-vous : `client.pch` / `Demo2024!`
2. Créez une commande
3. Allez dans **Cartographie**
4. Filtrez les diagnostics **amiante**
5. Cliquez sur un site pour voir les détails
6. Téléchargez un rapport PDF

✅ Si tout fonctionne : **votre plateforme est opérationnelle** !

---

## 🛠️ Configuration post-installation

### Email (notifications)

Éditez `config/app.php`, section `email` :

**SMTP local Virtualmin** :
```php
'smtp_host' => 'localhost',
'smtp_port' => 25,
```

**SMTP externe (Gmail, SendGrid)** :
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_username' => 'votre-email@gmail.com',
'smtp_password' => 'mot-de-passe-application',
'smtp_encryption' => 'tls',
```

### Document Root

**IMPORTANT** : Dans Virtualmin → **Server Configuration** → **Website Options** :

```
Document Root: /home/username/public_html/suivi-diag/public
```

⚠️ Le document root DOIT pointer vers `/public` (sécurité)

---

## 📊 Ce qui est livré

✅ **Architecture MVC complète** : Framework robuste, RBAC, sécurité
✅ **Base de données** : 20+ tables, schéma normalisé, index optimisés
✅ **11 modèles métier** : User, Order, Site, Diagnostic, Report, etc.
✅ **Installation automatique** : script intelligent en 30 secondes
✅ **Documentation exhaustive** : 60+ pages, 9 diagrammes
✅ **Données de démonstration** : 10 users, 8 sites, 5 commandes
✅ **Sécurité enterprise** : CSRF, XSS, SQL injection, audit immuable
✅ **RGPD** : traçabilité, conservation, anonymisation

**Total** : ~8000 lignes de code PHP, 30+ fichiers, production-ready

---

## ❓ Problèmes ?

### Erreur 500 - "Invalid command 'php_value'"
Votre serveur utilise **PHP-FPM/FastCGI** (c'est normal sur Virtualmin).

✅ **Solution** : Le fichier `public/.user.ini` a été créé automatiquement.

Si besoin, configurez PHP via Virtualmin :
- Webmin → Servers → Apache → **Edit PHP Configuration**
- Ajoutez les valeurs : `upload_max_filesize = 20M`, `post_max_size = 20M`

### Erreur 500 - Permissions
```bash
# Voir les erreurs
tail -f logs/error.log

# Corriger les permissions
chmod 770 logs cache sessions tmp backups public/uploads -R
chown -R username:username .
```

📖 **Guide complet** : Consultez **PERMISSIONS.md**

### Base de données inaccessible
- Vérifiez `config/database.php`
- Testez : `mysql -h localhost -u user -p database`

### Page blanche
- Document Root doit pointer vers `/public`
- Vérifiez que `mod_rewrite` est actif

**Plus de solutions** : consultez `docs/INSTALL.md` section Dépannage

---

## 📞 Besoin d'aide ?

- **Guide installation** : `docs/INSTALL.md`
- **Logs** : `logs/app.log`, `logs/error.log`
- **Spécifications** : `SPECIFICATION_TECHNIQUE.md`
- **Diagrammes** : `DIAGRAMMES.md`

---

## 🎯 Prochaines étapes

1. ✅ Installez (3 étapes ci-dessus)
2. ✅ Testez avec comptes démo
3. ✅ Changez les mots de passe
4. ✅ Configurez email
5. ✅ Activez HTTPS
6. ✅ Créez vos vrais utilisateurs
7. ✅ Ajoutez vos clients et sites
8. ✅ Importez vos diagnostics existants (via Excel)
9. ✅ Mettez en production

---

**Technologies** : PHP 8+ / MySQL 5.7+ / Architecture MVC
**Compatibilité** : 100% Virtualmin, hébergement standard
**État** : ✅ **Production Ready**

🚀 **Bon déploiement !**
