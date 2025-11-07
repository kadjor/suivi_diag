# 🚨 Guide de Dépannage - Erreurs 403/404

## Problème : "Forbidden" et "404 Route non trouvée"

### Symptômes
- `https://gestion.d-evidences.fr/` → **403 Forbidden**
- `https://gestion.d-evidences.fr/public/index.php` → **404 Route non trouvée : /public/index.php**

### Causes
1. ❌ Document Root ne pointe pas vers `/public`
2. ❌ Fichiers uploadés en root (propriétaire incorrect)
3. ❌ Permissions incorrectes

---

## ✅ Solution Rapide (5 minutes)

### Étape 1 : Corriger le propriétaire des fichiers

**IMPORTANT** : Les fichiers doivent appartenir à l'utilisateur web, pas à root.

```bash
# Se connecter en SSH en tant que root
sudo su

# Aller dans le répertoire
cd /home/gestion/public_html

# Changer le propriétaire (remplacer 'gestion' si votre utilisateur est différent)
chown -R gestion:gestion .

# Vérifier
ls -la
# La colonne propriétaire doit afficher "gestion gestion" et non "root root"
```

### Étape 2 : Corriger les permissions

```bash
# Toujours dans /home/gestion/public_html

# Permissions des dossiers (755)
find . -type d -exec chmod 755 {} \;

# Permissions des fichiers (644)
find . -type f -exec chmod 644 {} \;

# Permissions spéciales : dossiers d'écriture (770)
chmod 770 logs cache sessions tmp backups
chmod 770 public/uploads
find public/uploads -type d -exec chmod 770 {} \;

# Permissions spéciales : fichiers de config (640)
chmod 640 config/database.php config/app.php config/settings.php
```

### Étape 3 : Configurer le Document Root dans Virtualmin

**Option A : Via l'interface Virtualmin (recommandé)**

1. Connectez-vous à **Virtualmin** : `https://votre-serveur.com:10000`
2. Sélectionnez le domaine : **gestion.d-evidences.fr**
3. Allez dans : **Server Configuration** → **Website Options**
4. Cherchez : **Document Root**
5. Changez la valeur de :
   ```
   /home/gestion/public_html
   ```
   vers :
   ```
   /home/gestion/public_html/public
   ```
6. Cliquez sur **Save**
7. Redémarrez Apache : **Server Configuration** → **Restart Apache**

**Option B : Via fichier de configuration (avancé)**

```bash
# Trouver le fichier de config VirtualHost
grep -r "gestion.d-evidences.fr" /etc/apache2/sites-available/
# ou
grep -r "gestion.d-evidences.fr" /etc/httpd/conf.d/

# Éditer le fichier (exemple)
nano /etc/apache2/sites-available/gestion.d-evidences.fr.conf

# Modifier la ligne DocumentRoot
DocumentRoot /home/gestion/public_html/public

# ET la directive Directory
<Directory /home/gestion/public_html/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

# Sauvegarder et redémarrer Apache
systemctl restart apache2
# ou
systemctl restart httpd
```

### Étape 4 : Vérifier le fichier .htaccess

```bash
# Vérifier que le fichier existe
ls -la /home/gestion/public_html/public/.htaccess

# Vérifier les permissions (doit être 644)
chmod 644 /home/gestion/public_html/public/.htaccess

# Vérifier le contenu (doit contenir RewriteEngine On)
cat /home/gestion/public_html/public/.htaccess
```

### Étape 5 : Vérifier mod_rewrite

```bash
# Vérifier que mod_rewrite est activé
apache2ctl -M | grep rewrite
# Doit afficher : rewrite_module (shared)

# Si absent, activer :
a2enmod rewrite
systemctl restart apache2
```

---

## 🧪 Test Final

Après avoir appliqué toutes les corrections :

1. **Testez l'accès direct** : `https://gestion.d-evidences.fr/`
   - ✅ Devrait afficher la page de login
   - ❌ Si erreur, voir section Dépannage ci-dessous

2. **Testez l'accès à index.php** : `https://gestion.d-evidences.fr/index.php`
   - ✅ Devrait rediriger vers la page de login

3. **Testez un fichier statique** : `https://gestion.d-evidences.fr/assets/css/main.css`
   - ✅ Devrait afficher le CSS

---

## 🔍 Vérifications Rapides

### Vérifier la structure des fichiers

```bash
cd /home/gestion/public_html

# Vérifier que la structure est correcte
ls -la public/
# Doit contenir : index.php, .htaccess, assets/, uploads/

ls -la public/index.php
# Doit afficher : -rw-r--r-- 1 gestion gestion ... public/index.php
```

### Vérifier les logs Apache

```bash
# Erreurs Apache
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/httpd/error_log

# Logs de l'application
tail -f /home/gestion/public_html/logs/error.log
```

### Vérifier AllowOverride

```bash
# Le .htaccess doit être autorisé
grep -A 10 "gestion.d-evidences.fr" /etc/apache2/sites-available/*.conf | grep AllowOverride
# Doit afficher : AllowOverride All
```

---

## 🐛 Dépannage Spécifique

### Erreur 403 Forbidden persiste

**Cause** : Permissions ou SELinux

```bash
# 1. Vérifier les permissions du répertoire parent
ls -lad /home/gestion/
ls -lad /home/gestion/public_html/
ls -lad /home/gestion/public_html/public/

# Tous doivent avoir au moins r-x pour "others" (ex: drwxr-xr-x)

# 2. Si SELinux est actif
getenforce
# Si affiche "Enforcing" :

# Corriger les contextes SELinux
chcon -R -t httpd_sys_content_t /home/gestion/public_html/public/
chcon -R -t httpd_sys_rw_content_t /home/gestion/public_html/logs/
chcon -R -t httpd_sys_rw_content_t /home/gestion/public_html/public/uploads/

# Rendre persistant
semanage fcontext -a -t httpd_sys_content_t "/home/gestion/public_html/public(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "/home/gestion/public_html/logs(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "/home/gestion/public_html/public/uploads(/.*)?"
restorecon -Rv /home/gestion/public_html/
```

### Erreur 404 persiste

**Cause** : mod_rewrite ou .htaccess

```bash
# 1. Tester sans réécriture d'URL
https://gestion.d-evidences.fr/index.php
# Si ça marche, le problème vient de mod_rewrite

# 2. Vérifier la config Apache
apachectl -t -D DUMP_VHOSTS | grep gestion
# Doit afficher le bon DocumentRoot

# 3. Vérifier que .htaccess est lu
# Ajouter temporairement une erreur volontaire dans .htaccess
echo "ERREUR_TEST" >> /home/gestion/public_html/public/.htaccess
# Recharger la page - doit donner une erreur 500
# Si pas d'erreur 500, .htaccess n'est pas lu (AllowOverride Off)

# Retirer la ligne de test
sed -i '/ERREUR_TEST/d' /home/gestion/public_html/public/.htaccess
```

### Erreur "Route non trouvée : /public/index.php"

**Cause** : L'application s'exécute mais le Document Root n'est pas configuré

Cela signifie que vous accédez à `https://gestion.d-evidences.fr/public/index.php` au lieu de `https://gestion.d-evidences.fr/`

✅ **Solution** : Suivre l'Étape 3 ci-dessus (Configurer le Document Root)

---

## 📋 Checklist Complète

Après avoir tout configuré, vérifier :

```bash
# 1. Propriétaire correct
[ "$(stat -c %U /home/gestion/public_html/public/index.php)" = "gestion" ] && echo "✓ Propriétaire OK" || echo "✗ Propriétaire KO"

# 2. Permissions correctes
[ "$(stat -c %a /home/gestion/public_html/public/index.php)" = "644" ] && echo "✓ Permissions OK" || echo "✗ Permissions KO"

# 3. .htaccess existe
[ -f /home/gestion/public_html/public/.htaccess ] && echo "✓ .htaccess OK" || echo "✗ .htaccess KO"

# 4. Dossiers d'écriture accessibles
[ -w /home/gestion/public_html/logs ] && echo "✓ logs OK" || echo "✗ logs KO"
[ -w /home/gestion/public_html/public/uploads ] && echo "✓ uploads OK" || echo "✗ uploads KO"

# 5. Apache peut lire les fichiers
sudo -u www-data cat /home/gestion/public_html/public/index.php > /dev/null && echo "✓ Apache peut lire" || echo "✗ Apache ne peut pas lire"
```

---

## 🎯 Résumé des Commandes (Copier-Coller)

**Tout en une fois** (à exécuter en tant que root) :

```bash
# Aller dans le répertoire
cd /home/gestion/public_html

# Corriger propriétaire
chown -R gestion:gestion .

# Permissions générales
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Permissions spéciales
chmod 770 logs cache sessions tmp backups public/uploads -R
chmod 640 config/database.php config/app.php config/settings.php

# Vérifier
ls -la public/index.php
# Doit afficher : -rw-r--r-- 1 gestion gestion

echo "✅ Permissions corrigées !"
echo "⚠️  N'oubliez pas de configurer le Document Root dans Virtualmin !"
```

---

## 📞 Support

Si le problème persiste après avoir suivi ce guide :

1. **Copier les logs** :
   ```bash
   tail -50 /var/log/apache2/error.log
   tail -50 /home/gestion/public_html/logs/error.log
   ```

2. **Vérifier la configuration Apache** :
   ```bash
   apachectl -t -D DUMP_VHOSTS
   apachectl -M | grep rewrite
   ```

3. **Vérifier les permissions** :
   ```bash
   ls -laR /home/gestion/public_html/ | head -50
   ```

---

**IMPORTANT** : Le Document Root DOIT pointer vers `/home/gestion/public_html/public` et NON vers `/home/gestion/public_html/`
