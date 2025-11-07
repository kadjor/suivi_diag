#!/bin/bash

###############################################################################
# Script de réparation intelligent pour l'import des données
# Gère les tables manquantes et importe uniquement dans les tables existantes
# Usage: bash fix-seeds-smart.sh
###############################################################################

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "🔧 Réparation intelligente de l'import des données"
echo "=================================================="
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

echo -e "${YELLOW}[1/4]${NC} Vérification des tables existantes..."

# Récupérer la liste des tables
TABLES=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SHOW TABLES;" 2>/dev/null)
TABLE_COUNT=$(echo "$TABLES" | wc -l)

echo -e "${GREEN}✓ $TABLE_COUNT table(s) trouvée(s)${NC}"

if [ $TABLE_COUNT -lt 10 ]; then
    echo -e "${RED}⚠️  Nombre de tables insuffisant (attendu: 20+)${NC}"
    echo "Réimportez d'abord le schéma :"
    echo "  mysql -u $DB_USER -p $DB_NAME < database/schema.sql"
    exit 1
fi

echo ""
echo -e "${YELLOW}[2/4]${NC} Suppression des données existantes..."

# Désactiver les contraintes de clés étrangères
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 0;" 2>/dev/null

# Vider chaque table trouvée (dans l'ordre inverse des dépendances)
TABLES_TO_TRUNCATE="
audit_logs
order_events
messages
appointments
interventions
diagnostics
reports
orders
sites
clients
users
roles
"

for table in $TABLES_TO_TRUNCATE; do
    # Vérifier si la table existe
    if echo "$TABLES" | grep -q "^${table}$"; then
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "TRUNCATE TABLE \`$table\`;" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "  ✓ $table vidée"
        else
            echo "  ⚠️  $table : erreur (ignorée)"
        fi
    else
        echo "  - $table : n'existe pas (ignorée)"
    fi
done

# Réactiver les contraintes
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SET FOREIGN_KEY_CHECKS = 1;" 2>/dev/null

echo -e "${GREEN}✓ Tables vidées${NC}"

echo ""
echo -e "${YELLOW}[3/4]${NC} Import des données de démonstration..."

# Importer en capturant les erreurs mais en continuant
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_initial_data.sql 2>&1 | while read line; do
    if echo "$line" | grep -qi "error"; then
        echo -e "${YELLOW}  ⚠️  $line${NC}"
    fi
done

# Vérifier le code de sortie du pipe
if [ ${PIPESTATUS[0]} -eq 0 ]; then
    echo -e "${GREEN}✓ Import terminé${NC}"
else
    echo -e "${YELLOW}⚠️  Import terminé avec des avertissements${NC}"
fi

echo ""
echo -e "${YELLOW}[4/4]${NC} Vérification des données importées..."

# Vérifier les utilisateurs
USER_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM users;" 2>/dev/null)

if [ -z "$USER_COUNT" ]; then
    echo -e "${RED}✗ Impossible de compter les utilisateurs${NC}"
    exit 1
elif [ "$USER_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ $USER_COUNT utilisateur(s) créé(s)${NC}"

    echo ""
    echo -e "${BLUE}Liste des utilisateurs :${NC}"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT username, email, CONCAT(first_name, ' ', last_name) as nom FROM users LIMIT 10;" 2>/dev/null
else
    echo -e "${RED}✗ Aucun utilisateur créé${NC}"
    echo ""
    echo "Vérifiez les erreurs avec :"
    echo "  bash diagnose-seeds.sh"
    exit 1
fi

# Vérifier les autres tables importantes
echo ""
CLIENT_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM clients;" 2>/dev/null)
SITE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM sites;" 2>/dev/null)
ORDER_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM orders;" 2>/dev/null)

echo -e "${GREEN}✓ Statistiques :${NC}"
echo "  - $CLIENT_COUNT client(s)"
echo "  - $SITE_COUNT site(s)"
echo "  - $ORDER_COUNT commande(s)"

echo ""
echo "======================================================"
echo -e "${GREEN}✅ Import des données terminé !${NC}"
echo ""
echo -e "${BLUE}🔑 Comptes de démonstration :${NC}"
echo ""
echo "  📌 Mot de passe pour TOUS : ${YELLOW}password${NC}"
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
echo "🌐 Testez maintenant : https://gestion.d-evidences.fr/"
echo ""
