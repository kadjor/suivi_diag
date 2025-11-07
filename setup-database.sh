#!/bin/bash

###############################################################################
# Script de vérification et création de la base de données
# Usage: bash setup-database.sh
###############################################################################

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "🗄️  Configuration de la base de données - Suivi Diagnostics"
echo "============================================================"
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
    echo "  Hôte : $DB_HOST"
    echo ""
else
    echo -e "${RED}❌ Fichier config/database.php introuvable${NC}"
    echo "Veuillez d'abord configurer config/database.php"
    exit 1
fi

# Vérifier les informations
read -p "Ces informations sont-elles correctes ? [o/N]: " confirm
if [ "$confirm" != "o" ] && [ "$confirm" != "O" ]; then
    echo "Veuillez modifier config/database.php puis relancer ce script"
    exit 0
fi

# Demander le mot de passe MySQL root
echo ""
echo -e "${YELLOW}Entrez le mot de passe root MySQL pour créer la base :${NC}"
read -s MYSQL_ROOT_PASS
echo ""

# Étape 1 : Vérifier si la base existe
echo -e "${YELLOW}[1/4]${NC} Vérification de la base de données..."

DB_EXISTS=$(mysql -u root -p"$MYSQL_ROOT_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" 2>/dev/null | grep -c "$DB_NAME")

if [ "$DB_EXISTS" -eq 0 ]; then
    echo -e "${YELLOW}  ⚠️  Base de données '$DB_NAME' n'existe pas${NC}"
    echo -e "${YELLOW}[2/4]${NC} Création de la base de données..."

    mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'$DB_HOST';
FLUSH PRIVILEGES;
EOF

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}  ✓ Base de données créée avec succès${NC}"
    else
        echo -e "${RED}  ❌ Erreur lors de la création de la base${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}  ✓ Base de données '$DB_NAME' existe${NC}"
    echo -e "${YELLOW}[2/4]${NC} Suppression et recréation de la base..."

    read -p "  ⚠️  ATTENTION : Cela va SUPPRIMER toutes les données existantes. Continuer ? [o/N]: " confirm_drop
    if [ "$confirm_drop" != "o" ] && [ "$confirm_drop" != "O" ]; then
        echo "Opération annulée"
        exit 0
    fi

    mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
DROP DATABASE IF EXISTS \`$DB_NAME\`;
CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'$DB_HOST';
FLUSH PRIVILEGES;
EOF

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}  ✓ Base de données recréée${NC}"
    else
        echo -e "${RED}  ❌ Erreur${NC}"
        exit 1
    fi
fi

# Étape 3 : Import du schéma
echo -e "${YELLOW}[3/4]${NC} Import du schéma de la base de données..."

if [ ! -f "database/schema.sql" ]; then
    echo -e "${RED}  ❌ Fichier database/schema.sql introuvable${NC}"
    exit 1
fi

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}  ✓ Schéma importé (20+ tables créées)${NC}"
else
    echo -e "${RED}  ❌ Erreur lors de l'import du schéma${NC}"
    exit 1
fi

# Étape 4 : Import des données de démonstration
echo -e "${YELLOW}[4/4]${NC} Import des données de démonstration..."

if [ ! -f "database/seeds/001_initial_data.sql" ]; then
    echo -e "${RED}  ❌ Fichier database/seeds/001_initial_data.sql introuvable${NC}"
    exit 1
fi

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_initial_data.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}  ✓ Données de démonstration importées${NC}"
else
    echo -e "${RED}  ❌ Erreur lors de l'import des données${NC}"
    exit 1
fi

# Vérifier les utilisateurs
echo ""
echo -e "${BLUE}Vérification des utilisateurs créés :${NC}"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT username, email, first_name, last_name FROM users;" 2>/dev/null

echo ""
echo "============================================================"
echo -e "${GREEN}✅ Base de données configurée avec succès !${NC}"
echo ""
echo -e "${BLUE}Comptes de démonstration :${NC}"
echo ""
echo "  Administrateur :"
echo "    Identifiant : admin"
echo "    Mot de passe : password"
echo ""
echo "  Secrétariat :"
echo "    Identifiant : secretariat"
echo "    Mot de passe : password"
echo ""
echo "  Technicien :"
echo "    Identifiant : tech1"
echo "    Mot de passe : password"
echo ""
echo "  Client :"
echo "    Identifiant : client.pch"
echo "    Mot de passe : password"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT : Changez ces mots de passe après la première connexion !${NC}"
echo ""
echo "Vous pouvez maintenant accéder à l'application :"
echo "  https://votre-domaine.com/"
echo ""
