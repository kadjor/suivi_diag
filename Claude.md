# Documentation Claude - Plateforme de Suivi de Diagnostics

## 📋 Vue d'ensemble du projet

**Nom**: Plateforme de Suivi de Commandes et Cartographie des Diagnostics
**Type**: Application web PHP MVC
**Version actuelle**: 1.3.0
**Branche de développement**: `claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv`

### Objectif
Plateforme de gestion complète du cycle de vie des commandes de diagnostics immobiliers (DTA, DAPP, RAAT, RAAD, DPE, etc.) avec cartographie interactive du patrimoine client.

## 🏗️ Architecture Technique

### Stack Technologique
- **Backend**: PHP 8.1+ (Architecture MVC custom)
- **Base de données**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript vanilla (ES6+)
- **Bibliothèques principales**:
  - Leaflet.js (cartographie)
  - Chart.js (tableaux de bord)
  - FullCalendar (planning)
  - PhpSpreadsheet (import/export Excel)
  - PHPMailer (envoi emails)

### Structure des dossiers
```
/home/user/suivi_diag/
├── app/
│   ├── Core/              # Classes fondamentales (Router, Controller, Model, Auth, RBAC, etc.)
│   ├── Controllers/       # Contrôleurs de l'application
│   ├── Models/           # Modèles de données
│   ├── Views/            # Templates et vues
│   ├── Helpers/          # Fonctions utilitaires
│   └── Services/         # Services (EmailService, etc.)
├── config/               # Fichiers de configuration
├── database/
│   ├── schema.sql        # Schéma complet de la base de données
│   ├── migrations/       # Migrations SQL
│   └── seeds/           # Données de test
├── public/              # Point d'entrée web et assets
│   ├── index.php        # Bootstrap de l'application
│   ├── assets/          # CSS, JS, images
│   ├── reports/         # Rapports PDF générés
│   └── uploads/         # Fichiers uploadés
├── logs/                # Fichiers de logs
└── scripts/             # Scripts d'administration
```

## 📊 Schéma de Base de Données

### Tables principales

#### Utilisateurs et Authentification
- **roles**: Rôles utilisateurs (admin, secretariat, technicien, client)
- **users**: Utilisateurs de la plateforme
- **clients**: Organisations clientes (PCH, etc.)

#### Patrimoine
- **sites**: Bâtiments/sites du patrimoine client
  - Champs: name, address, city, postal_code, latitude, longitude, reference_pch, building_type, construction_year, surface
  - Lié à: clients

#### Diagnostics
- **diagnostic_types**: Référentiel des types de diagnostics (DTA, DAPP, RAAT, RAAD, DPE, etc.)
  - Champs: code, name, description, color, active
- **diagnostics**: Diagnostics réalisés sur les sites
  - Champs: site_id, diagnostic_type_id, date, status (ok/anomaly/critical), reference_number, valid_until
  - Lié à: sites, orders, interventions, diagnostic_types

#### Commandes et Workflow
- **statuses**: Référentiel des statuts (order, intervention, diagnostic)
- **orders**: Bons de commande (Format: CMD-YYYYMMDD-XXXX)
  - Champs: order_number, client_id, status_id, priority, requested_date, deadline_date, assigned_to
  - Lié à: clients, statuses, users
- **order_events**: Timeline horodatée des événements (IMMUABLE)
  - Événements: created, acknowledged, assigned, scheduled, in_progress, report_uploaded, closed
- **interventions**: Interventions techniques sur les sites
  - Champs: order_id, site_id, technician_id, diagnostic_types (JSON), scheduled_date, status
  - Lié à: orders, sites, users

#### Documents
- **reports**: Rapports PDF
  - Champs: order_id, filename, filepath, version, uploaded_by
- **attachments**: Pièces jointes (commandes, messages)
- **acknowledgments**: Accusés de réception (AR commande, AR rapport, AR clôture)

#### Communication
- **messages**: Messagerie par commande
  - Champs: order_id, user_id, recipient_id, content, requires_response

#### Planification
- **appointments**: Rendez-vous planifiés
  - Champs: order_id, technician_id, site_id, start_datetime, end_datetime, status

#### Import/Export
- **excel_imports**: Historique des imports Excel pour cartographie
  - Champs: filename, status, rows_total, rows_processed, column_mapping (JSON)

#### Audit et Configuration
- **audit_log**: Journal d'audit immuable (INSERT ONLY)
- **settings**: Paramètres de l'application
- **migrations**: Suivi des migrations de schéma

## 🎭 Rôles et Permissions

### Rôles disponibles
1. **admin**: Administrateur (accès complet)
2. **secretariat**: Secrétariat (gestion commandes, planification)
3. **technicien**: Technicien (interventions, rapports)
4. **client**: Client (consultation commandes et rapports)

### Permissions clés
- `create_orders`: Créer des commandes
- `manage_orders`: Gérer les commandes
- `manage_sites`: Gérer le patrimoine
- `upload_reports`: Uploader des rapports
- `view_all_orders`: Voir toutes les commandes

## 🔌 Contrôleurs et Routes

### Contrôleurs principaux
- **AuthController**: Authentification (login, logout)
- **DashboardController**: Tableaux de bord par rôle
- **OrderController**: Gestion des commandes
  - `index()`: Liste des commandes
  - `show($id)`: Détails d'une commande
  - `create()`: Formulaire de création
  - `store()`: Enregistrement
- **SiteController**: Gestion des sites
- **MapController**: Cartographie interactive
- **ReportController**: Gestion des rapports PDF
- **MessageController**: Messagerie
- **CalendarController**: Planning/Calendrier
- **InterventionController**: Gestion des interventions
- **UserController**: Gestion des utilisateurs
- **AdminController**: Administration
- **ExportController**: Exports de données

### Pattern de routing
Le routeur est basé sur une architecture MVC avec des routes définies dans `public/index.php`.

## 📦 Modèles Principaux

### Modèles et méthodes clés

#### Order (app/Models/Order.php)
- `generateOrderNumber()`: Génère numéro commande (CMD-YYYYMMDD-XXXX)
- `getWithDetails($id)`: Récupère commande avec jointures
- `getEvents($orderId)`: Timeline des événements
- `addEvent($orderId, $eventType, $userId, $data)`: Ajoute événement
- `updateStatus($orderId, $statusCode, $userId)`: Change statut

#### Site (app/Models/Site.php)
- Gestion du patrimoine client
- Relations avec diagnostics

#### Diagnostic (app/Models/Diagnostic.php)
- Gestion des diagnostics par site
- Statuts: ok, anomaly, critical

#### User (app/Models/User.php)
- Gestion des utilisateurs
- Authentification et permissions

## 🔄 Workflow des Commandes

1. **Création** (`created`)
   - Secrétariat crée la commande
   - Génération numéro automatique
   - Assignation sites/diagnostics

2. **Accusé de réception** (`acknowledged`)
   - Génération AR commande
   - Envoi email client

3. **Assignation** (`assigned`)
   - Attribution à un technicien
   - Notification email

4. **Planification** (`scheduled`)
   - Création RDV/interventions
   - Envoi notifications

5. **En cours** (`in_progress`)
   - Technicien sur site
   - Réalisation diagnostics

6. **Rapport uploadé** (`report_uploaded`)
   - Upload PDF
   - Versionnage automatique
   - Notification client

7. **Clôture** (`closed`)
   - Génération AR clôture
   - Archivage

## 🎨 Frontend et Assets

### Structure des assets
- `public/assets/css/`: Styles CSS
- `public/assets/js/`: Scripts JavaScript
  - `map.js`: Logique cartographie Leaflet
  - `main.js`: Scripts généraux

### Vues principales
- `dashboard/`: Tableaux de bord par rôle
- `orders/`: Gestion commandes
- `map/`: Cartographie interactive
- `calendar/`: Planning
- `messages/`: Messagerie
- `admin/`: Administration

## 📧 Système de Notifications

### EmailService (app/Services/EmailService.php)
Notifications automatiques pour :
- Création commande
- Accusés de réception
- Attribution technicien
- Upload rapport
- Clôture commande

### Configuration
Fichier: `config/email.example.php`
- Support SMTP (Gmail, Mailgun, SendGrid, etc.)
- Templates HTML personnalisables

## 📝 Fonctionnalités Clés

### 1. Gestion du Patrimoine
- Import Excel des sites
- Cartographie interactive (Leaflet)
- Historique diagnostics par site
- Recherche et filtres

### 2. Système de Commandes
- Création assistée
- Timeline horodatée
- Messagerie contextualisée
- Pièces jointes

### 3. Planification
- Calendrier techniciens
- Attribution automatique
- Gestion conflits

### 4. Rapports et Documents
- Upload PDF multi-version
- Génération AR automatique
- Traçabilité téléchargements

### 5. Audit et Sécurité
- Journal immuable
- Traçabilité actions
- Permissions granulaires (RBAC)

## 🔧 Commandes Utiles

### Base de données
```bash
# Réinitialiser la base de données
./setup-database.sh

# Diagnostiquer les seeds
./diagnose-seeds.sh

# Réparer l'import des seeds
./fix-seeds-import.sh
```

### Scripts PHP
```bash
# Installation
php scripts/install.php
```

## 📚 Fichiers de Documentation

- `README.md`: Guide principal
- `START_HERE.md`: Guide de démarrage rapide
- `SPECIFICATION_TECHNIQUE.md`: Spécifications détaillées
- `GUIDE_PATRIMOINE.md`: Guide gestion patrimoine
- `GUIDE_DEPLOIEMENT.md`: Guide de déploiement
- `PERMISSIONS.md`: Documentation permissions RBAC
- `DIAGRAMMES.md`: Diagrammes architecture
- `CHANGELOG.md`: Historique des versions
- `TROUBLESHOOTING.md`: Guide de dépannage
- `COMPTES_DEMO.md`: Comptes de démonstration

## 🎯 Prochaines Fonctionnalités (TODO)

### Système de Cession
**Objectif**: Gérer la cession de sites entre clients

**Tables à créer**:
- `cessions`: Table principale pour les cessions
  - `id`, `from_client_id`, `to_client_id`, `status`, `effective_date`
  - `created_by`, `validated_by`, `created_at`, `updated_at`
- `cession_sites`: Sites concernés par la cession
  - `cession_id`, `site_id`, `transfer_date`
- `cession_documents`: Documents liés à la cession
  - `cession_id`, `filename`, `filepath`, `document_type`

**Workflow prévu**:
1. Création demande de cession
2. Sélection des sites à transférer
3. Validation multi-niveaux
4. Transfert effectif des sites
5. Génération documents de cession
6. Archivage et audit

**Permissions**:
- `create_cessions`: Créer une cession
- `validate_cessions`: Valider une cession
- `manage_cessions`: Gérer les cessions

## 🔐 Sécurité

### Authentification
- Hachage passwords (password_hash/password_verify)
- Protection brute force (login_attempts, locked_until)
- Reset password sécurisé
- Sessions PHP natives

### Autorisations
- RBAC (Role-Based Access Control)
- Vérification permissions par action
- Isolation des données par client

### Audit
- Traçabilité complète (audit_log)
- IP et User-Agent enregistrés
- Événements immuables

## 🐛 Debugging

### Logs
- Fichier: `logs/error.log`
- Configuration: `config/app.php`

### Mode développement
```php
// config/app.php
'environment' => 'development',  // Affiche les erreurs
'environment' => 'production',   // Cache les erreurs
```

## 💡 Conventions de Code

### Namespaces
- `Core\`: Classes fondamentales
- `Models\`: Modèles de données
- `Controllers\`: Contrôleurs
- `Helpers\`: Fonctions utilitaires
- `Services\`: Services métier

### Nommage
- Classes: PascalCase (`OrderController`)
- Méthodes: camelCase (`getWithDetails()`)
- Tables: snake_case pluriel (`order_events`)
- Colonnes: snake_case (`created_at`)

### Base de données
- Toutes les tables ont `created_at` et `updated_at` (sauf tables immuables)
- Foreign keys avec `ON DELETE CASCADE` ou `ON DELETE SET NULL`
- Index sur les colonnes de recherche/jointure
- JSON pour données structurées flexibles

## 📞 Support

Pour toute question ou problème :
1. Consulter `TROUBLESHOOTING.md`
2. Vérifier les logs (`logs/error.log`)
3. Consulter la documentation technique

---

**Dernière mise à jour**: 2025-11-11
**Version**: 1.3.0
**Branche**: claude/nouveau-ce-011CV2BhZxqYCKeojX7ozuYv
