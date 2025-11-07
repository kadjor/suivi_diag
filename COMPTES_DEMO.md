# 👥 Comptes de Démonstration

## Informations de connexion

**Mot de passe pour TOUS les comptes** : `Demo2024!`

## 🔑 Comptes disponibles

### 1. Administrateur
- **Identifiant** : `admin`
- **Mot de passe** : `Demo2024!`
- **Email** : admin@suivi-diag.fr
- **Permissions** : Accès complet à toutes les fonctionnalités

**Ce compte peut** :
- ✅ Gérer les utilisateurs (créer, modifier, supprimer)
- ✅ Gérer les clients et sites
- ✅ Voir toutes les commandes
- ✅ Accéder au journal d'audit complet
- ✅ Configurer les paramètres système
- ✅ Gérer les référentiels (statuts, types diagnostics)
- ✅ Exporter toutes les données
- ✅ Consulter la cartographie complète

### 2. Secrétariat
- **Identifiant** : `secretariat`
- **Mot de passe** : `Demo2024!`
- **Email** : secretariat@suivi-diag.fr
- **Permissions** : Gestion opérationnelle des commandes

**Ce compte peut** :
- ✅ Créer et éditer les bons de commande
- ✅ Générer et déposer les AR (accusés de réception)
- ✅ Assigner des techniciens aux interventions
- ✅ Planifier des rendez-vous
- ✅ Gérer la messagerie des commandes
- ✅ Valider les rapports
- ✅ Clôturer les commandes
- ✅ Exporter les commandes et interventions
- ❌ Accéder à l'administration système

### 3. Technicien
- **Identifiant** : `tech1`
- **Mot de passe** : `Demo2024!`
- **Email** : tech1@suivi-diag.fr
- **Permissions** : Gestion des interventions assignées

**Ce compte peut** :
- ✅ Voir ses interventions assignées
- ✅ Consulter son planning
- ✅ Échanger via messagerie sur ses commandes
- ✅ Déposer des rapports PDF
- ✅ Uploader des fichiers Excel pour alimenter la cartographie
- ✅ Mettre à jour les statuts d'intervention
- ✅ Voir la cartographie complète
- ❌ Voir les commandes non assignées
- ❌ Clôturer les commandes

**Autres techniciens disponibles** :
- `tech2` / Demo2024! (Marc Bernard)
- `tech3` / Demo2024! (Luc Petit)

### 4. Client PCH
- **Identifiant** : `client.pch`
- **Mot de passe** : `Demo2024!`
- **Email** : p.charron@pch-immobilier.fr
- **Organisation** : PCH Immobilier
- **Permissions** : Portail client complet

**Ce compte peut** :
- ✅ Déposer des commandes
- ✅ Ajouter des pièces jointes
- ✅ Suivre l'avancement de ses commandes
- ✅ Voir la timeline horodatée
- ✅ Échanger via messagerie
- ✅ Télécharger les rapports PDF
- ✅ Consulter la cartographie de SON patrimoine uniquement
- ✅ Filtrer les diagnostics amiante par période
- ❌ Voir les commandes d'autres clients
- ❌ Assigner des techniciens

**Autres clients disponibles** :
- `client.lyon` / Demo2024! (Marie Dubois - Société Foncière Lyon)
- `client.bdx` / Demo2024! (Jean Martin - Immobilière Bordeaux)

## 🏢 Données de démonstration

### Clients
- **PCH Immobilier** (Paris) - 5 sites
- **Société Foncière Lyon** - 2 sites
- **Immobilière Bordeaux** - 1 site

### Commandes en cours
- CMD-20240115-0001 (Clôturée)
- CMD-20240220-0002 (Rapport déposé)
- CMD-20240305-0003 (En cours)
- CMD-20240310-0004 (Planifiée)
- CMD-20240312-0005 (Nouvelle)

### Types de diagnostics
- DTA, DAPP, RAAT, RAAD (amiante)
- DPE, CREP, GAZ, ELEC, TERMITES, ERP

## 🔒 Sécurité

⚠️ **IMPORTANT - Après installation** :

1. **Changez TOUS les mots de passe** immédiatement en production
2. **Supprimez les comptes démo** non nécessaires
3. **Créez vos propres utilisateurs** avec des mots de passe forts

### Changer un mot de passe

1. Connectez-vous en tant qu'**admin**
2. Allez dans **Utilisateurs**
3. Cliquez sur **Modifier** à côté d'un utilisateur
4. Entrez le nouveau mot de passe (min 8 caractères, 1 maj, 1 min, 1 chiffre)
5. Sauvegardez

### Supprimer un compte démo

1. Connectez-vous en tant qu'**admin**
2. Allez dans **Utilisateurs**
3. Cliquez sur **Supprimer** à côté d'un utilisateur
4. Confirmez la suppression

## 📝 Scénarios de test

### Scénario Client

1. Connectez-vous avec `client.pch` / `Demo2024!`
2. Créez une nouvelle commande
3. Ajoutez une description et une pièce jointe
4. Soumettez la commande
5. Consultez la timeline de la commande
6. Envoyez un message
7. Allez dans **Cartographie**
8. Filtrez les diagnostics amiante
9. Cliquez sur un site pour voir les détails
10. Téléchargez un rapport PDF

### Scénario Secrétariat

1. Connectez-vous avec `secretariat` / `Demo2024!`
2. Voyez la nouvelle commande créée par le client
3. Générez un AR
4. Assignez la commande à un technicien
5. Planifiez un rendez-vous
6. Envoyez une notification au client

### Scénario Technicien

1. Connectez-vous avec `tech1` / `Demo2024!`
2. Consultez vos interventions assignées
3. Voyez votre planning
4. Démarrez une intervention
5. Déposez un rapport PDF
6. Uploadez un fichier Excel de cartographie
7. Marquez l'intervention comme terminée

### Scénario Administrateur

1. Connectez-vous avec `admin` / `Demo2024!`
2. Créez un nouvel utilisateur
3. Consultez le journal d'audit
4. Exportez les commandes du mois
5. Modifiez les paramètres email
6. Consultez les statistiques

## 🧪 Tests d'acceptation

Tous les scénarios de test détaillés se trouvent dans `/tests/acceptance/`

- `test_client.md`
- `test_secretariat.md`
- `test_technicien.md`
- `test_admin.md`

## 📞 Support

Si vous rencontrez des problèmes de connexion :

1. Vérifiez que l'installation s'est bien terminée
2. Vérifiez les logs : `logs/error.log`
3. Testez la connexion à la base de données
4. Consultez `docs/INSTALL.md` section Dépannage

---

**Note** : Ces comptes sont à usage de démonstration uniquement.
En production, supprimez-les et créez vos propres comptes sécurisés.
