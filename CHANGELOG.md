# Changelog - Système de suivi diagnostics

## [1.2.1] - 2025-11-10

### Corrigé
✅ **Classe DiagnosticType manquante**
- Création du modèle DiagnosticType.php
- Méthodes getActive(), getByCode(), toggleActive()
- Correction erreur "Class Models\DiagnosticType not found"

✅ **Interface import patrimoine**
- Vue sites/import.php complète avec :
  - Upload Excel et prévisualisation
  - Mappage interactif des colonnes
  - Détection automatique des correspondances
  - Affichage des résultats détaillés
- Menu navigation : Ajout "📥 Import Patrimoine" pour admin/secrétariat

✅ **Système de versioning**
- Fichier VERSION à la racine (format semver)
- CHANGELOG.md avec historique complet
- Affichage version actuelle sur page /deploy
- Permet de vérifier que le déploiement s'est bien effectué

### Documentation
- CHANGELOG.md : Historique complet des versions

---

## [1.2.0] - 2025-11-10

### Ajouté
✅ **Système de gestion du patrimoine client**
- Import Excel avec mappage flexible des colonnes
- Champs obligatoires : numero_groupe, numero_lot, adresse, ville, code postal
- Détection et mise à jour automatique des doublons
- Historique complet des imports (table site_imports)

✅ **Création de commandes enrichie**
- Numéro de commande auto-généré (CMD-YYYYMMDD-XXXX)
- Adresse d'exécution complète (ville, CP, porte, niveau)
- Sélection multiple diagnostics demandés
- Upload PDF bon de commande
- Création automatique site depuis commande si adresse fournie
- Liaison site ↔ commande

✅ **Nouvelles tables base de données**
- `order_diagnostics` : Diagnostics demandés par commande
- `site_imports` : Historique imports Excel

✅ **Nouveaux modèles**
- DiagnosticType : Gestion types diagnostics
- SiteImport : Tracking imports
- OrderDiagnostic : Liaison commandes-diagnostics

✅ **APIs**
- `/api/sites/search-clients` : Autocomplete clients (min 3 car)
- `/api/sites/search` : Recherche sites par adresse

✅ **Routes import patrimoine**
- `/sites/import` : Page import Excel
- `/sites/upload-excel` : Upload et prévisualisation
- `/sites/process-import` : Traitement avec mappage

### Modifié
- OrderController : Création complète avec diagnostics
- SiteController : Import Excel déjà fonctionnel
- Modèle Order : Méthodes site et diagnostics
- Modèle Site : Recherche et patrimoine
- Modèle Client : Autocomplete

### Corrigé
- ✅ Erreur JSON.parse dans /deploy/migrations (interception précoce)
- ✅ Classe DiagnosticType manquante

### Documentation
- GUIDE_PATRIMOINE.md : Guide complet système patrimoine
- GUIDE_DEPLOIEMENT.md : Mise à jour avec solution JSON.parse

---

## [1.1.0] - 2025-11-09

### Ajouté
- Système de déploiement avec interface web
- Migration runner via /deploy
- Support GitHub pour déploiement sans Git

### Corrigé
- Gestion erreurs SQL Virtualmin
- Support branches avec slashes dans nom

---

## [1.0.0] - 2025-11-01

### Initial
- Système de base de gestion des commandes
- Gestion clients, sites, utilisateurs
- Tableau de bord
- Authentification et permissions
