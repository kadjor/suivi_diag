#!/bin/bash

###############################################################################
# Script de configuration de la base de données
# Usage: bash setup-database.sh
# Note: S'exécute avec l'utilisateur courant (ex: gestion)
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

# Détecter l'utilisateur actuel
CURRENT_USER=$(whoami)
echo -e "${BLUE}Utilisateur actuel :${NC} $CURRENT_USER"
echo ""

# Configuration par défaut pour Virtualmin
DEFAULT_DB_NAME="$CURRENT_USER"
DEFAULT_DB_USER="$CURRENT_USER"
DEFAULT_DB_HOST="localhost"

# Lire la configuration existante si elle existe
if [ -f "config/database.php" ]; then
    CONFIG_DB_NAME=$(grep "'database'" config/database.php | cut -d"'" -f4)
    CONFIG_DB_USER=$(grep "'username'" config/database.php | cut -d"'" -f4)
    CONFIG_DB_HOST=$(grep "'host'" config/database.php | cut -d"'" -f4)

    if [ "$CONFIG_DB_NAME" != "CHANGE_ME" ] && [ -n "$CONFIG_DB_NAME" ]; then
        DEFAULT_DB_NAME="$CONFIG_DB_NAME"
    fi
    if [ "$CONFIG_DB_USER" != "CHANGE_ME" ] && [ -n "$CONFIG_DB_USER" ]; then
        DEFAULT_DB_USER="$CONFIG_DB_USER"
    fi
    if [ -n "$CONFIG_DB_HOST" ]; then
        DEFAULT_DB_HOST="$CONFIG_DB_HOST"
    fi
fi

# Demander confirmation de la configuration
echo -e "${YELLOW}Configuration de la base de données :${NC}"
echo ""
read -p "Nom de la base de données [$DEFAULT_DB_NAME]: " DB_NAME
DB_NAME=${DB_NAME:-$DEFAULT_DB_NAME}

read -p "Utilisateur MySQL [$DEFAULT_DB_USER]: " DB_USER
DB_USER=${DB_USER:-$DEFAULT_DB_USER}

read -p "Hôte MySQL [$DEFAULT_DB_HOST]: " DB_HOST
DB_HOST=${DB_HOST:-$DEFAULT_DB_HOST}

echo ""
echo -e "${YELLOW}Mot de passe MySQL pour l'utilisateur '$DB_USER' :${NC}"
read -s DB_PASS
echo ""

if [ -z "$DB_PASS" ]; then
    echo -e "${RED}❌ Le mot de passe ne peut pas être vide${NC}"
    exit 1
fi

# Résumé
echo ""
echo -e "${BLUE}Configuration à utiliser :${NC}"
echo "  Base de données : $DB_NAME"
echo "  Utilisateur     : $DB_USER"
echo "  Hôte           : $DB_HOST"
echo ""

read -p "Continuer avec cette configuration ? [o/N]: " confirm
if [ "$confirm" != "o" ] && [ "$confirm" != "O" ]; then
    echo "Annulé."
    exit 0
fi

# Étape 1 : Tester la connexion
echo ""
echo -e "${YELLOW}[1/5]${NC} Test de connexion MySQL..."

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" > /dev/null 2>&1

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Impossible de se connecter à MySQL${NC}"
    echo "Vérifiez vos identifiants MySQL"
    exit 1
fi

echo -e "${GREEN}✓ Connexion MySQL réussie${NC}"

# Étape 2 : Vérifier/Créer la base de données
echo -e "${YELLOW}[2/5]${NC} Vérification de la base de données..."

DB_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" 2>/dev/null | grep -c "$DB_NAME")

if [ "$DB_EXISTS" -eq 0 ]; then
    echo -e "${YELLOW}  ⚠️  Base de données '$DB_NAME' n'existe pas${NC}"
    echo "  Création de la base de données..."

    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}  ✓ Base de données créée${NC}"
    else
        echo -e "${RED}  ❌ Erreur lors de la création${NC}"
        echo "  Vous devez créer la base manuellement dans Virtualmin"
        exit 1
    fi
else
    echo -e "${GREEN}  ✓ Base de données '$DB_NAME' existe${NC}"

    # Vérifier si des tables existent
    TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l)

    if [ "$TABLE_COUNT" -gt 1 ]; then
        echo -e "${YELLOW}  ⚠️  La base contient déjà $((TABLE_COUNT-1)) table(s)${NC}"
        read -p "  Voulez-vous SUPPRIMER toutes les tables et réinstaller ? [o/N]: " confirm_drop

        if [ "$confirm_drop" = "o" ] || [ "$confirm_drop" = "O" ]; then
            mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 0;" 2>/dev/null

            TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | tail -n +2)

            for table in $TABLES; do
                mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DROP TABLE IF EXISTS \`$table\`;" 2>/dev/null
            done

            mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 1;" 2>/dev/null
            echo -e "${GREEN}  ✓ Tables supprimées${NC}"
        else
            echo "  Installation annulée"
            exit 0
        fi
    fi
fi

# Étape 3 : Import du schéma
echo -e "${YELLOW}[3/5]${NC} Import du schéma de la base de données..."

if [ ! -f "database/schema.sql" ]; then
    echo -e "${RED}  ❌ Fichier database/schema.sql introuvable${NC}"
    exit 1
fi

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql 2>/dev/null

if [ $? -eq 0 ]; then
    TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l)
    echo -e "${GREEN}  ✓ Schéma importé ($((TABLE_COUNT-1)) tables créées)${NC}"
else
    echo -e "${RED}  ❌ Erreur lors de l'import du schéma${NC}"
    exit 1
fi

# Étape 4 : Import des données de démonstration
echo -e "${YELLOW}[4/5]${NC} Import des données de démonstration..."

if [ ! -f "database/seeds/001_initial_data.sql" ]; then
    echo -e "${RED}  ❌ Fichier database/seeds/001_initial_data.sql introuvable${NC}"
    exit 1
fi

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_initial_data.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}  ✓ Données de démonstration importées${NC}"
else
    echo -e "${RED}  ❌ Erreur lors de l'import des données${NC}"
    exit 1
fi

# Étape 5 : Mise à jour du fichier de configuration
echo -e "${YELLOW}[5/5]${NC} Mise à jour de config/database.php..."

if [ -f "config/database.php" ]; then
    # Sauvegarder l'ancien fichier
    cp config/database.php config/database.php.backup

    # Créer le nouveau fichier
    cat > config/database.php <<EOF
<?php
/**
 * Configuration de la base de données
 * Généré automatiquement par setup-database.sh
 */

return [
    'driver' => 'mysql',
    'host' => '$DB_HOST',
    'port' => 3306,
    'database' => '$DB_NAME',
    'username' => '$DB_USER',
    'password' => '$DB_PASS',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
EOF

    chmod 640 config/database.php
    echo -e "${GREEN}  ✓ Fichier config/database.php mis à jour${NC}"
    echo -e "${BLUE}  ℹ️  Sauvegarde : config/database.php.backup${NC}"
fi

# Vérification finale
echo ""
echo -e "${BLUE}Vérification des utilisateurs créés :${NC}"
echo ""

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT username, email, CONCAT(first_name, ' ', last_name) as nom FROM users;" 2>/dev/null

echo ""
echo "============================================================"
echo -e "${GREEN}✅ Base de données configurée avec succès !${NC}"
echo ""
echo -e "${BLUE}🔑 Comptes de démonstration :${NC}"
echo ""
echo "  📌 Mot de passe pour TOUS les comptes : ${YELLOW}password${NC}"
echo ""
echo "  👤 Administrateur :"
echo "     Identifiant : admin"
echo "     Mot de passe : password"
echo ""
echo "  👤 Secrétariat :"
echo "     Identifiant : secretariat"
echo "     Mot de passe : password"
echo ""
echo "  👤 Technicien :"
echo "     Identifiant : tech1"
echo "     Mot de passe : password"
echo ""
echo "  👤 Client :"
echo "     Identifiant : client.pch"
echo "     Mot de passe : password"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT :${NC}"
echo "   Changez ces mots de passe après la première connexion !"
echo ""
echo "🌐 Accédez maintenant à l'application :"
echo "   https://gestion.d-evidences.fr/"
echo ""
echo "📖 Pour plus d'aide, consultez MOTS_DE_PASSE.md"
echo ""
