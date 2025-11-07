# 🏢 Plateforme de Suivi de Commandes et Cartographie des Diagnostics

> **Version 1.0.0** | **PHP 7.4+** | **MySQL 5.7+** | **Compatible Virtualmin**

## 📖 Vue d'ensemble

Plateforme web complète de gestion du cycle de vie des commandes de diagnostics immobiliers (amiante, DPE, électricité, etc.) avec cartographie interactive du patrimoine client, workflow de bout en bout, timeline horodatée, journal d'audit immuable et accès multi-rôles sécurisé.

### 🎯 Fonctionnalités principales

✅ **Gestion complète des commandes** : émission → AR → planification → réalisation → rapport → clôture
✅ **Timeline horodatée immuable** : traçabilité complète de chaque action
✅ **4 profils utilisateurs** : Administrateur, Secrétariat, Technicien, Client
✅ **Cartographie interactive** : visualisation patrimoine + historique diagnostics amiante
✅ **Import Excel** : alimentation automatique cartographie depuis rapports
✅ **Messagerie contextuelle** : discussions par commande
✅ **Rapports PDF** : dépôt, archivage, téléchargement sécurisé
✅ **Planning** : gestion rendez-vous, export ICS
✅ **Journal d'audit** : log immuable de toutes les actions
✅ **RGPD compliant** : traçabilité téléchargements, durées conservation, anonymisation
✅ **Notifications email** : automatiques et paramétrables
✅ **Exports** : CSV/JSON pour commandes, interventions, diagnostics, audit

## 🔧 Technologies

- **Backend** : PHP 8+ (compatible 7.4+), architecture MVC custom
- **Base de données** : MySQL 5.7+ / MariaDB 10.3+
- **Frontend** : HTML5, CSS3, JavaScript vanilla, Leaflet.js (cartographie)
- **Serveur** : Apache 2.4+ ou Nginx
- **Bibliothèques** : PhpSpreadsheet (Excel), FPDF (PDF), PHPMailer (email)

## 🚀 Installation rapide

### Prérequis

```bash
php -v  # PHP 7.4+ requis
php -m | grep -E "pdo|pdo_mysql|mbstring|json|fileinfo"  # Extensions requises
```

### Installation en 3 étapes

#### 1️⃣ Uploader les fichiers

Uploadez tous les fichiers dans votre domaine Virtualmin :
```
/home/username/public_html/suivi-diag/
```

#### 2️⃣ Configurer la base de données

Éditez `config/database.php` :
```php
'database' => 'votre_base',
'username' => 'votre_user',
'password' => 'votre_pass',
```

#### 3️⃣ Lancer l'installation

**Via navigateur** : `https://votredomaine.com/scripts/install.php`

**Ou via SSH** :
```bash
cd /home/username/public_html/suivi-diag
php scripts/install.php
```

✅ **C'est prêt !** Accédez à `https://votredomaine.com`

## 👥 Comptes de démonstration

Mot de passe pour tous : **Demo2024!**

| Rôle | Login | Fonctionnalités |
|------|-------|-----------------|
| **Admin** | `admin` | Tout : users, paramètres, audit, exports globaux |
| **Secrétariat** | `secretariat` | Commandes, assignations, planning, AR, clôtures |
| **Technicien** | `tech1` | Interventions assignées, rapports, Excel, statuts |
| **Client** | `client.pch` | Dépôt commandes, suivi, messagerie, cartographie, téléchargements |

## 📁 Structure du projet

```
suivi_diag/
├── app/
│   ├── Core/           # Framework MVC (Router, Database, Auth, RBAC)
│   ├── Models/         # Modèles métier (User, Order, Site, Diagnostic, etc.)
│   ├── Controllers/    # Contrôleurs (Auth, Order, Map, Report, Admin)
│   ├── Views/          # Templates PHP
│   └── Helpers/        # Validator, FileUpload, Pagination, etc.
├── config/             # Configuration (database, app)
├── database/
│   ├── schema.sql      # Schéma complet
│   └── seeds/          # Données de démonstration
├── public/
│   ├── index.php       # Point d'entrée
│   ├── .htaccess       # Réécriture URLs
│   ├── assets/         # CSS, JS, images
│   └── uploads/        # Fichiers uploadés
├── scripts/
│   ├── install.php     # Installation automatique
│   ├── migrate.php     # Migrations schéma
│   └── backup.php      # Sauvegarde DB
├── logs/               # Logs (app, error, audit)
├── docs/               # Documentation complète
└── tests/              # Tests d'acceptation
```

## 🗄️ Modèle de données

### Entités principales

- **users** : utilisateurs + rôles + permissions RBAC
- **clients** : organisations clientes (PCH, etc.)
- **sites** : bâtiments/patrimoine géolocalisés
- **orders** : bons de commande + workflow
- **order_events** : timeline horodatée (IMMUABLE)
- **interventions** : interventions techniques
- **diagnostics** : diagnostics réalisés (DTA, DAPP, etc.)
- **reports** : rapports PDF + versionnage
- **messages** : messagerie par commande
- **appointments** : rendez-vous planifiés
- **excel_imports** : historique imports cartographie
- **audit_log** : journal d'audit (INSERT ONLY)

### Référentiels

- **roles** : admin, secretariat, technicien, client
- **statuses** : new, acknowledged, assigned, scheduled, in_progress, completed, closed
- **diagnostic_types** : DTA, DAPP, RAAT, RAAD, DPE, CREP, GAZ, ELEC, etc.

## 🔐 Sécurité

✅ **Authentification** : bcrypt, tentatives limitées, verrouillage temporaire
✅ **Autorisations** : RBAC granulaire par ressource et action
✅ **Upload sécurisé** : validation extension + MIME réel, UUID, antivirus (optionnel)
✅ **Protection XSS/CSRF** : échappement systématique, tokens CSRF
✅ **SQL Injection** : requêtes préparées PDO obligatoires
✅ **Headers sécurité** : X-Frame-Options, X-Content-Type-Options, X-XSS-Protection
✅ **HTTPS** : recommandé, configuration Let's Encrypt Virtualmin
✅ **Audit immuable** : toutes actions tracées (user, IP, timestamp, payload)

## 📊 Workflows clés

### Workflow Commande

```
Client/Secrétariat → Crée commande
↓
Secrétariat → Génère AR (obligatoire)
↓
Secrétariat → Assigne technicien
↓
Secrétariat → Planifie rendez-vous
↓
Technicien → Réalise intervention
↓
Technicien → Dépose rapport PDF + Excel
↓
Système → Importe Excel → Met à jour cartographie
↓
Secrétariat → Valide rapport
↓
Secrétariat → Clôture commande
```

Chaque étape crée un `order_event` horodaté et un `audit_log` entry.

### Import Excel Cartographie

```
Technicien → Upload .xlsx
↓
Système → Analyse colonnes + propose mapping
↓
Utilisateur → Valide mapping
↓
Système → Validation données (adresses, dates, types diagnostics)
↓
Système → Géocodage adresses
↓
Système → Création/update sites + diagnostics
↓
Système → Log succès/erreurs
↓
Cartographie → Mise à jour temps réel
```

## 🌍 Cartographie

- **Leaflet.js** : cartographie interactive
- **Géocodage** : Nominatim (gratuit) ou Google Maps/Mapbox (payant)
- **Filtres** : période, type diagnostic, statut, criticité
- **Historique amiante** : visualisation temporelle diagnostics DTA/DAPP/RAAT/RAAD
- **Fiches site** : clic sur marqueur → détails + diagnostics + rapports + commandes
- **Export GeoJSON** : données cartographiques exportables

## 📧 Notifications

Notifications email automatiques (paramétrables) :
- Création commande → secrétariat
- AR généré → client
- Assignation → technicien
- Rendez-vous planifié → technicien + client
- Rapport déposé → secrétariat + client
- Commande clôturée → client

Configuration SMTP : `config/app.php` section `email`

## 🔄 Maintenance

### Sauvegarde base de données

```bash
php scripts/backup.php
# Ou manuellement :
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
```

### Nettoyage fichiers temporaires

```bash
php scripts/cleanup.php
```

### Logs

- `logs/app.log` : logs applicatifs (info, warning)
- `logs/error.log` : erreurs PHP
- `logs/audit.log` : miroir fichier audit_log DB
- `logs/import.log` : imports Excel

### Rotation logs

Configurez logrotate ou cron :
```bash
0 0 * * 0 find /path/to/logs -name "*.log" -mtime +30 -delete
```

## 📈 Performance

- **Pagination** : 25-50 résultats par page
- **Index DB** : optimisés sur colonnes recherchées
- **PHP OPcache** : activé recommandé
- **Gzip** : compression activée via `.htaccess`
- **Cache navigateur** : assets statiques cachés 1 mois

## 🌐 Internationalisation

- Interface en **français** par défaut
- Structure i18n prête : `lang/fr.php`, `lang/en.php`
- Fonction helper `__('key')` pour traductions
- Formats dates/nombres français

## 📚 Documentation complète

- **SPECIFICATION_TECHNIQUE.md** : architecture, modèle données, diagrammes
- **DIAGRAMMES.md** : ERD, cas d'usage, séquences (format Mermaid)
- **INSTALL.md** : guide installation détaillé Virtualmin
- **USER_GUIDE.md** : guide utilisateur par rôle (à créer)
- **API.md** : documentation endpoints (à créer si API REST)

## 🧪 Tests

### Tests d'acceptation

Scénarios par rôle dans `tests/acceptance/` :
- `test_admin.md` : gestion users, paramètres, audit
- `test_secretariat.md` : création commande, AR, assignation, planning
- `test_technicien.md` : interventions, rapports, Excel
- `test_client.md` : dépôt commande, suivi, cartographie, téléchargements

### Tests manuels

```bash
# Vérifier connexion DB
php -r "require 'config/database.php'; new PDO(...);"

# Tester envoi email
php scripts/test_email.php

# Tester géocodage
php scripts/test_geocoding.php "15 rue de Rivoli, Paris"
```

## 🆘 Dépannage

### Erreur 500

```bash
tail -f logs/error.log
# Vérifier permissions :
chmod 770 logs public/uploads -R
```

### Base de données

```bash
# Tester connexion :
mysql -h localhost -u user -p database

# Vérifier tables :
mysql> SHOW TABLES;
mysql> SELECT COUNT(*) FROM orders;
```

### Uploads échouent

```bash
# Vérifier config PHP :
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"

# Augmenter si nécessaire dans .htaccess ou php.ini
```

### Cartographie vide

- Vérifier que sites ont `latitude` et `longitude` non NULL
- Tester géocodage manuellement
- Console JavaScript (F12) pour erreurs

## 🛣️ Roadmap / Améliorations futures

- [ ] API REST complète avec authentification JWT
- [ ] Application mobile (React Native / Flutter)
- [ ] Signature électronique rapports
- [ ] OCR automatique sur rapports PDF scannés
- [ ] Dashboard analytics avancé (charts, KPI)
- [ ] Export Word/Excel personnalisés
- [ ] Intégration calendriers externes (Google Calendar, Outlook)
- [ ] Notifications push navigateur
- [ ] Module de facturation intégré
- [ ] Multi-langue complète (EN, ES, DE)

## 📄 Licence

**Propriétaire** - Tous droits réservés.

Usage autorisé dans le cadre du projet pour lequel cette plateforme a été développée.

## 👨‍💻 Auteur & Support

Développé pour la gestion des diagnostics immobiliers PCH.

**Support** :
- 📖 Documentation : `/docs`
- 🐛 Issues : à définir
- 📧 Contact : à définir

## 🙏 Remerciements

- **Leaflet.js** : cartographie open-source
- **PhpSpreadsheet** : manipulation Excel en PHP
- **FPDF** : génération PDF
- **Font Awesome** : icônes
- **Nominatim/OpenStreetMap** : géocodage gratuit

---

**Version** : 1.0.0
**Date** : 2024-03-15
**PHP** : 7.4+ / 8.0+
**Base de données** : MySQL 5.7+ / MariaDB 10.3+

🚀 **Prêt pour production Virtualmin !**
