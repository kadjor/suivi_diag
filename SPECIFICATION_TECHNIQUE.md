# Spécification Technique - Plateforme de Suivi de Commandes et Cartographie des Diagnostics

## 1. Vue d'ensemble

### 1.1 Objectif
Plateforme web de gestion complète du cycle de vie des commandes de diagnostics immobiliers (amiante, DPE, etc.) avec cartographie interactive du patrimoine client.

### 1.2 Périmètre fonctionnel
- Gestion multi-rôles (Admin, Secrétariat, Technicien, Client)
- Workflow complet de commande (émission → AR → planification → réalisation → clôture)
- Timeline horodatée et journal d'audit immuable
- Messagerie contextualisée par commande
- Cartographie interactive du patrimoine avec historique diagnostics
- Import Excel pour alimenter la cartographie
- Gestion documentaire (rapports PDF, pièces jointes)
- Notifications email automatiques

## 2. Architecture Technique

### 2.1 Stack technologique

**Backend**
- PHP 8.1+ (compatible PHP 7.4+)
- MySQL 5.7+ / MariaDB 10.3+
- Architecture MVC custom légère
- PDO pour l'accès base de données
- Sessions PHP natives

**Frontend**
- HTML5 / CSS3 (responsive design)
- JavaScript vanilla (ES6+)
- Bibliothèques CDN :
  - Leaflet.js 1.9+ (cartographie)
  - Chart.js 4+ (tableaux de bord)
  - FullCalendar 6+ (planning)
  - Moment.js (dates)

**Bibliothèques PHP incluses**
- PhpSpreadsheet 1.29+ (import/export Excel)
- FPDF 1.85+ (génération PDF)
- PHPMailer 6.8+ (envoi emails)

### 2.2 Structure de fichiers

```
/
├── config/
│   ├── database.php          # Configuration base de données
│   ├── app.php               # Configuration application
│   └── settings.php          # Paramètres métier
├── app/
│   ├── Core/
│   │   ├── Router.php        # Gestionnaire de routes
│   │   ├── Controller.php    # Contrôleur de base
│   │   ├── Model.php         # Modèle de base
│   │   ├── View.php          # Moteur de templates
│   │   ├── Auth.php          # Authentification
│   │   ├── RBAC.php          # Gestion des permissions
│   │   ├── Database.php      # Connexion DB
│   │   └── Session.php       # Gestion sessions
│   ├── Models/
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Order.php
│   │   ├── Site.php
│   │   ├── Diagnostic.php
│   │   ├── Intervention.php
│   │   ├── Report.php
│   │   ├── Message.php
│   │   ├── Appointment.php
│   │   ├── AuditLog.php
│   │   └── ExcelImport.php
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── OrderController.php
│   │   ├── SiteController.php
│   │   ├── MapController.php
│   │   ├── ReportController.php
│   │   ├── MessageController.php
│   │   ├── CalendarController.php
│   │   ├── UserController.php
│   │   ├── AdminController.php
│   │   └── ExportController.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   └── auth.php
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── orders/
│   │   ├── map/
│   │   ├── calendar/
│   │   ├── messages/
│   │   ├── reports/
│   │   └── admin/
│   └── Helpers/
│       ├── Validator.php
│       ├── FileUpload.php
│       ├── Pagination.php
│       ├── ExcelParser.php
│       ├── PdfGenerator.php
│       └── Geocoder.php
├── public/
│   ├── index.php             # Point d'entrée
│   ├── .htaccess             # Réécriture URLs
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css
│   │   │   └── responsive.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── map.js
│   │   │   ├── calendar.js
│   │   │   └── orders.js
│   │   └── images/
│   └── uploads/
│       ├── reports/
│       ├── attachments/
│       ├── acknowledgments/
│       └── imports/
├── database/
│   ├── schema.sql            # Schéma complet
│   ├── migrations/           # Migrations versionnées
│   └── seeds/                # Données de démonstration
├── logs/
│   ├── app.log
│   ├── error.log
│   └── audit.log
├── libs/                      # Bibliothèques tierces
│   ├── PhpSpreadsheet/
│   ├── fpdf/
│   └── PHPMailer/
├── scripts/
│   ├── install.php           # Installation automatique
│   ├── backup.php            # Sauvegarde
│   └── migrate.php           # Migration de schéma
├── tests/
│   ├── acceptance/
│   └── manual/
└── docs/
    ├── INSTALL.md
    ├── USER_GUIDE.md
    ├── ADMIN_GUIDE.md
    └── API.md
```

## 3. Modèle de Données

### 3.1 Entités principales

#### Users (Utilisateurs)
```sql
- id (PK)
- username (unique)
- password_hash
- email (unique)
- first_name
- last_name
- role_id (FK → roles)
- client_id (FK → clients, nullable pour non-clients)
- phone
- active (boolean)
- last_login
- created_at
- updated_at
```

#### Roles (Rôles)
```sql
- id (PK)
- name (admin, secretariat, technicien, client)
- label
- permissions (JSON: {orders: {create, read, update, delete}, ...})
- created_at
```

#### Clients (Organisations clientes)
```sql
- id (PK)
- organization_name
- contact_name
- email
- phone
- address
- city
- postal_code
- country
- siret
- notes
- active
- created_at
- updated_at
```

#### Sites (Bâtiments/Patrimoine)
```sql
- id (PK)
- client_id (FK → clients)
- name
- address
- city
- postal_code
- country
- latitude
- longitude
- reference_pch (identifiant interne client)
- building_type
- construction_year
- surface
- metadata (JSON: champs personnalisés)
- created_at
- updated_at
```

#### Orders (Bons de commande)
```sql
- id (PK)
- order_number (unique, format: CMD-YYYYMMDD-XXXX)
- client_id (FK → clients)
- status_id (FK → statuses)
- priority (low, normal, high, urgent)
- requested_date
- deadline_date
- description
- custom_fields (JSON)
- created_by (FK → users)
- assigned_to (FK → users, nullable)
- closed_at
- closed_by (FK → users, nullable)
- created_at
- updated_at
```

#### Order_Events (Timeline horodatée)
```sql
- id (PK)
- order_id (FK → orders)
- event_type (created, acknowledged, assigned, scheduled, in_progress, completed, report_uploaded, closed, etc.)
- user_id (FK → users)
- timestamp (immutable)
- data (JSON: détails contextuels)
- ip_address
- is_system (boolean: événement automatique ou manuel)
```
**Index** : (order_id, timestamp)

#### Interventions
```sql
- id (PK)
- order_id (FK → orders)
- site_id (FK → sites)
- technician_id (FK → users)
- diagnostic_types (JSON array: ['DTA', 'DAPP', ...])
- scheduled_date
- completed_date
- status (pending, in_progress, completed, cancelled)
- notes
- internal_notes (non visible client)
- created_at
- updated_at
```

#### Diagnostics
```sql
- id (PK)
- site_id (FK → sites)
- order_id (FK → orders, nullable)
- intervention_id (FK → interventions, nullable)
- diagnostic_type_id (FK → diagnostic_types)
- date
- status (ok, anomaly, critical)
- criticality (0-5)
- reference_number
- valid_until
- report_id (FK → reports, nullable)
- excel_import_id (FK → excel_imports, nullable)
- metadata (JSON)
- created_at
- updated_at
```
**Index** : (site_id, date), (diagnostic_type_id, date)

#### Reports (Rapports PDF)
```sql
- id (PK)
- order_id (FK → orders)
- intervention_id (FK → interventions, nullable)
- filename
- original_filename
- filepath
- file_size
- mime_type
- version
- uploaded_by (FK → users)
- uploaded_at
- download_count
```
**Index** : (order_id, version)

#### Attachments (Pièces jointes)
```sql
- id (PK)
- order_id (FK → orders, nullable)
- message_id (FK → messages, nullable)
- filename
- original_filename
- filepath
- file_size
- mime_type
- uploaded_by (FK → users)
- uploaded_at
```

#### Messages (Messagerie par commande)
```sql
- id (PK)
- order_id (FK → orders)
- user_id (FK → users)
- recipient_id (FK → users, nullable: message visible à tous si null)
- content
- requires_response (boolean)
- response_to (FK → messages, nullable)
- read_at
- created_at
```
**Index** : (order_id, created_at)

#### Appointments (Rendez-vous)
```sql
- id (PK)
- order_id (FK → orders)
- intervention_id (FK → interventions, nullable)
- technician_id (FK → users)
- site_id (FK → sites)
- title
- start_datetime
- end_datetime
- location
- notes
- status (scheduled, confirmed, completed, cancelled)
- created_by (FK → users)
- created_at
- updated_at
```
**Index** : (technician_id, start_datetime), (order_id)

#### Acknowledgments (Accusés de réception)
```sql
- id (PK)
- order_id (FK → orders)
- type (ar_commande, ar_rapport, ar_cloture)
- filename
- filepath
- generated_by (FK → users)
- generated_at
- sent_at
```

#### Audit_Log (Journal d'audit immuable)
```sql
- id (PK)
- user_id (FK → users, nullable si système)
- action (login, logout, create, update, delete, view, download, export, etc.)
- entity_type (order, report, site, user, etc.)
- entity_id
- timestamp (immutable)
- ip_address
- user_agent
- payload (JSON: données pertinentes non sensibles)
- result (success, failure)
```
**Index** : (timestamp DESC), (user_id, timestamp), (entity_type, entity_id)
**Contrainte** : Table INSERT-only (pas de UPDATE ni DELETE)

#### Excel_Imports (Imports Excel cartographie)
```sql
- id (PK)
- report_id (FK → reports, nullable)
- order_id (FK → orders, nullable)
- filename
- filepath
- imported_by (FK → users)
- imported_at
- status (pending, processing, completed, failed)
- rows_total
- rows_processed
- rows_success
- rows_errors
- log_json (JSON: détails erreurs, warnings)
- column_mapping (JSON: correspondance colonnes Excel ↔ champs DB)
```

#### Settings (Paramètres application)
```sql
- id (PK)
- category (general, email, map, security, etc.)
- key
- value (TEXT)
- data_type (string, int, boolean, json)
- label
- description
- updated_by (FK → users)
- updated_at
```
**Unique** : (category, key)

### 3.2 Référentiels

#### Diagnostic_Types
```sql
- id (PK)
- code (DTA, DAPP, RAAT, RAAD, DPE, etc.)
- name
- description
- color (hex)
- active
- sort_order
```

#### Statuses
```sql
- id (PK)
- category (order, intervention, diagnostic)
- code
- label
- color
- icon
- sort_order
- active
```

Statuts orders : new, acknowledged, assigned, scheduled, in_progress, report_pending, completed, closed, cancelled

#### Event_Types
```sql
- id (PK)
- code
- label
- category (order, intervention, message, system)
- icon
- color
```

## 4. Workflows Clés

### 4.1 Workflow Commande Complète

```
1. ÉMISSION
   Client ou Secrétariat → Création commande
   → Event: order_created
   → Status: new

2. ACCUSÉ RÉCEPTION (obligatoire)
   Secrétariat → Génère ou dépose AR
   → Event: acknowledgment_generated
   → Status: acknowledged

3. AFFECTATION
   Secrétariat → Assigne technicien(s)
   → Event: order_assigned
   → Status: assigned

4. PLANIFICATION
   Secrétariat/Technicien → Crée rendez-vous
   → Event: appointment_scheduled
   → Status: scheduled

5. RÉALISATION
   Technicien → Débute intervention
   → Event: intervention_started
   → Status: in_progress

6. DÉPÔT RAPPORT
   Technicien → Upload PDF + Excel
   → Event: report_uploaded
   → Status: report_pending
   → Déclenche import Excel (asynchrone ou immédiat)

7. VALIDATION
   Secrétariat → Vérifie rapport
   → Event: report_validated
   → Status: completed

8. CLÔTURE
   Secrétariat/Admin → Clôture commande
   → Event: order_closed
   → Status: closed
```

Chaque transition crée un `order_event` horodaté et un `audit_log` entry.

### 4.2 Workflow Import Excel

```
1. UPLOAD
   Technicien → Upload fichier .xlsx
   → Validation format, taille, extension
   → Stockage sécurisé
   → Création excel_imports (status: pending)

2. ANALYSE
   Système → Lecture entêtes
   → Proposition mapping colonnes automatique
   → Affichage preview

3. MAPPING (si nécessaire)
   Utilisateur → Ajuste correspondances
   → Enregistre mapping

4. VALIDATION
   Système → Contrôles qualité:
     - Adresses complètes
     - Dates valides
     - Types diagnostics reconnus
     - Doublons
   → Génère rapport erreurs/warnings

5. IMPORT
   Système → Traitement ligne par ligne:
     - Géocodage adresses
     - Création/update sites
     - Création diagnostics
     - Logging détaillé
   → Update excel_imports (status: completed/failed)

6. RAPPORT
   → Affichage résumé: X lignes, Y succès, Z erreurs
   → Log JSON exploitable
   → Mise à jour carte temps réel
```

### 4.3 Politique d'Autorisations (RBAC)

| Action | Admin | Secrétariat | Technicien | Client |
|--------|-------|-------------|------------|--------|
| **Commandes** |
| Créer | ✓ | ✓ | ✗ | ✓ (limitées) |
| Voir toutes | ✓ | ✓ | ✗ | ✗ |
| Voir assignées | ✓ | ✓ | ✓ | ✗ |
| Voir siennes | ✓ | ✓ | ✓ | ✓ |
| Modifier | ✓ | ✓ | ✗ | ✗ |
| Assigner | ✓ | ✓ | ✗ | ✗ |
| Clôturer | ✓ | ✓ | ✗ | ✗ |
| **Rapports** |
| Upload | ✓ | ✓ | ✓ | ✗ |
| Télécharger tous | ✓ | ✓ | ✗ | ✗ |
| Télécharger siens | ✓ | ✓ | ✓ | ✓ |
| **Cartographie** |
| Voir tout | ✓ | ✓ | ✓ | ✗ |
| Voir son patrimoine | ✓ | ✓ | ✗ | ✓ |
| Import Excel | ✓ | ✓ | ✓ | ✗ |
| **Messages** |
| Voir contexte commande | ✓ | ✓ | ✓ | ✓ |
| Envoyer | ✓ | ✓ | ✓ | ✓ |
| **Administration** |
| Gérer utilisateurs | ✓ | ✗ | ✗ | ✗ |
| Gérer référentiels | ✓ | ✗ | ✗ | ✗ |
| Voir audit log | ✓ | ✗ | ✗ | ✗ |
| Exports globaux | ✓ | ✓ | ✗ | ✗ |
| Paramètres | ✓ | ✗ | ✗ | ✗ |

## 5. Sécurité

### 5.1 Authentification
- Hash mots de passe: `password_hash()` avec PASSWORD_BCRYPT
- Politique: min 8 caractères, 1 maj, 1 min, 1 chiffre
- Tentatives login: max 5 échecs → blocage temporaire 15 min
- Sessions sécurisées: httponly, secure (si HTTPS), SameSite=Strict
- Timeout session: 2h inactivité, 8h max
- Logout: destruction complète session

### 5.2 Autorisations
- Vérification RBAC sur chaque route
- Middleware d'autorisation systématique
- Filtrage données selon rôle (client voit uniquement ses données)

### 5.3 Upload de fichiers
- Whitelist extensions: pdf, xlsx, xls, jpg, jpeg, png, doc, docx
- Validation MIME type réel (finfo)
- Taille max: 20 MB (configurable)
- Renommage avec UUID: `{uuid}_{timestamp}.{ext}`
- Stockage hors document root si possible
- Scan antivirus si ClamAV disponible (optionnel)
- Téléchargement forcé: `Content-Disposition: attachment`

### 5.4 Protection XSS/CSRF
- Échappement systématique: `htmlspecialchars()` sur toute sortie
- Token CSRF sur tous les formulaires
- Headers sécurité:
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`

### 5.5 SQL Injection
- Utilisation exclusive de requêtes préparées PDO
- Pas de concaténation SQL

## 6. Performance

### 6.1 Base de données
- Index sur colonnes fréquemment recherchées
- Requêtes optimisées avec EXPLAIN
- Pagination: 25-50 résultats par page
- Cache de requêtes activé

### 6.2 Application
- PHP OPcache activé
- Sessions fichier (opcache possible si volume élevé)
- Lazy loading images
- Minification CSS/JS (optionnel)
- Compression Gzip

### 6.3 Exports/Imports
- Traitement par batch (1000 lignes)
- Timeout PHP augmenté pour imports lourds
- Progression affichée (AJAX polling)

## 7. Conformité RGPD

### 7.1 Données personnelles
- Consentement explicite (CGU/CGV)
- Minimisation des données collectées
- Durée de conservation configurable
- Droit d'accès: export données utilisateur
- Droit à l'effacement: anonymisation (pas suppression physique pour audit)
- Traçabilité des accès/téléchargements

### 7.2 Audit
- Logging de tous les accès données sensibles
- Conservation logs: 1 an minimum
- Export audit log: CSV/JSON

## 8. Internationalisation

- Interface en français par défaut
- Structure i18n prête:
  - Fichiers langue: `lang/fr.php`, `lang/en.php`
  - Fonction helper: `__('key')`
- Dates: format français dd/mm/yyyy
- Nombres: séparateurs français

## 9. Diagrammes

### 9.1 Cas d'usage principaux

**Client PCH**
- Se connecter
- Déposer une commande
- Consulter statut commandes
- Échanger via messagerie
- Télécharger rapports
- Consulter cartographie patrimoine
- Filtrer diagnostics amiante

**Secrétariat**
- Gérer commandes (CRUD)
- Générer/déposer AR
- Assigner techniciens
- Planifier rendez-vous
- Suivre avancement
- Clôturer commandes
- Envoyer notifications

**Technicien**
- Consulter planning
- Voir interventions assignées
- Échanger sur commandes
- Uploader rapports PDF
- Uploader Excel cartographie
- Mettre à jour statuts

**Administrateur**
- Gérer utilisateurs/rôles
- Gérer référentiels
- Consulter audit log
- Exporter données
- Configurer paramètres
- Tableaux de bord

### 9.2 Séquence : Création commande → Clôture

```
Client                Secrétariat         Système             Technicien
  |                        |                  |                    |
  |--Dépose commande------>|                  |                    |
  |                        |--Crée order----->|                    |
  |                        |                  |--order_created--->|
  |                        |                  |--notification---->|
  |<-------Email acquitté--|                  |                    |
  |                        |--Génère AR------>|                    |
  |                        |                  |--AR stocké-------->|
  |                        |--Assigne-------->|                    |
  |                        |                  |--order_assigned--->|
  |                        |                  |--notification----->|
  |                        |--Planifie RDV--->|                    |
  |<-------Email RDV-------|                  |                    |
  |                        |                  |                    |
  |                        |                  |   <--Débute--------|
  |                        |                  |--in_progress------>|
  |                        |                  |   <--Upload PDF----|
  |                        |                  |   <--Upload Excel--|
  |                        |                  |--Import Excel----->|
  |                        |                  |--Mise à jour carte->|
  |<-------Email rapport---|                  |                    |
  |                        |--Valide--------->|                    |
  |                        |--Clôture-------->|                    |
  |                        |                  |--order_closed----->|
  |<-------Email clôture---|                  |                    |
```

## 10. Exemples de Fichiers

### 10.1 Modèle Excel Import Cartographie

Fichier : `modele_import_cartographie.xlsx`

| Colonne | Type | Obligatoire | Exemple |
|---------|------|-------------|---------|
| reference_site | string | Oui | BAT-001 |
| nom_site | string | Oui | Bâtiment A - Siège social |
| adresse | string | Oui | 15 rue de la République |
| code_postal | string | Oui | 75001 |
| ville | string | Oui | Paris |
| type_diagnostic | string | Oui | DTA |
| date_diagnostic | date | Oui | 15/03/2024 |
| statut | string | Non | ok / anomaly / critical |
| criticite | int | Non | 0-5 |
| numero_rapport | string | Non | DIAG-2024-0123 |
| validite_jusqu_au | date | Non | 15/03/2027 |
| observations | text | Non | Présence amiante niveau 2 |

### 10.2 Exemple JSON Event

```json
{
  "id": 42,
  "order_id": 15,
  "event_type": "report_uploaded",
  "user_id": 8,
  "timestamp": "2024-03-15T14:32:18+01:00",
  "data": {
    "report_id": 23,
    "filename": "Rapport_DTA_Batiment_A.pdf",
    "file_size": 2458624,
    "has_excel": true,
    "excel_filename": "Resultats_DTA.xlsx"
  },
  "ip_address": "192.168.1.45",
  "is_system": false
}
```

### 10.3 Exemple Export CSV Interventions

```csv
"Numéro Commande","Client","Site","Type Diagnostic","Technicien","Date Prévue","Date Réalisée","Statut","Rapport"
"CMD-20240315-0001","PCH Immobilier","15 rue de la République, 75001 Paris","DTA","Jean Dupont","15/03/2024","15/03/2024","completed","Rapport_DTA_Batiment_A.pdf"
"CMD-20240316-0002","PCH Immobilier","28 avenue des Champs, 69001 Lyon","DAPP","Marie Martin","18/03/2024","","scheduled",""
```

## 11. Migrations et Versioning

### 11.1 Système de migration

Fichiers numérotés : `database/migrations/001_initial_schema.sql`, `002_add_excel_imports.sql`, etc.

Table `migrations` :
```sql
CREATE TABLE migrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version INT UNIQUE,
    filename VARCHAR(255),
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Script `scripts/migrate.php` exécute les migrations manquantes.

### 11.2 Numéro de version application

Format : MAJOR.MINOR.PATCH (ex: 1.0.0)

Stocké dans `config/app.php` :
```php
define('APP_VERSION', '1.0.0');
define('APP_BUILD_DATE', '2024-03-15');
```

## 12. Monitoring et Logs

### 12.1 Fichiers logs

- `logs/app.log` : logs applicatifs (info, warning)
- `logs/error.log` : erreurs PHP, exceptions
- `logs/audit.log` : miroir fichier du audit_log DB (sécurité)
- `logs/import.log` : détails imports Excel

### 12.2 Métriques supervisables

- Espace disque uploads/
- Taille base de données
- Nombre commandes ouvertes
- Temps réponse moyen
- Taux d'erreur 500

Supervision possible via :
- Virtualmin : graphiques ressources
- Scripts cron d'alerte
- Logs centralisés (syslog)

## 13. Installation et Configuration

Voir `docs/INSTALL.md` pour procédure détaillée.

**Prérequis** :
- PHP 8.1+ avec extensions : pdo_mysql, mbstring, gd, zip, xml, curl
- MySQL 5.7+ / MariaDB 10.3+
- Apache 2.4+ avec mod_rewrite ou Nginx
- 500 MB espace disque minimum
- 256 MB memory_limit PHP

**Configuration Virtualmin** :
1. Créer domaine virtuel
2. Activer PHP (mode FPM recommandé)
3. Créer base MySQL
4. Uploader fichiers
5. Configurer permissions
6. Lancer script d'installation
7. Configurer cron jobs (notifications, nettoyage)

---

**Auteur** : Système de gestion de diagnostics immobiliers
**Version** : 1.0.0
**Date** : 2024-03-15
