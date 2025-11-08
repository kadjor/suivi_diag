#!/bin/bash

###############################################################################
# Script de restauration des droits Virtualmin pour l'application
# Usage: sudo ./restore_virtualmin_permissions.sh
###############################################################################

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=================================================${NC}"
echo -e "${GREEN}   Restauration droits Virtualmin - D-Evidences${NC}"
echo -e "${GREEN}=================================================${NC}"
echo ""

# Vérifier si exécuté en tant que root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Ce script doit être exécuté en tant que root${NC}"
    echo "Usage: sudo $0"
    exit 1
fi

# Configuration - À adapter selon votre environnement
VIRTUALMIN_USER="gestion"
APP_DIR="/home/gestion/public_html"
LOGS_DIR="/home/gestion/logs"
TMP_DIR="/home/gestion/tmp"

echo -e "${YELLOW}Configuration:${NC}"
echo "  - Utilisateur Virtualmin: $VIRTUALMIN_USER"
echo "  - Répertoire application: $APP_DIR"
echo ""

# Vérifier que le répertoire existe
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}❌ Le répertoire $APP_DIR n'existe pas${NC}"
    exit 1
fi

echo -e "${YELLOW}1. Restauration des propriétaires des fichiers...${NC}"
chown -R ${VIRTUALMIN_USER}:${VIRTUALMIN_USER} "$APP_DIR"
echo -e "${GREEN}✓ Propriétaires restaurés${NC}"

echo -e "${YELLOW}2. Configuration des permissions des répertoires...${NC}"
find "$APP_DIR" -type d -exec chmod 755 {} \;
echo -e "${GREEN}✓ Permissions répertoires: 755${NC}"

echo -e "${YELLOW}3. Configuration des permissions des fichiers...${NC}"
find "$APP_DIR" -type f -exec chmod 644 {} \;
echo -e "${GREEN}✓ Permissions fichiers: 644${NC}"

echo -e "${YELLOW}4. Configuration des répertoires d'écriture...${NC}"
# Répertoires nécessitant l'écriture
WRITABLE_DIRS=(
    "storage"
    "storage/logs"
    "storage/cache"
    "storage/sessions"
    "storage/uploads"
    "public/uploads"
    "public/reports"
)

for dir in "${WRITABLE_DIRS[@]}"; do
    if [ -d "$APP_DIR/$dir" ]; then
        chmod 775 "$APP_DIR/$dir"
        echo -e "${GREEN}✓ $dir: 775${NC}"
    else
        mkdir -p "$APP_DIR/$dir"
        chmod 775 "$APP_DIR/$dir"
        chown ${VIRTUALMIN_USER}:${VIRTUALMIN_USER} "$APP_DIR/$dir"
        echo -e "${GREEN}✓ $dir: créé avec permissions 775${NC}"
    fi
done

echo -e "${YELLOW}5. Configuration des fichiers de logs...${NC}"
if [ -d "$LOGS_DIR" ]; then
    chmod 775 "$LOGS_DIR"
    find "$LOGS_DIR" -type f -exec chmod 664 {} \;
    echo -e "${GREEN}✓ Logs configurés${NC}"
fi

echo -e "${YELLOW}6. Configuration du cache...${NC}"
if [ -d "$APP_DIR/storage/cache" ]; then
    rm -rf "$APP_DIR/storage/cache/*"
    echo -e "${GREEN}✓ Cache vidé${NC}"
fi

echo -e "${YELLOW}7. Permissions spéciales pour .htaccess...${NC}"
if [ -f "$APP_DIR/public/.htaccess" ]; then
    chmod 644 "$APP_DIR/public/.htaccess"
    echo -e "${GREEN}✓ .htaccess configuré${NC}"
fi

echo -e "${YELLOW}8. Configuration du fichier de configuration...${NC}"
if [ -f "$APP_DIR/config/app.php" ]; then
    chmod 600 "$APP_DIR/config/app.php"
    echo -e "${GREEN}✓ config/app.php: 600 (sécurisé)${NC}"
fi

if [ -f "$APP_DIR/config/database.php" ]; then
    chmod 600 "$APP_DIR/config/database.php"
    echo -e "${GREEN}✓ config/database.php: 600 (sécurisé)${NC}"
fi

echo -e "${YELLOW}9. Vérification SELinux (si activé)...${NC}"
if command -v getenforce &> /dev/null; then
    if [ "$(getenforce)" != "Disabled" ]; then
        chcon -R -t httpd_sys_rw_content_t "$APP_DIR/storage" 2>/dev/null
        chcon -R -t httpd_sys_rw_content_t "$APP_DIR/public/uploads" 2>/dev/null
        echo -e "${GREEN}✓ Contextes SELinux configurés${NC}"
    else
        echo -e "${YELLOW}⚠ SELinux désactivé${NC}"
    fi
else
    echo -e "${YELLOW}⚠ SELinux non installé${NC}"
fi

echo -e "${YELLOW}10. Rechargement Apache/Nginx...${NC}"
if systemctl is-active --quiet httpd; then
    systemctl reload httpd
    echo -e "${GREEN}✓ Apache rechargé${NC}"
elif systemctl is-active --quiet apache2; then
    systemctl reload apache2
    echo -e "${GREEN}✓ Apache rechargé${NC}"
elif systemctl is-active --quiet nginx; then
    systemctl reload nginx
    echo -e "${GREEN}✓ Nginx rechargé${NC}"
else
    echo -e "${YELLOW}⚠ Impossible de détecter le serveur web${NC}"
fi

echo ""
echo -e "${GREEN}=================================================${NC}"
echo -e "${GREEN}✅ Restauration des droits terminée avec succès !${NC}"
echo -e "${GREEN}=================================================${NC}"
echo ""

# Résumé
echo -e "${YELLOW}Résumé des permissions:${NC}"
echo "  - Propriétaire: ${VIRTUALMIN_USER}:${VIRTUALMIN_USER}"
echo "  - Répertoires: 755 (rwxr-xr-x)"
echo "  - Fichiers: 644 (rw-r--r--)"
echo "  - Répertoires d'écriture: 775 (rwxrwxr-x)"
echo "  - Fichiers config: 600 (rw-------)"
echo ""
echo -e "${YELLOW}Note:${NC} Si vous rencontrez toujours des problèmes,"
echo "       vérifiez les logs Apache/Nginx et les permissions PHP-FPM."
echo ""

exit 0
