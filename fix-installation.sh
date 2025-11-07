#!/bin/bash

###############################################################################
# Script de correction automatique pour l'installation
# Usage: sudo bash fix-installation.sh
###############################################################################

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔧 Script de correction automatique - Suivi Diagnostics"
echo "========================================================"
echo ""

# Vérifier qu'on est root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Ce script doit être exécuté en tant que root${NC}"
    echo "Utilisation : sudo bash fix-installation.sh"
    exit 1
fi

# Détecter le répertoire d'installation
if [ -z "$1" ]; then
    read -p "Chemin complet de l'installation [/home/gestion/public_html]: " APP_DIR
    APP_DIR=${APP_DIR:-/home/gestion/public_html}
else
    APP_DIR="$1"
fi

# Vérifier que le répertoire existe
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}❌ Le répertoire $APP_DIR n'existe pas${NC}"
    exit 1
fi

# Détecter l'utilisateur web
WEB_USER=$(stat -c '%U' "$APP_DIR" 2>/dev/null)
if [ "$WEB_USER" = "root" ] || [ -z "$WEB_USER" ]; then
    # Essayer de détecter depuis le chemin
    WEB_USER=$(echo "$APP_DIR" | cut -d'/' -f3)
    read -p "Utilisateur web détecté: $WEB_USER. Est-ce correct? [o/N]: " confirm
    if [ "$confirm" != "o" ] && [ "$confirm" != "O" ]; then
        read -p "Entrez le nom de l'utilisateur web: " WEB_USER
    fi
fi

echo ""
echo -e "${YELLOW}Configuration détectée :${NC}"
echo "  Répertoire : $APP_DIR"
echo "  Utilisateur web : $WEB_USER"
echo ""

read -p "Continuer avec cette configuration? [o/N]: " confirm
if [ "$confirm" != "o" ] && [ "$confirm" != "O" ]; then
    echo "Annulé."
    exit 0
fi

echo ""
echo "🚀 Début de la correction..."
echo ""

# Aller dans le répertoire
cd "$APP_DIR" || exit 1

# Étape 1 : Corriger le propriétaire
echo -e "${YELLOW}[1/5]${NC} Correction du propriétaire des fichiers..."
chown -R "$WEB_USER:$WEB_USER" .
echo -e "${GREEN}✓${NC} Propriétaire corrigé : $WEB_USER:$WEB_USER"
echo ""

# Étape 2 : Permissions des dossiers
echo -e "${YELLOW}[2/5]${NC} Configuration des permissions des dossiers..."
find . -type d -exec chmod 755 {} \;
echo -e "${GREEN}✓${NC} Permissions des dossiers : 755 (rwxr-xr-x)"
echo ""

# Étape 3 : Permissions des fichiers
echo -e "${YELLOW}[3/5]${NC} Configuration des permissions des fichiers..."
find . -type f -exec chmod 644 {} \;
echo -e "${GREEN}✓${NC} Permissions des fichiers : 644 (rw-r--r--)"
echo ""

# Étape 4 : Permissions spéciales
echo -e "${YELLOW}[4/5]${NC} Configuration des permissions spéciales..."

# Dossiers d'écriture
for dir in logs cache sessions tmp backups; do
    if [ -d "$dir" ]; then
        chmod 770 "$dir"
        echo -e "${GREEN}✓${NC} $dir : 770 (rwxrwx---)"
    fi
done

if [ -d "public/uploads" ]; then
    chmod 770 public/uploads
    find public/uploads -type d -exec chmod 770 {} \;
    echo -e "${GREEN}✓${NC} public/uploads (récursif) : 770"
fi

# Fichiers de configuration
if [ -f "config/database.php" ]; then
    chmod 640 config/database.php
    echo -e "${GREEN}✓${NC} config/database.php : 640 (rw-r-----)"
fi

if [ -f "config/app.php" ]; then
    chmod 640 config/app.php
    echo -e "${GREEN}✓${NC} config/app.php : 640"
fi

if [ -f "config/settings.php" ]; then
    chmod 640 config/settings.php
    echo -e "${GREEN}✓${NC} config/settings.php : 640"
fi

echo ""

# Étape 5 : Vérifications
echo -e "${YELLOW}[5/5]${NC} Vérifications..."

# Vérifier index.php
if [ -f "public/index.php" ]; then
    OWNER=$(stat -c '%U' public/index.php)
    PERMS=$(stat -c '%a' public/index.php)
    if [ "$OWNER" = "$WEB_USER" ] && [ "$PERMS" = "644" ]; then
        echo -e "${GREEN}✓${NC} public/index.php : OK ($OWNER:$PERMS)"
    else
        echo -e "${RED}✗${NC} public/index.php : Problème ($OWNER:$PERMS)"
    fi
else
    echo -e "${RED}✗${NC} public/index.php : INTROUVABLE"
fi

# Vérifier .htaccess
if [ -f "public/.htaccess" ]; then
    echo -e "${GREEN}✓${NC} public/.htaccess : Présent"
else
    echo -e "${RED}✗${NC} public/.htaccess : MANQUANT"
fi

# Vérifier logs accessible en écriture
if [ -w "logs" ]; then
    echo -e "${GREEN}✓${NC} logs/ : Écriture autorisée"
else
    echo -e "${RED}✗${NC} logs/ : Écriture refusée"
fi

# Vérifier uploads accessible en écriture
if [ -w "public/uploads" ]; then
    echo -e "${GREEN}✓${NC} public/uploads/ : Écriture autorisée"
else
    echo -e "${RED}✗${NC} public/uploads/ : Écriture refusée"
fi

echo ""
echo "========================================================"
echo -e "${GREEN}✅ Correction des permissions terminée !${NC}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT - Prochaine étape :${NC}"
echo ""
echo "Configurez le Document Root dans Virtualmin :"
echo "  1. Connectez-vous à Virtualmin"
echo "  2. Sélectionnez votre domaine"
echo "  3. Server Configuration → Website Options"
echo "  4. Changez Document Root vers :"
echo -e "     ${GREEN}$APP_DIR/public${NC}"
echo "  5. Sauvegardez et redémarrez Apache"
echo ""
echo "Testez ensuite : https://votre-domaine.com/"
echo ""
echo "📖 Pour plus d'aide, consultez TROUBLESHOOTING.md"
echo ""
