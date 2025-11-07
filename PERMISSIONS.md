# Guide des Permissions (chmod)

Ce guide liste **toutes les commandes chmod** nécessaires pour sécuriser et faire fonctionner l'application.

---

## 📋 Commandes Complètes (Copier-Coller)

### Option 1 : Depuis le répertoire racine de l'application

```bash
cd /home/username/public_html/suivi-diag

# 1. Permissions des dossiers (770 = rwxrwx---)
chmod 770 logs
chmod 770 public/uploads
chmod 770 public/uploads/reports
chmod 770 public/uploads/attachments
chmod 770 public/uploads/acknowledgments
chmod 770 public/uploads/imports
chmod 770 public/uploads/temp
chmod 770 cache
chmod 770 sessions
chmod 770 tmp
chmod 770 backups

# 2. Permissions des fichiers de configuration (640 = rw-r-----)
chmod 640 config/database.php
chmod 640 config/app.php
chmod 640 config/settings.php

# 3. Permissions des fichiers logs (660 = rw-rw----)
chmod 660 logs/*.log 2>/dev/null || true

# 4. Propriétaire des fichiers (remplacer 'username' par votre utilisateur)
chown -R username:username .

# 5. Permissions générales des fichiers PHP (644 = rw-r--r--)
find . -type f -name "*.php" -exec chmod 644 {} \;

# 6. Permissions générales des dossiers (755 = rwxr-xr-x)
find . -type d -exec chmod 755 {} \;

# 7. Re-appliquer les permissions spéciales pour les dossiers d'écriture
chmod 770 logs public/uploads cache sessions tmp backups
find public/uploads -type d -exec chmod 770 {} \;
```

### Option 2 : Script automatisé (recommandé)

Créer un fichier `fix-permissions.sh` :

```bash
#!/bin/bash

# Configuration
APP_DIR="/home/username/public_html/suivi-diag"
WEB_USER="username"  # Remplacer par votre utilisateur web

cd "$APP_DIR" || exit 1

echo "🔧 Application des permissions..."

# Dossiers avec écriture
for dir in logs cache sessions tmp backups; do
    if [ -d "$dir" ]; then
        chmod 770 "$dir"
        echo "✓ $dir : 770"
    fi
done

# Dossiers uploads
chmod 770 public/uploads
find public/uploads -type d -exec chmod 770 {} \;
echo "✓ public/uploads (récursif) : 770"

# Fichiers de configuration
chmod 640 config/database.php config/app.php config/settings.php
echo "✓ Fichiers config : 640"

# Fichiers PHP
find . -type f -name "*.php" -exec chmod 644 {} \;
echo "✓ Fichiers PHP : 644"

# Dossiers généraux
find . -type d -exec chmod 755 {} \;
echo "✓ Dossiers : 755"

# Re-appliquer les permissions d'écriture
chmod 770 logs cache sessions tmp backups public/uploads
find public/uploads -type d -exec chmod 770 {} \;

# Propriétaire
chown -R "$WEB_USER:$WEB_USER" .
echo "✓ Propriétaire : $WEB_USER"

echo "✅ Permissions appliquées avec succès !"
```

Puis exécuter :

```bash
chmod +x fix-permissions.sh
./fix-permissions.sh
```

---

## 📖 Explication des Permissions

### Codes de permission

| Code | Binaire | Signification | Usage |
|------|---------|---------------|-------|
| **755** | rwxr-xr-x | Propriétaire: tout / Autres: lecture+exécution | Dossiers standards |
| **770** | rwxrwx--- | Propriétaire+Groupe: tout / Autres: rien | Dossiers d'écriture |
| **644** | rw-r--r-- | Propriétaire: lecture+écriture / Autres: lecture | Fichiers PHP, HTML |
| **640** | rw-r----- | Propriétaire: lecture+écriture / Groupe: lecture | Config sensibles |
| **660** | rw-rw---- | Propriétaire+Groupe: lecture+écriture | Logs |
| **600** | rw------- | Propriétaire uniquement | Fichiers très sensibles |

### Dossiers nécessitant l'écriture (770)

```bash
logs/                    # Fichiers de log (app.log, error.log)
public/uploads/          # Tous les uploads utilisateurs
  ├── reports/           # Rapports PDF des diagnostics
  ├── attachments/       # Pièces jointes
  ├── acknowledgments/   # Accusés de réception
  ├── imports/           # Fichiers Excel importés
  └── temp/              # Fichiers temporaires
cache/                   # Cache de l'application
sessions/                # Sessions PHP
tmp/                     # Fichiers temporaires
backups/                 # Sauvegardes automatiques
```

### Fichiers sensibles (640)

```bash
config/database.php      # Identifiants base de données
config/app.php           # Configuration application
config/settings.php      # Paramètres métier
```

---

## 🚨 Dépannage

### Erreur : "Permission denied"

```bash
# Vérifier le propriétaire
ls -la logs/

# Si le propriétaire est root ou autre, corriger :
sudo chown -R username:username /path/to/suivi-diag
```

### Erreur : "Failed to write to log file"

```bash
# S'assurer que le dossier logs existe et est accessible
mkdir -p logs
chmod 770 logs
chown username:username logs
```

### Erreur : "Cannot upload file"

```bash
# Vérifier les permissions des dossiers uploads
chmod -R 770 public/uploads
find public/uploads -type d -exec chmod 770 {} \;
chown -R username:username public/uploads
```

### Vérifier les permissions actuelles

```bash
# Lister avec détails
ls -lah logs/
ls -lah public/uploads/
ls -lah config/

# Vérifier récursivement
find logs -type f -ls
find public/uploads -type d -ls
```

---

## 🔐 Sécurité Avancée (Production)

### 1. Restreindre davantage les fichiers sensibles

```bash
# Configuration ultra-sécurisée
chmod 600 config/database.php
chmod 600 config/app.php

# Logs en lecture seule après génération
find logs -type f -exec chmod 640 {} \;
```

### 2. SELinux (si activé)

```bash
# Autoriser Apache à écrire dans les dossiers
sudo chcon -R -t httpd_sys_rw_content_t logs/
sudo chcon -R -t httpd_sys_rw_content_t public/uploads/
sudo chcon -R -t httpd_sys_rw_content_t cache/
sudo chcon -R -t httpd_sys_rw_content_t sessions/

# Rendre persistant
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/suivi-diag/logs(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/suivi-diag/public/uploads(/.*)?"
sudo restorecon -Rv /path/to/suivi-diag
```

### 3. Virtualmin : Configuration via l'interface

1. **Webmin** → **Server Configuration** → **Website Options**
2. Configurer :
   - Document Root : `/home/username/public_html/suivi-diag/public`
   - PHP Execution Mode : **FCGId** ou **FPM**
3. **Edit PHP Configuration** :
   - `upload_max_filesize = 20M`
   - `post_max_size = 20M`
   - `memory_limit = 256M`

---

## ✅ Checklist Rapide

Après installation, vérifier :

```bash
# 1. Dossiers d'écriture accessibles
[ -w logs ] && echo "✓ logs OK" || echo "✗ logs KO"
[ -w public/uploads ] && echo "✓ uploads OK" || echo "✗ uploads KO"
[ -w cache ] && echo "✓ cache OK" || echo "✗ cache KO"

# 2. Fichiers de config sécurisés
[ "$(stat -c %a config/database.php)" = "640" ] && echo "✓ database.php OK" || echo "✗ database.php KO"

# 3. Propriétaire correct
[ "$(stat -c %U logs)" = "username" ] && echo "✓ Owner OK" || echo "✗ Owner KO"
```

---

## 📞 Support

Si vous rencontrez des problèmes de permissions :

1. Vérifier les logs Apache : `tail -f /var/log/apache2/error.log`
2. Vérifier les logs de l'application : `tail -f logs/error.log`
3. Tester les permissions : `sudo -u username touch logs/test.txt`

---

**Note Virtualmin** : L'utilisateur web est généralement le même que votre nom de domaine (ex: si votre domaine est `monsite.com`, l'utilisateur sera `monsite`).

Pour trouver votre utilisateur web :
```bash
# Méthode 1
ls -l public_html/

# Méthode 2
ps aux | grep apache | head -1

# Méthode 3 (Virtualmin)
virtualmin list-domains --simple-multiline | grep "^$(hostname)"
```
