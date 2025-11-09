# Guide du Système de Gestion du Patrimoine

## Vue d'ensemble

Le système de gestion du patrimoine permet de :
- Gérer les sites (logements) des clients
- Importer le patrimoine via Excel avec mappage flexible des colonnes
- Créer des commandes avec informations d'exécution complètes
- Lier automatiquement les sites aux commandes

## Architecture

### Base de données

#### Migration 004: Tables et champs ajoutés

**Table `orders` - Nouveaux champs :**
- `execution_address` : Adresse d'exécution de la commande
- `execution_city` : Ville
- `execution_postal_code` : Code postal
- `execution_numero_porte` : Numéro de porte
- `execution_niveau` : Niveau/Étage
- `site_id` : Référence au site du patrimoine (optionnel)
- `numero_lot` : Numéro de lot
- `bon_de_commande_pdf` : Chemin du PDF uploadé

**Table `order_diagnostics` (nouvelle) :**
Stocke les diagnostics demandés pour chaque commande avec :
- `order_id` : ID de la commande
- `diagnostic_type_id` : Type de diagnostic
- `notes` : Notes spécifiques

**Table `sites` - Champs patrimoine (Migration 003) :**
- `numero_groupe` ⚠️ **OBLIGATOIRE** pour import
- `numero_lot` ⚠️ **OBLIGATOIRE** pour import
- `nom_groupe` : Nom du groupe
- `address` ⚠️ **OBLIGATOIRE**
- `city` ⚠️ **OBLIGATOIRE**
- `postal_code` ⚠️ **OBLIGATOIRE**
- `numero_porte` : Numéro de porte
- `niveau` : Niveau/Étage
- `identifiant_fiscal` : Identifiant fiscal
- `nommage_rapport` : Nom pour les rapports
- `numero_batiment` : Numéro de bâtiment
- `numero_entree` : Numéro d'entrée
- `numero_batiment_brgm` : BRGM
- `cadastre` : Référence cadastrale
- `numero_gardien` : Numéro de gardien

**Table `site_imports` (nouvelle) :**
Historique des imports Excel avec :
- Tracking complet (lignes traitées, succès, erreurs)
- Stockage du mappage des colonnes (JSON)
- Logs détaillés ligne par ligne

### Modèles créés/modifiés

#### `Site.php`
**Nouvelles méthodes :**
- `searchByAddress($query, $clientId)` : Recherche par adresse (autocomplete)
- `findByGroupAndLot($numeroGroupe, $numeroLot, $clientId)` : Trouve un site unique
- `createOrUpdateFromImport($clientId, $data)` : Import Excel
- `getPatrimoine($clientId, $filters)` : Liste patrimoine avec filtres

#### `SiteImport.php` (nouveau)
Gestion des imports Excel :
- `createImport()`, `startProcessing()`, `updateProgress()`
- `completeImport()`, `failImport()`
- `getByClient()`, `getAllWithDetails()`

#### `OrderDiagnostic.php` (nouveau)
Gestion des diagnostics demandés :
- `getByOrder($orderId)` : Récupère diagnostics d'une commande
- `addDiagnosticsToOrder()` : Ajoute diagnostics
- `updateOrderDiagnostics()` : Met à jour diagnostics
- `countOrdersByDiagnosticType()` : Statistiques

#### `Order.php`
**Nouvelles méthodes :**
- `getWithFullDetails($id)` : Commande + site + diagnostics
- `getDiagnostics($orderId)` : Liste diagnostics
- `createWithDiagnostics()` : Création avec diagnostics
- `createSiteFromOrder($orderId)` : Crée site depuis commande

#### `Client.php`
**Nouvelle méthode :**
- `search($query)` : Recherche clients (autocomplete min 3 caractères)

### Contrôleurs

#### `SiteController.php`
**Méthodes existantes (déjà implémentées) :**
- `index()` : Liste sites avec filtres par client et rôle
- `import()` : Page d'import Excel
- `uploadExcel()` : Upload et prévisualisation
- `processExcelImport()` : Traitement avec mappage
- `searchClients()` : API autocomplete clients
- `searchSites()` : API recherche sites

**Corrections apportées :**
- Ordre des paramètres `searchByAddress()` et `findByGroupAndLot()`

#### `OrderController.php`
**Méthodes mises à jour :**
- `create()` : Formulaire avec diagnostics
- `store()` : Création complète avec :
  - Génération numéro de commande
  - Upload PDF bon de commande
  - Gestion diagnostics demandés
  - Création automatique site si adresse fournie
- `handlePdfUpload()` : Upload sécurisé PDF

### Routes ajoutées

```php
// Import Excel patrimoine
GET  /sites/import
POST /sites/upload-excel
POST /sites/process-import

// API
GET  /api/sites/search-clients?q={query}
GET  /api/sites/search?client_id={id}&q={query}
```

## Utilisation

### 1. Import Excel du patrimoine

**Étape 1 : Préparer le fichier Excel**

Format attendu :
- Première ligne = en-têtes (noms de colonnes libres)
- Lignes suivantes = données

Champs obligatoires (doivent être mappés) :
- ✅ Numéro de groupe
- ✅ Numéro de lot
- ✅ Adresse
- ✅ Ville
- ✅ Code postal

Champs optionnels :
- Nom du groupe
- Numéro de porte
- Niveau
- Identifiant fiscal
- Nommage rapport
- Etc.

**Étape 2 : Upload et mappage**

1. Aller sur `/sites/import`
2. Sélectionner le client
3. Uploader le fichier Excel (.xls ou .xlsx)
4. Le système affiche les en-têtes et 5 lignes d'aperçu
5. Mapper chaque colonne Excel vers les champs de la base
6. Valider l'import

**Étape 3 : Traitement**

Le système :
- Vérifie les champs obligatoires
- Pour chaque ligne :
  - Recherche si le site existe (via `numero_groupe` + `numero_lot`)
  - Met à jour si existant
  - Crée si nouveau
- Enregistre le log complet dans `site_imports`

**Résultat :**
- Nombre de sites créés
- Nombre de sites mis à jour
- Liste des erreurs ligne par ligne
- Liste des avertissements

### 2. Créer une commande

**Formulaire de création (`/orders/create`) :**

1. **Client** (obligatoire)
   - Sélection du client
   - Déclenche le chargement des sites du patrimoine

2. **Site du patrimoine** (optionnel)
   - Autocomplete sur les sites du client
   - Si sélectionné, pré-remplit les champs d'exécution

3. **Adresse d'exécution** (si pas de site)
   - Adresse
   - Ville
   - Code postal
   - Numéro de porte
   - Niveau

4. **Numéro de lot** (optionnel)

5. **Diagnostics demandés** (obligatoire)
   - Cases à cocher des types de diagnostics
   - Notes spécifiques par diagnostic

6. **Bon de commande PDF** (optionnel)
   - Upload du PDF
   - Stocké dans `/public/uploads/orders/`

**Comportement automatique :**
- Génération automatique du numéro de commande (CMD-YYYYMMDD-XXXX)
- Si adresse fournie sans site : création automatique d'un site
- Liaison automatique site ↔ commande
- Création des diagnostics demandés

### 3. Consulter le patrimoine

**Page Sites (`/sites`) :**

**Pour les clients :**
- Voient uniquement leurs sites
- Liste avec recherche par adresse

**Pour admin/bureau/techniciens :**
- Sélecteur de client (autocomplete min 3 caractères)
- Liste des sites du client sélectionné
- Recherche par adresse
- Affichage : adresse, lot, groupe, nombre de commandes

## Sécurité et permissions

- Import Excel : `manage_sites`
- Création commandes : `create_orders`
- Vue patrimoine client : Clients voient uniquement leurs sites
- Upload PDF : Validation format, taille, type MIME

## Fichiers modifiés

**Migrations :**
- `003_enhance_sites_and_orders.sql` (existante)
- `004_enhance_orders_execution_details.sql` (nouvelle)

**Modèles :**
- `app/Models/Site.php` (enrichi)
- `app/Models/SiteImport.php` (nouveau)
- `app/Models/OrderDiagnostic.php` (nouveau)
- `app/Models/Order.php` (enrichi)
- `app/Models/Client.php` (enrichi)

**Contrôleurs :**
- `app/Controllers/SiteController.php` (corrigé)
- `app/Controllers/OrderController.php` (enrichi)

**Routes :**
- `public/index.php` (routes API et import ajoutées)

## Prochaines étapes

⏳ **Vues à créer** (le backend est complet) :
1. `app/Views/sites/index.php` - Liste patrimoine avec sélecteur client
2. `app/Views/sites/import.php` - Interface import Excel avec mappage
3. `app/Views/orders/create.php` - Formulaire création commande complet

Ces vues utiliseront les APIs déjà en place :
- `/api/sites/search-clients` - Autocomplete clients
- `/api/sites/search` - Autocomplete sites
- `/sites/upload-excel` - Upload et aperçu
- `/sites/process-import` - Traitement import

## Notes importantes

✅ **Import Excel flexible** : Le mappage permet à chaque client d'avoir des noms de colonnes différents

✅ **Unicité des sites** : Un site est unique par combinaison (`client_id`, `numero_groupe`, `numero_lot`)

✅ **Création automatique** : Si une commande a une adresse mais pas de site, le système crée le site automatiquement

✅ **Historique complet** : Tous les imports sont tracés dans `site_imports` avec logs détaillés
