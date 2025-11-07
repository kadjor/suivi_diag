#!/bin/bash

###############################################################################
# Script de diagnostic pour l'import des données
# Usage: bash diagnose-seeds.sh
###############################################################################

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "🔍 Diagnostic de l'import des données de démonstration"
echo "======================================================"
echo ""

# Lire la configuration
if [ -f "config/database.php" ]; then
    DB_NAME=$(grep "'database'" config/database.php | cut -d"'" -f4)
    DB_USER=$(grep "'username'" config/database.php | cut -d"'" -f4)
    DB_PASS=$(grep "'password'" config/database.php | cut -d"'" -f4)
    DB_HOST=$(grep "'host'" config/database.php | cut -d"'" -f4)

    echo -e "${BLUE}Configuration détectée :${NC}"
    echo "  Base de données : $DB_NAME"
    echo "  Utilisateur : $DB_USER"
    echo ""
else
    echo -e "${RED}❌ Fichier config/database.php introuvable${NC}"
    exit 1
fi

# Vérifier que le fichier seeds existe
if [ ! -f "database/seeds/001_initial_data.sql" ]; then
    echo -e "${RED}❌ Fichier database/seeds/001_initial_data.sql introuvable${NC}"
    exit 1
fi

echo -e "${YELLOW}Tentative d'import avec affichage des erreurs...${NC}"
echo ""

# Essayer l'import avec affichage des erreurs
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_initial_data.sql 2>&1

EXIT_CODE=$?

echo ""
echo "======================================================"

if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ Import réussi !${NC}"
else
    echo -e "${RED}❌ Erreur lors de l'import (code: $EXIT_CODE)${NC}"
    echo ""
    echo -e "${YELLOW}Suggestions :${NC}"
    echo "1. Vérifiez l'erreur ci-dessus"
    echo "2. Les tables existent-elles déjà ?"
    echo "3. Vérifiez les contraintes de clés étrangères"
    echo ""
    echo "Pour voir les tables existantes :"
    echo "  mysql -u $DB_USER -p $DB_NAME -e 'SHOW TABLES;'"
    echo ""
    echo "Pour vider la base et réessayer :"
    echo "  bash setup-database.sh"
fi

echo ""
