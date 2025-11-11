# Installation de l'application Suivi Diagnostics

## Prérequis

- PHP 7.4 ou supérieur
- MySQL/MariaDB
- Composer (gestionnaire de dépendances PHP)
- Git

## Installation sur le serveur

### 1. Cloner ou mettre à jour le dépôt

Si Git n'est pas encore initialisé :

```bash
cd /home/gestion/public_html
git init
git config --global --add safe.directory /home/gestion/public_html
git remote add origin https://github.com/kadjor/suivi_diag.git
git fetch origin
git checkout -b claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv origin/claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv
chown -R gestion:gestion /home/gestion/public_html
```

Ou pour mettre à jour :

```bash
cd /home/gestion/public_html
sudo sh deploy.sh
```

### 2. Installer Composer (si nécessaire)

Vérifier si Composer est installé :

```bash
composer --version
```

Si Composer n'est pas installé, l'installer :

```bash
# Télécharger l'installateur
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Vérifier l'installateur (optionnel)
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Installer Composer globalement
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Nettoyer
php -r "unlink('composer-setup.php');"

# Vérifier
composer --version
```

### 3. Installer les dépendances PHP

```bash
cd /home/gestion/public_html

# Installer les dépendances via Composer
composer install --no-dev --optimize-autoloader

# Ou si vous êtes en tant que root
sudo -u gestion composer install --no-dev --optimize-autoloader
```

Cette commande va installer :
- **PhpSpreadsheet** : Pour l'import/export de fichiers Excel
- **PHPMailer** : Pour l'envoi d'emails
- **FPDF** : Pour la génération de PDF

Les bibliothèques seront installées dans le dossier `vendor/`.

### 4. Configurer les permissions

```bash
# Donner les bonnes permissions
chown -R gestion:gestion /home/gestion/public_html
chmod -R 755 /home/gestion/public_html
chmod -R 775 /home/gestion/public_html/storage
```

### 5. Configurer la base de données

Copier et éditer le fichier de configuration :

```bash
cp config/database.example.php config/database.php
nano config/database.php
```

### 6. Exécuter les migrations

Aller sur : https://gestion.d-evidences.fr/deploy

Dans la section "Migrations", cliquer sur "Exécuter les migrations en attente".

## Mise à jour de l'application

### Méthode automatique (recommandée)

```bash
cd /home/gestion/public_html
sudo sh deploy.sh
```

Le script va :
1. Télécharger les dernières modifications depuis GitHub
2. Configurer les permissions
3. Nettoyer le cache

### Après chaque mise à jour

1. Installer/mettre à jour les dépendances Composer :
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. Exécuter les nouvelles migrations (si nécessaire) :
   - Aller sur https://gestion.d-evidences.fr/deploy
   - Exécuter les migrations en attente

## Dépannage

### Erreur : "Class 'PhpOffice\PhpSpreadsheet\IOFactory' not found"

**Solution :** Installer les dépendances Composer

```bash
cd /home/gestion/public_html
composer install --no-dev --optimize-autoloader
```

### Erreur : "composer: command not found"

**Solution :** Installer Composer (voir section 2)

### Erreur : "Failed to open stream: No such file or directory"

**Vérifications :**

1. Le dossier `vendor/` existe-t-il ?
   ```bash
   ls -la /home/gestion/public_html/vendor
   ```

2. Les permissions sont-elles correctes ?
   ```bash
   ls -la /home/gestion/public_html/
   ```

3. Réinstaller les dépendances :
   ```bash
   rm -rf vendor/
   composer install --no-dev --optimize-autoloader
   ```

### Erreur de permissions

```bash
sudo chown -R gestion:gestion /home/gestion/public_html
sudo chmod -R 755 /home/gestion/public_html
sudo chmod -R 775 /home/gestion/public_html/storage
```

## Structure des dossiers après installation

```
/home/gestion/public_html/
├── app/                    # Code de l'application
├── config/                 # Configuration
├── database/              # Migrations et schéma
├── libs/                  # Bibliothèques tierces (legacy)
├── public/                # Point d'entrée web
├── storage/               # Fichiers générés (logs, cache, uploads)
├── vendor/                # Dépendances Composer (auto-généré)
├── composer.json          # Définition des dépendances
├── composer.lock          # Versions exactes installées (auto-généré)
├── deploy.sh              # Script de déploiement
├── init_git.sh            # Script d'initialisation Git
└── README.md              # Documentation
```

## Commandes utiles

### Git

```bash
# Voir l'état du dépôt
git status

# Voir les derniers commits
git log --oneline -10

# Voir la branche actuelle
git branch

# Mettre à jour
git pull origin claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv
```

### Composer

```bash
# Installer les dépendances
composer install

# Mettre à jour les dépendances
composer update

# Voir les packages installés
composer show

# Vérifier les packages obsolètes
composer outdated
```

### Permissions

```bash
# Réparer les permissions
sudo chown -R gestion:gestion /home/gestion/public_html
sudo find /home/gestion/public_html -type d -exec chmod 755 {} \;
sudo find /home/gestion/public_html -type f -exec chmod 644 {} \;
sudo chmod -R 775 /home/gestion/public_html/storage
```

## Support

En cas de problème :
1. Consulter les logs : `/home/gestion/public_html/storage/logs/`
2. Vérifier les permissions
3. Vérifier que Composer est installé et les dépendances à jour
4. Consulter la documentation : DEPLOY_README.md

## Versions

- PHP : 7.4+
- PhpSpreadsheet : 1.29+
- PHPMailer : 6.8+
- FPDF : 1.8+
