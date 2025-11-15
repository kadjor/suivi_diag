#!/bin/bash

#############################################
# Script d'initialisation Git
# - Initialise le dépôt Git
# - Configure le remote
# - Télécharge la branche spécifiée
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

printf "${BLUE}============================================${NC}\n"
printf "${BLUE}Script d'initialisation Git${NC}\n"
printf "${BLUE}============================================${NC}\n\n"

# Vérifier si c'est déjà un dépôt Git
if [ -d ".git" ]; then
    printf "${YELLOW}⚠ Ce répertoire est déjà un dépôt Git.${NC}\n"
    printf "${YELLOW}Remote actuel:${NC}\n"
    git remote -v
    printf "\n${GREEN}Rien à faire !${NC}\n"
    exit 0
fi

# Demander l'URL du dépôt
printf "${BLUE}Quelle est l'URL de votre dépôt Git ?${NC}\n"
printf "Exemples:\n"
printf "  - https://github.com/user/repo.git\n"
printf "  - git@github.com:user/repo.git\n"
printf "  - http://127.0.0.1:xxxxx/git/kadjor/suivi_diag\n"
printf "\n${GREEN}URL du dépôt:${NC} "
read -r REPO_URL

if [ -z "$REPO_URL" ]; then
    printf "${RED}Erreur: URL du dépôt requise${NC}\n"
    exit 1
fi

# Demander la branche
printf "\n${BLUE}Quelle branche voulez-vous utiliser ?${NC}\n"
printf "Exemple: claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv\n"
printf "${GREEN}Nom de la branche:${NC} "
read -r BRANCH_NAME

if [ -z "$BRANCH_NAME" ]; then
    printf "${RED}Erreur: Nom de branche requis${NC}\n"
    exit 1
fi

printf "\n${YELLOW}===========================================${NC}\n"
printf "${YELLOW}Configuration:${NC}\n"
printf "  Répertoire: ${BLUE}$APP_DIR${NC}\n"
printf "  URL: ${BLUE}$REPO_URL${NC}\n"
printf "  Branche: ${BLUE}$BRANCH_NAME${NC}\n"
printf "${YELLOW}===========================================${NC}\n\n"

printf "${YELLOW}⚠ ATTENTION: Cette opération va:${NC}\n"
printf "  1. Initialiser Git dans ce répertoire\n"
printf "  2. Ajouter tous les fichiers existants\n"
printf "  3. Créer un commit initial\n"
printf "  4. Configurer le remote 'origin'\n"
printf "  5. Télécharger et fusionner la branche distante\n\n"

printf "${GREEN}Voulez-vous continuer ? (oui/non):${NC} "
read -r CONFIRM

if [ "$CONFIRM" != "oui" ] && [ "$CONFIRM" != "o" ]; then
    printf "${YELLOW}Opération annulée.${NC}\n"
    exit 0
fi

printf "\n${GREEN}=========================================${NC}\n"
printf "${GREEN}Début de l'initialisation${NC}\n"
printf "${GREEN}=========================================${NC}\n\n"

# 1. Initialiser Git
printf "${BLUE}[1/7]${NC} Initialisation du dépôt Git...\n"
git init
printf "${GREEN}✓ Dépôt initialisé${NC}\n\n"

# 2. Configurer Git
printf "${BLUE}[2/7]${NC} Configuration Git...\n"
git config --local core.fileMode true
git config --local core.autocrlf input
printf "${GREEN}✓ Configuration effectuée${NC}\n\n"

# 3. Ajouter le remote
printf "${BLUE}[3/7]${NC} Ajout du remote 'origin'...\n"
git remote add origin "$REPO_URL"
printf "${GREEN}✓ Remote ajouté${NC}\n\n"

# 4. Fetch depuis le remote
printf "${BLUE}[4/7]${NC} Récupération des données depuis le remote...\n"
git fetch origin
printf "${GREEN}✓ Fetch terminé${NC}\n\n"

# 5. Créer la branche locale et la lier au remote
printf "${BLUE}[5/7]${NC} Création de la branche locale '$BRANCH_NAME'...\n"
git checkout -b "$BRANCH_NAME" "origin/$BRANCH_NAME"
printf "${GREEN}✓ Branche créée et liée au remote${NC}\n\n"

# 6. Vérifier l'état
printf "${BLUE}[6/7]${NC} Vérification de l'état...\n"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
LAST_COMMIT=$(git log -1 --pretty=format:"%h - %s (%an)")
printf "  Branche actuelle: ${GREEN}$CURRENT_BRANCH${NC}\n"
printf "  Dernier commit: ${GREEN}$LAST_COMMIT${NC}\n\n"

# 7. Configurer les permissions (si root)
if [ "$(id -u)" -eq 0 ]; then
    printf "${BLUE}[7/7]${NC} Configuration des permissions pour l'utilisateur 'gestion'...\n"
    chown -R gestion:gestion "$APP_DIR"
    printf "${GREEN}✓ Permissions configurées${NC}\n\n"
else
    printf "${YELLOW}[7/7] Permissions non modifiées (nécessite root)${NC}\n\n"
fi

printf "${GREEN}============================================${NC}\n"
printf "${GREEN}✓ Initialisation terminée avec succès !${NC}\n"
printf "${GREEN}============================================${NC}\n\n"

printf "${BLUE}Vous pouvez maintenant utiliser:${NC}\n"
printf "  ${GREEN}sudo ./deploy.sh${NC}  - Pour déployer les mises à jour\n"
printf "  ${GREEN}git status${NC}         - Pour voir l'état du dépôt\n"
printf "  ${GREEN}git pull${NC}           - Pour récupérer les mises à jour\n\n"

exit 0
