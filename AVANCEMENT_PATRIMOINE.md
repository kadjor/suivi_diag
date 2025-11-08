# Avancement - Gestion du Patrimoine et Import Excel

## ✅ Réalisé

### 1. Migration de base de données
**Fichier:** `database/migrations/003_enhance_sites_and_orders.sql`

- ✅ Ajout de tous les champs patrimoine à la table `sites`:
  - numero_groupe (obligatoire)
  - numero_lot (obligatoire)
  - numero_porte
  - niveau
  - numero_batiment
  - numero_entree
  - identifiant_fiscal
  - numero_batiment_brgm
  - cadastre
  - nommage_rapport
  - numero_gardien
  - nom_groupe

- ✅ Ajout du numéro_lot à la table `orders`
- ✅ Création de la table `site_imports` pour tracker les imports Excel
- ✅ Index optimisés pour les recherches

### 2. SiteController enrichi
**Fichier:** `app/Controllers/SiteController.php`

- ✅ `index()` : Filtrage par client avec recherche
  - Clients : voient uniquement leur patrimoine
  - Admin/Bureau/Techniciens : voient tous les clients avec sélection
- ✅ `searchClients()` : API autocomplete (min 3 caractères)
- ✅ `searchSites()` : API recherche d'adresses
- ✅ `import()` : Page d'import Excel
- ✅ `uploadExcel()` : Upload + prévisualisation des en-têtes
- ✅ `processExcelImport()` : Traitement avec mapping de colonnes

## 🚧 À faire

### 3. Modèles à créer/modifier

#### Site.php (à compléter)
Ajouter les méthodes :
```php
public function searchByAddress($clientId, $query)
public function findByGroupAndLot($clientId, $numeroGroupe, $numeroLot)
```

#### Client.php (à compléter)
Ajouter la méthode :
```php
public function search($query) // Recherche par nom (min 3 car.)
```

#### SiteImport.php (à créer)
Nouveau modèle pour `site_imports`

### 4. Vues à créer/modifier

#### sites/index.php
- Zone de sélection client avec autocomplete
- Affichage du patrimoine filtré
- Recherche d'adresse
- Tableau avec nouveaux champs
- Bouton "Importer patrimoine"

#### sites/import.php (à créer)
- Sélection du client
- Upload fichier Excel
- Mapping des colonnes (drag & drop ou sélecteurs)
- Prévisualisation
- Bouton import

#### orders/create.php (à modifier)
- Ajouter champ numéro_lot
- Sélection du site avec autocomplete
- Upload bon de commande PDF

### 5. Routes à ajouter
**Fichier:** `public/index.php`

```php
// Sites
$router->get('/sites/search-clients', 'Controllers\SiteController@searchClients');
$router->get('/sites/search-sites', 'Controllers\SiteController@searchSites');
$router->get('/sites/import', 'Controllers\SiteController@import');
$router->post('/sites/upload-excel', 'Controllers\SiteController@uploadExcel');
$router->post('/sites/process-import', 'Controllers\SiteController@processExcelImport');
```

### 6. Dépendances
- PhpSpreadsheet (pour lecture Excel)
  - Vérifier si `/libs/PhpSpreadsheet/` existe
  - Sinon, installer via Composer

## 📋 Prochaines étapes recommandées

1. **Exécuter la migration** via `/deploy` > Migrations
2. **Créer les modèles manquants**
3. **Ajouter les routes**
4. **Créer les vues**
5. **Tester l'import Excel**

## 🎯 Fonctionnalités principales

### Import Excel
**Colonnes supportées :**
- ✅ Obligatoires : numero_groupe, numero_lot, address, city, postal_code
- ✅ Optionnelles : Les 7 autres champs

**Processus :**
1. Upload fichier .xls/.xlsx
2. Lecture des en-têtes
3. Mapping interactif des colonnes
4. Validation ligne par ligne
5. Création ou mise à jour des sites (par numero_groupe + numero_lot)
6. Rapport détaillé : succès / erreurs

### Permissions
- **Client** : Voit uniquement son patrimoine
- **Admin/Bureau/Techniciens** : Voient tous les clients
  - Autocomplete après 3 caractères
  - Sélection client → affichage patrimoine
  - Recherche d'adresse dans le patrimoine

## 📝 Notes techniques

- Les buffers de sortie sont nettoyés pour éviter les erreurs JSON
- Import Excel avec détection automatique des doublons (numero_groupe + numero_lot)
- Logs détaillés des imports dans `site_imports`
- Support .xls et .xlsx via PhpSpreadsheet

---

**Statut global :** 40% complété
**Prochaine étape :** Créer les modèles manquants
