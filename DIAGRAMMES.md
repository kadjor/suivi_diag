# Diagrammes - Plateforme de Suivi de Commandes

## 1. Diagramme Entité-Relation (ERD)

```mermaid
erDiagram
    USERS ||--o{ ORDERS : creates
    USERS ||--o{ MESSAGES : sends
    USERS ||--o{ AUDIT_LOG : generates
    USERS }o--|| ROLES : has
    USERS }o--o| CLIENTS : belongs_to

    CLIENTS ||--o{ SITES : owns
    CLIENTS ||--o{ ORDERS : requests

    SITES ||--o{ DIAGNOSTICS : has
    SITES ||--o{ INTERVENTIONS : location

    ORDERS ||--o{ ORDER_EVENTS : timeline
    ORDERS ||--o{ INTERVENTIONS : contains
    ORDERS ||--o{ MESSAGES : discussion
    ORDERS ||--o{ REPORTS : produces
    ORDERS ||--o{ ATTACHMENTS : has
    ORDERS ||--o{ APPOINTMENTS : schedules
    ORDERS ||--o{ ACKNOWLEDGMENTS : generates
    ORDERS }o--|| STATUSES : current_status

    INTERVENTIONS }o--|| USERS : assigned_to
    INTERVENTIONS ||--o{ DIAGNOSTICS : performs
    INTERVENTIONS ||--o{ REPORTS : generates

    REPORTS ||--o| EXCEL_IMPORTS : triggers
    EXCEL_IMPORTS ||--o{ DIAGNOSTICS : creates

    DIAGNOSTICS }o--|| DIAGNOSTIC_TYPES : type

    USERS {
        int id PK
        string username
        string password_hash
        string email
        int role_id FK
        int client_id FK
        boolean active
    }

    ROLES {
        int id PK
        string name
        json permissions
    }

    CLIENTS {
        int id PK
        string organization_name
        string email
        string phone
    }

    SITES {
        int id PK
        int client_id FK
        string name
        string address
        decimal latitude
        decimal longitude
        string reference_pch
    }

    ORDERS {
        int id PK
        string order_number
        int client_id FK
        int status_id FK
        int created_by FK
        int assigned_to FK
        datetime created_at
    }

    ORDER_EVENTS {
        int id PK
        int order_id FK
        string event_type
        int user_id FK
        datetime timestamp
        json data
    }

    INTERVENTIONS {
        int id PK
        int order_id FK
        int site_id FK
        int technician_id FK
        date scheduled_date
        string status
    }

    DIAGNOSTICS {
        int id PK
        int site_id FK
        int diagnostic_type_id FK
        date date
        string status
        int criticality
        int report_id FK
    }

    REPORTS {
        int id PK
        int order_id FK
        string filename
        int uploaded_by FK
        datetime uploaded_at
    }

    EXCEL_IMPORTS {
        int id PK
        int report_id FK
        string filename
        int imported_by FK
        string status
        json log_json
    }

    AUDIT_LOG {
        int id PK
        int user_id FK
        string action
        string entity_type
        int entity_id
        datetime timestamp
        json payload
    }
```

## 2. Diagramme de Cas d'Usage

```mermaid
graph TB
    subgraph "Acteurs"
        CLIENT[Client PCH]
        SECRET[Secrétariat]
        TECH[Technicien]
        ADMIN[Administrateur]
    end

    subgraph "Cas d'Usage - Gestion Commandes"
        UC1[Déposer une commande]
        UC2[Créer un bon de commande]
        UC3[Générer/Déposer AR]
        UC4[Assigner technicien]
        UC5[Planifier rendez-vous]
        UC6[Suivre avancement]
        UC7[Clôturer commande]
    end

    subgraph "Cas d'Usage - Interventions"
        UC8[Consulter planning]
        UC9[Voir interventions assignées]
        UC10[Mettre à jour statut]
        UC11[Déposer rapport PDF]
        UC12[Uploader Excel cartographie]
    end

    subgraph "Cas d'Usage - Cartographie"
        UC13[Consulter carte patrimoine]
        UC14[Filtrer diagnostics]
        UC15[Voir historique amiante]
        UC16[Consulter fiche site]
    end

    subgraph "Cas d'Usage - Communication"
        UC17[Échanger via messagerie]
        UC18[Télécharger rapports]
        UC19[Consulter timeline]
    end

    subgraph "Cas d'Usage - Administration"
        UC20[Gérer utilisateurs]
        UC21[Gérer référentiels]
        UC22[Consulter audit log]
        UC23[Exporter données]
        UC24[Configurer paramètres]
    end

    CLIENT --> UC1
    CLIENT --> UC6
    CLIENT --> UC13
    CLIENT --> UC14
    CLIENT --> UC15
    CLIENT --> UC16
    CLIENT --> UC17
    CLIENT --> UC18
    CLIENT --> UC19

    SECRET --> UC2
    SECRET --> UC3
    SECRET --> UC4
    SECRET --> UC5
    SECRET --> UC6
    SECRET --> UC7
    SECRET --> UC17
    SECRET --> UC19
    SECRET --> UC23

    TECH --> UC8
    TECH --> UC9
    TECH --> UC10
    TECH --> UC11
    TECH --> UC12
    TECH --> UC17
    TECH --> UC19

    ADMIN --> UC20
    ADMIN --> UC21
    ADMIN --> UC22
    ADMIN --> UC23
    ADMIN --> UC24
```

## 3. Diagramme de Séquence - Workflow Complet Commande

```mermaid
sequenceDiagram
    participant C as Client PCH
    participant S as Secrétariat
    participant SYS as Système
    participant T as Technicien
    participant DB as Base de données
    participant EMAIL as Service Email

    C->>+SYS: Dépose commande + pièces jointes
    SYS->>DB: INSERT order (status=new)
    SYS->>DB: INSERT order_event (created)
    SYS->>DB: INSERT audit_log
    SYS->>EMAIL: Notification secrétariat
    SYS-->>-C: Confirmation + n° commande

    S->>+SYS: Génère AR commande
    SYS->>DB: INSERT acknowledgment
    SYS->>DB: INSERT order_event (acknowledged)
    SYS->>DB: UPDATE order (status=acknowledged)
    SYS->>EMAIL: Envoi AR au client
    SYS-->>-S: AR généré et envoyé

    S->>+SYS: Assigne technicien
    SYS->>DB: UPDATE order (assigned_to)
    SYS->>DB: INSERT order_event (assigned)
    SYS->>DB: UPDATE order (status=assigned)
    SYS->>EMAIL: Notification technicien
    SYS-->>-S: Technicien assigné

    S->>+SYS: Planifie rendez-vous
    SYS->>DB: INSERT appointment
    SYS->>DB: INSERT order_event (scheduled)
    SYS->>DB: UPDATE order (status=scheduled)
    SYS->>EMAIL: Notification client + technicien
    SYS->>EMAIL: Export ICS
    SYS-->>-S: RDV planifié

    T->>+SYS: Débute intervention
    SYS->>DB: UPDATE intervention (status=in_progress)
    SYS->>DB: INSERT order_event (in_progress)
    SYS->>DB: UPDATE order (status=in_progress)
    SYS-->>-T: Intervention démarrée

    T->>+SYS: Upload rapport PDF + Excel
    SYS->>SYS: Validation fichiers
    SYS->>DB: INSERT report
    SYS->>DB: INSERT order_event (report_uploaded)
    SYS->>DB: UPDATE order (status=report_pending)
    SYS->>EMAIL: Notification secrétariat

    SYS->>+SYS: Lance import Excel
    SYS->>DB: INSERT excel_import (status=processing)
    loop Pour chaque ligne
        SYS->>SYS: Géocode adresse
        SYS->>DB: INSERT/UPDATE site
        SYS->>DB: INSERT diagnostic
    end
    SYS->>DB: UPDATE excel_import (status=completed)
    SYS-->>-T: Import terminé + rapport

    S->>+SYS: Valide rapport
    SYS->>DB: UPDATE order (status=completed)
    SYS->>DB: INSERT order_event (validated)
    SYS->>EMAIL: Notification client
    SYS-->>-S: Rapport validé

    S->>+SYS: Clôture commande
    SYS->>DB: UPDATE order (status=closed, closed_at)
    SYS->>DB: INSERT order_event (closed)
    SYS->>DB: INSERT audit_log
    SYS->>EMAIL: Notification client + AR clôture
    SYS-->>-S: Commande clôturée

    C->>+SYS: Consulte timeline + télécharge rapport
    SYS->>DB: SELECT order_events
    SYS->>DB: INSERT audit_log (download)
    SYS-->>-C: Timeline complète + fichier PDF
```

## 4. Diagramme de Séquence - Import Excel Cartographie

```mermaid
sequenceDiagram
    participant T as Technicien
    participant UI as Interface
    participant CTL as Controller
    participant PARSER as ExcelParser
    participant GEO as Geocoder
    participant DB as Base de données

    T->>+UI: Upload fichier .xlsx
    UI->>+CTL: POST /reports/import-excel
    CTL->>CTL: Validation fichier
    CTL->>DB: INSERT excel_import (status=pending)
    CTL->>+PARSER: Parse Excel
    PARSER->>PARSER: Lecture entêtes
    PARSER-->>-CTL: Colonnes détectées
    CTL-->>-UI: Affiche preview + mapping
    UI-->>-T: Demande validation mapping

    T->>+UI: Valide mapping + lance import
    UI->>+CTL: POST /reports/confirm-import
    CTL->>DB: UPDATE excel_import (status=processing)
    CTL->>+PARSER: Parse avec mapping

    loop Pour chaque ligne
        PARSER->>PARSER: Validation données
        alt Adresse complète
            PARSER->>+GEO: Géocode adresse
            GEO-->>-PARSER: lat/lng ou erreur
        else Adresse incomplète
            PARSER->>PARSER: Log warning
        end

        alt Données valides
            PARSER->>DB: UPSERT site
            PARSER->>DB: INSERT diagnostic
            PARSER->>PARSER: Log success
        else Données invalides
            PARSER->>PARSER: Log error
        end
    end

    PARSER-->>-CTL: Rapport import
    CTL->>DB: UPDATE excel_import (status=completed, log_json)
    CTL-->>-UI: Résumé : X lignes, Y succès, Z erreurs
    UI-->>-T: Affiche rapport détaillé + log téléchargeable

    T->>+UI: Consulte carte
    UI->>+CTL: GET /map
    CTL->>DB: SELECT sites + diagnostics (avec nouveaux)
    CTL-->>-UI: GeoJSON + métadonnées
    UI->>UI: Affiche marqueurs sur carte
    UI-->>-T: Carte mise à jour avec nouveaux diagnostics
```

## 5. Diagramme d'Architecture

```mermaid
graph TB
    subgraph "Navigateur Client"
        BROWSER[Interface Web HTML/CSS/JS]
        MAP[Leaflet.js Cartographie]
        CAL[FullCalendar Planning]
    end

    subgraph "Serveur Web Apache/Nginx"
        HTACCESS[.htaccess Réécriture URLs]
        INDEX[index.php Point d'entrée]
    end

    subgraph "Application PHP MVC"
        ROUTER[Router]
        AUTH[Auth Middleware]
        RBAC[RBAC Middleware]

        subgraph "Controllers"
            CTLAUTH[AuthController]
            CTLORDER[OrderController]
            CTLMAP[MapController]
            CTLREPORT[ReportController]
            CTLADMIN[AdminController]
        end

        subgraph "Models"
            MUSER[User]
            MORDER[Order]
            MSITE[Site]
            MDIAG[Diagnostic]
            MAUDIT[AuditLog]
        end

        subgraph "Helpers"
            HVALID[Validator]
            HUPLOAD[FileUpload]
            HEXCEL[ExcelParser]
            HPDF[PdfGenerator]
            HGEO[Geocoder]
        end

        VIEW[View Engine]
    end

    subgraph "Persistance"
        DB[(MySQL/MariaDB)]
        FILES[Système de fichiers<br/>Uploads/Reports]
        LOGS[Fichiers logs]
    end

    subgraph "Services Externes"
        SMTP[Serveur SMTP]
        GEOAPI[API Géocodage]
    end

    BROWSER --> HTACCESS
    HTACCESS --> INDEX
    INDEX --> ROUTER
    ROUTER --> AUTH
    AUTH --> RBAC
    RBAC --> CTLAUTH
    RBAC --> CTLORDER
    RBAC --> CTLMAP
    RBAC --> CTLREPORT
    RBAC --> CTLADMIN

    CTLORDER --> MORDER
    CTLORDER --> MUSER
    CTLMAP --> MSITE
    CTLMAP --> MDIAG
    CTLADMIN --> MAUDIT

    CTLORDER --> HVALID
    CTLREPORT --> HUPLOAD
    CTLREPORT --> HEXCEL
    CTLREPORT --> HPDF
    CTLMAP --> HGEO

    MUSER --> DB
    MORDER --> DB
    MSITE --> DB
    MDIAG --> DB
    MAUDIT --> DB

    HUPLOAD --> FILES
    HEXCEL --> FILES
    HPDF --> FILES

    MAUDIT --> LOGS

    VIEW --> BROWSER

    CTLORDER -.Email.-> SMTP
    HGEO -.Géocodage.-> GEOAPI
```

## 6. Diagramme de Flux - Gestion des Permissions

```mermaid
flowchart TD
    START([Requête HTTP]) --> AUTH{Utilisateur<br/>authentifié ?}
    AUTH -->|Non| LOGIN[Redirection /login]
    AUTH -->|Oui| ROUTE[Analyse route demandée]

    ROUTE --> RBAC{Permission<br/>requise ?}
    RBAC -->|Non| PUBLIC[Route publique]
    RBAC -->|Oui| CHECKPERM[Vérification permissions rôle]

    CHECKPERM --> HASPERM{Permission<br/>accordée ?}
    HASPERM -->|Non| DENY403[HTTP 403 Forbidden]
    HASPERM -->|Oui| CHECKSCOPE{Scope<br/>restreint ?}

    CHECKSCOPE -->|Non| ALLOW[Exécution contrôleur]
    CHECKSCOPE -->|Oui| FILTERDATA[Filtrage données selon client_id]

    FILTERDATA --> ALLOW
    PUBLIC --> ALLOW

    ALLOW --> AUDIT[Log audit_log]
    AUDIT --> RESPONSE([Réponse HTTP])

    LOGIN --> END1([Fin])
    DENY403 --> END2([Fin])
    RESPONSE --> END3([Fin])
```

## 7. Diagramme États-Transitions - Commande

```mermaid
stateDiagram-v2
    [*] --> new : Client dépose commande

    new --> acknowledged : Secrétariat génère AR
    acknowledged --> assigned : Secrétariat assigne technicien
    assigned --> scheduled : Planification rendez-vous
    scheduled --> in_progress : Technicien débute intervention
    in_progress --> report_pending : Technicien upload rapport
    report_pending --> completed : Secrétariat valide rapport
    completed --> closed : Secrétariat clôture

    new --> cancelled : Annulation
    acknowledged --> cancelled : Annulation
    assigned --> cancelled : Annulation
    scheduled --> cancelled : Annulation

    closed --> [*]
    cancelled --> [*]

    note right of new
        Commande créée
        En attente d'AR
    end note

    note right of in_progress
        Intervention en cours
        Communication active
    end note

    note right of report_pending
        Rapport déposé
        En attente validation
        Import Excel possible
    end note

    note right of closed
        Commande terminée
        Archivage
        Immuable
    end note
```

## 8. Diagramme de Déploiement Virtualmin

```mermaid
graph TB
    subgraph "Serveur Virtualmin"
        subgraph "Domaine virtuel : suivi-diagnostics.example.com"
            WEBROOT["/home/username/public_html"]
            PHPFPM["PHP-FPM 8.1<br/>memory_limit=256M<br/>max_execution_time=300"]
            MYSQL["MySQL 8.0<br/>Base: suivi_diag"]
            CRON["Cron Jobs<br/>- Notifications<br/>- Nettoyage<br/>- Backups"]
        end

        subgraph "Fichiers Application"
            APP["app/"]
            CONFIG["config/"]
            PUBLIC["public/"]
            UPLOADS["uploads/<br/>(chmod 770)"]
            LOGS["logs/<br/>(chmod 770)"]
        end

        subgraph "Services"
            APACHE["Apache 2.4<br/>mod_rewrite"]
            POSTFIX["Postfix SMTP"]
        end
    end

    INTERNET((Internet)) --> APACHE
    APACHE --> WEBROOT
    WEBROOT --> PUBLIC
    PUBLIC --> PHPFPM
    PHPFPM --> APP
    PHPFPM --> CONFIG
    APP --> MYSQL
    APP --> UPLOADS
    APP --> LOGS
    APP --> POSTFIX
    CRON --> PHPFPM
```

## 9. Diagramme de Classes - Modèle Simplifié

```mermaid
classDiagram
    class Model {
        #PDO db
        #string table
        +find(id) object
        +all() array
        +where(conditions) array
        +create(data) int
        +update(id, data) bool
        +delete(id) bool
    }

    class User {
        +int id
        +string username
        +string email
        +int role_id
        +int client_id
        +authenticate(username, password) bool
        +hasPermission(action, resource) bool
        +getRole() Role
        +getClient() Client
    }

    class Order {
        +int id
        +string order_number
        +int client_id
        +int status_id
        +getEvents() array
        +addEvent(type, data) void
        +assignTechnician(user_id) void
        +updateStatus(status_id) void
        +getTimeline() array
        +canBeModifiedBy(user) bool
    }

    class Site {
        +int id
        +int client_id
        +string name
        +float latitude
        +float longitude
        +getDiagnostics(filters) array
        +getLastDiagnostic(type) Diagnostic
        +geocode() bool
    }

    class Diagnostic {
        +int id
        +int site_id
        +int diagnostic_type_id
        +date date
        +string status
        +int criticality
        +isExpired() bool
        +getSite() Site
    }

    class Report {
        +int id
        +int order_id
        +string filename
        +getPath() string
        +download() void
        +logDownload(user_id) void
    }

    class ExcelImport {
        +int id
        +int report_id
        +string status
        +json log_json
        +process(mapping) array
        +validate() array
        +import() bool
    }

    class AuditLog {
        +int id
        +int user_id
        +string action
        +datetime timestamp
        +logAction(user, action, entity) void
        +export(filters) string
    }

    Model <|-- User
    Model <|-- Order
    Model <|-- Site
    Model <|-- Diagnostic
    Model <|-- Report
    Model <|-- ExcelImport
    Model <|-- AuditLog

    User "1" -- "*" Order : creates
    User "1" -- "*" AuditLog : generates
    Order "1" -- "*" Report : has
    Order "1" -- "*" Site : references
    Site "1" -- "*" Diagnostic : contains
    Report "1" -- "0..1" ExcelImport : triggers
    ExcelImport "1" -- "*" Diagnostic : creates
```

---

Ces diagrammes peuvent être visualisés avec :
- Mermaid Live Editor : https://mermaid.live
- Extensions VSCode : Markdown Preview Mermaid Support
- Intégration GitLab/GitHub (rendu natif)
