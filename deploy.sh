#!/bin/bash

#############################################
# Script de déploiement automatique
# - Télécharge la dernière version de la branche
# - Configure les permissions pour l'utilisateur gestion
#############################################

# Forcer l'utilisation de bash si le script est appelé avec sh
if [ -z "$BASH_VERSION" ]; then
    exec bash "$0" "$@"
fi

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
USER_OWNER="gestion"
GROUP_OWNER="gestion"
LOG_FILE="$APP_DIR/storage/logs/deploy_$(date +%Y%m%d_%H%M%S).log"

# Fonction de log
log() {
    printf "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} %s\n" "$1" | tee -a "$LOG_FILE"
}

log_error() {
    printf "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERREUR:${NC} %s\n" "$1" | tee -a "$LOG_FILE"
}

log_warning() {
    printf "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ATTENTION:${NC} %s\n" "$1" | tee -a "$LOG_FILE"
}

log_info() {
    printf "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] INFO:${NC} %s\n" "$1" | tee -a "$LOG_FILE"
}

# Vérifier que le script est exécuté en tant que root ou avec sudo
if [ "$(id -u)" -ne 0 ]; then
    log_error "Ce script doit être exécuté en tant que root (sudo)"
    exit 1
fi

# Vérifier que l'utilisateur gestion existe
if ! id "$USER_OWNER" >/dev/null 2>&1; then
    log_error "L'utilisateur '$USER_OWNER' n'existe pas sur ce système"
    exit 1
fi

log "=========================================="
log "Début du déploiement"
log "=========================================="
log_info "Répertoire de l'application: $APP_DIR"

# Se déplacer dans le répertoire de l'application
cd "$APP_DIR"

# Vérifier si c'est un dépôt Git
if [ ! -d ".git" ]; then
    log_error "Ce répertoire n'est pas un dépôt Git"
    exit 1
fi

# Récupérer la branche actuelle
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
if [ -z "$CURRENT_BRANCH" ]; then
    log_error "Impossible de déterminer la branche actuelle"
    exit 1
fi

log_info "Branche actuelle: $CURRENT_BRANCH"

# Vérifier s'il y a des changements locaux
if ! git diff-index --quiet HEAD -- 2>/dev/null; then
    log_warning "Changements locaux détectés - création d'un stash"
    git stash save "Auto-stash avant déploiement $(date '+%Y-%m-%d %H:%M:%S')" 2>&1 | tee -a "$LOG_FILE"
fi

# Mettre à jour depuis le dépôt distant
log "Récupération des dernières modifications..."
if git fetch origin 2>&1 | tee -a "$LOG_FILE"; then
    log "✓ Fetch réussi"
else
    log_error "Échec du git fetch"
    exit 1
fi

# Pull de la branche actuelle
log "Téléchargement de la branche $CURRENT_BRANCH..."
if git pull origin "$CURRENT_BRANCH" 2>&1 | tee -a "$LOG_FILE"; then
    log "✓ Pull réussi"
else
    log_error "Échec du git pull"
    exit 1
fi

# Afficher le dernier commit
LAST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%an, %ar)")
log_info "Dernier commit: $LAST_COMMIT"

# Configuration des permissions
log "Configuration des permissions pour l'utilisateur '$USER_OWNER'..."

# Changer le propriétaire de tous les fichiers
log_info "Changement du propriétaire des fichiers..."
chown -R "$USER_OWNER:$GROUP_OWNER" "$APP_DIR" 2>&1 | tee -a "$LOG_FILE"
log "✓ Propriétaire changé"

# Permissions des répertoires (755 = rwxr-xr-x)
log_info "Configuration des permissions des répertoires..."
find "$APP_DIR" -type d -exec chmod 755 {} \; 2>&1 | tee -a "$LOG_FILE"
log "✓ Permissions des répertoires: 755"

# Permissions des fichiers (644 = rw-r--r--)
log_info "Configuration des permissions des fichiers..."
find "$APP_DIR" -type f -exec chmod 644 {} \; 2>&1 | tee -a "$LOG_FILE"
log "✓ Permissions des fichiers: 644"

# Permissions spéciales pour les dossiers qui doivent être en écriture
log_info "Configuration des permissions d'écriture pour storage et cache..."
if [ -d "$APP_DIR/storage" ]; then
    chmod -R 775 "$APP_DIR/storage" 2>&1 | tee -a "$LOG_FILE"
    log "✓ storage: 775"
fi

if [ -d "$APP_DIR/storage/logs" ]; then
    chmod -R 775 "$APP_DIR/storage/logs" 2>&1 | tee -a "$LOG_FILE"
    log "✓ storage/logs: 775"
fi

if [ -d "$APP_DIR/storage/cache" ]; then
    chmod -R 775 "$APP_DIR/storage/cache" 2>&1 | tee -a "$LOG_FILE"
    log "✓ storage/cache: 775"
fi

if [ -d "$APP_DIR/storage/uploads" ]; then
    chmod -R 775 "$APP_DIR/storage/uploads" 2>&1 | tee -a "$LOG_FILE"
    log "✓ storage/uploads: 775"
fi

if [ -d "$APP_DIR/storage/backups" ]; then
    chmod -R 775 "$APP_DIR/storage/backups" 2>&1 | tee -a "$LOG_FILE"
    log "✓ storage/backups: 775"
fi

# Permissions exécutables pour les scripts
log_info "Configuration des permissions exécutables pour les scripts..."
if [ -f "$APP_DIR/deploy.sh" ]; then
    chmod 755 "$APP_DIR/deploy.sh" 2>&1 | tee -a "$LOG_FILE"
    log "✓ deploy.sh: 755"
fi

# Nettoyer le cache si le dossier existe
if [ -d "$APP_DIR/storage/cache" ]; then
    log "Nettoyage du cache..."
    rm -rf "$APP_DIR/storage/cache/"* 2>&1 | tee -a "$LOG_FILE"
    log "✓ Cache nettoyé"
fi

# Vérifications finales
log "=========================================="
log "Vérifications finales"
log "=========================================="

# Vérifier le propriétaire
OWNER_CHECK=$(stat -c '%U' "$APP_DIR/public/index.php")
if [ "$OWNER_CHECK" = "$USER_OWNER" ]; then
    log "✓ Propriétaire correct: $OWNER_CHECK"
else
    log_warning "Propriétaire inattendu: $OWNER_CHECK (attendu: $USER_OWNER)"
fi

# Vérifier les permissions
PERMS_CHECK=$(stat -c '%a' "$APP_DIR/public/index.php")
log_info "Permissions public/index.php: $PERMS_CHECK"

# Résumé
log "=========================================="
log "Déploiement terminé avec succès !"
log "=========================================="
log_info "Branche: $CURRENT_BRANCH"
log_info "Commit: $LAST_COMMIT"
log_info "Propriétaire: $USER_OWNER:$GROUP_OWNER"
log_info "Log complet: $LOG_FILE"

printf "\n"
printf "${GREEN}✓ Déploiement réussi !${NC}\n"
printf "${BLUE}Pour voir le log complet: cat %s${NC}\n" "$LOG_FILE"
printf "\n"

exit 0
