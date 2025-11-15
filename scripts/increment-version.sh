#!/bin/bash

# Script pour incrémenter la version de l'application
# Usage: ./scripts/increment-version.sh

VERSION_FILE="$(dirname "$0")/../VERSION"

if [ ! -f "$VERSION_FILE" ]; then
    echo "Erreur: Fichier VERSION introuvable"
    exit 1
fi

# Lire la version actuelle
CURRENT_VERSION=$(cat "$VERSION_FILE")

# Vérifier que c'est un nombre
if ! [[ "$CURRENT_VERSION" =~ ^[0-9]+$ ]]; then
    echo "Erreur: La version actuelle n'est pas un nombre valide: $CURRENT_VERSION"
    exit 1
fi

# Incrémenter
NEW_VERSION=$((CURRENT_VERSION + 1))

# Afficher les versions
echo "Version actuelle: V$CURRENT_VERSION"
echo "Nouvelle version: V$NEW_VERSION"

# Demander confirmation
read -p "Confirmer l'incrémentation ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "$NEW_VERSION" > "$VERSION_FILE"
    echo "✅ Version mise à jour vers V$NEW_VERSION"

    # Proposer de commiter
    read -p "Créer un commit Git ? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git add VERSION
        git commit -m "chore: Incrémentation version V$CURRENT_VERSION → V$NEW_VERSION"
        echo "✅ Commit créé"
    fi
else
    echo "❌ Incrémentation annulée"
fi
