#!/bin/bash

###############################################################################
# Script de réparation pour l'import des données de démonstration
# Usage: bash fix-seeds-import.sh
###############################################################################

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "🔧 Réparation de l'import des données de démonstration"
echo "======================================================"
echo ""

# Lire la configuration
if [ -f "config/database.php" ]; then
    DB_NAME=$(grep "'database'" config/database.php | cut -d"'" -f4)
    DB_USER=$(grep "'username'" config/database.php | cut -d"'" -f4)
    DB_PASS=$(grep "'password'" config/database.php | cut -d"'" -f4)
    DB_HOST=$(grep "'host'" config/database.php | cut -d"'" -f4)

    echo -e "${BLUE}Configuration :${NC}"
    echo "  Base de données : $DB_NAME"
    echo "  Utilisateur : $DB_USER"
    echo ""
else
    echo -e "${RED}❌ Fichier config/database.php introuvable${NC}"
    exit 1
fi

echo -e "${YELLOW}⚠️  ATTENTION : Ce script va :${NC}"
echo "  1. Supprimer TOUTES les données existantes (tables conservées)"
echo "  2. Réimporter les données de démonstration"
echo ""
read -p "Continuer ? [o/N]: " confirm

if [ "$confirm" != "o" ] && [ "$confirm" != "O" ]; then
    echo "Annulé."
    exit 0
fi

echo ""
echo -e "${YELLOW}[1/3]${NC} Suppression des données existantes..."

# Désactiver les contraintes de clés étrangères temporairement
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
SET FOREIGN_KEY_CHECKS = 0;

-- Vider les tables dans l'ordre inverse des dépendances
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE order_events;
TRUNCATE TABLE messages;
TRUNCATE TABLE appointments;
TRUNCATE TABLE interventions;
TRUNCATE TABLE diagnostics;
TRUNCATE TABLE reports;
TRUNCATE TABLE orders;
TRUNCATE TABLE sites;
TRUNCATE TABLE clients;
TRUNCATE TABLE users;
TRUNCATE TABLE roles;

SET FOREIGN_KEY_CHECKS = 1;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Données supprimées${NC}"
else
    echo -e "${RED}✗ Erreur lors de la suppression${NC}"
    exit 1
fi

echo -e "${YELLOW}[2/3]${NC} Import des données de démonstration..."

# Importer les données
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_initial_data.sql 2>&1 | grep -i error

if [ ${PIPESTATUS[0]} -eq 0 ]; then
    echo -e "${GREEN}✓ Données importées avec succès${NC}"
else
    echo -e "${RED}✗ Erreur lors de l'import${NC}"
    echo ""
    echo "Essayez d'importer avec le diagnostic complet :"
    echo "  bash diagnose-seeds.sh"
    exit 1
fi

echo -e "${YELLOW}[3/3]${NC} Vérification..."

# Compter les utilisateurs
USER_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM users;" 2>/dev/null)

if [ "$USER_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ $USER_COUNT utilisateur(s) créé(s)${NC}"

    echo ""
    echo -e "${BLUE}Liste des utilisateurs :${NC}"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT username, email, CONCAT(first_name, ' ', last_name) as nom FROM users;" 2>/dev/null
else
    echo -e "${RED}✗ Aucun utilisateur trouvé${NC}"
    exit 1
fi

echo ""
echo "======================================================"
echo -e "${GREEN}✅ Réparation terminée !${NC}"
echo ""
echo -e "${BLUE}🔑 Comptes de démonstration :${NC}"
echo ""
echo "  Mot de passe pour TOUS : ${YELLOW}password${NC}"
echo ""
echo "  - admin / password"
echo "  - secretariat / password"
echo "  - tech1 / password"
echo "  - client.pch / password"
echo ""
echo "🌐 Testez maintenant : https://gestion.d-evidences.fr/"
echo ""
