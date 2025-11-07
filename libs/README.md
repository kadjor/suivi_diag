# Bibliothèques PHP Tierces

Ce dossier doit contenir les bibliothèques PHP nécessaires au fonctionnement de l'application.

## Installation avec Composer (Recommandé)

Si vous avez Composer installé, exécutez simplement :

```bash
composer require phpmailer/phpmailer
composer require phpoffice/phpspreadsheet
composer require setasign/fpdf
```

Les bibliothèques seront installées dans `vendor/` et seront automatiquement chargées.

## Installation Manuelle

Si vous n'avez pas Composer, téléchargez manuellement les bibliothèques :

### 1. PHPMailer (Envoi d'emails)

Télécharger depuis : https://github.com/PHPMailer/PHPMailer/releases

Extraire dans : `libs/PHPMailer/`

### 2. PhpSpreadsheet (Import/Export Excel)

Télécharger depuis : https://github.com/PHPOffice/PhpSpreadsheet/releases

Extraire dans : `libs/PhpSpreadsheet/`

### 3. FPDF (Génération PDF)

Télécharger depuis : http://www.fpdf.org/

Extraire dans : `libs/FPDF/`

## Structure attendue

```
libs/
├── PHPMailer/
│   └── src/
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
├── PhpSpreadsheet/
│   └── src/
│       └── PhpOffice/
│           └── PhpSpreadsheet/
├── FPDF/
│   └── fpdf.php
└── README.md (ce fichier)
```

## Autoload

L'application charge automatiquement ces bibliothèques via le fichier `public/index.php`.

## Versions recommandées

- PHPMailer : 6.8+
- PhpSpreadsheet : 1.29+
- FPDF : 1.85+

## Alternative : Utiliser Composer

Pour générer un `composer.json` et installer via Composer :

```bash
cd /path/to/suivi_diag
composer init
composer require phpmailer/phpmailer phpoffice/phpspreadsheet setasign/fpdf
```

Puis dans `public/index.php`, ajouter :

```php
require_once __DIR__ . '/../vendor/autoload.php';
```
