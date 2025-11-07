# 🔑 Mots de Passe par Défaut

## ⚠️ IMPORTANT - Correction

Les mots de passe dans les données de démonstration sont **`password`** et NON `Demo2024!`

## Comptes de Démonstration

Tous les comptes utilisent le même mot de passe : **`password`**

| Rôle | Identifiant | Mot de passe | Email |
|------|-------------|--------------|-------|
| **Administrateur** | `admin` | `password` | admin@suivi-diag.fr |
| **Secrétariat** | `secretariat` | `password` | secretariat@suivi-diag.fr |
| **Technicien** | `tech1` | `password` | tech1@suivi-diag.fr |
| **Client** | `client.pch` | `password` | contact@pch-immobilier.fr |

---

## 🗄️ Configuration de la Base de Données

### Option 1 : Script Automatique (Recommandé)

```bash
# Aller dans le répertoire
cd /home/gestion/public_html

# Exécuter le script
bash setup-database.sh
```

Le script va :
1. ✅ Vérifier la configuration dans `config/database.php`
2. ✅ Créer la base de données si elle n'existe pas
3. ✅ Importer le schéma (20+ tables)
4. ✅ Importer les données de démonstration (utilisateurs, clients, sites, commandes)

### Option 2 : Manuellement

```bash
# 1. Se connecter à MySQL en tant que root
mysql -u root -p

# 2. Créer la base de données
CREATE DATABASE suivi_diag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Créer l'utilisateur
CREATE USER 'suivi_diag_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';

# 4. Donner les permissions
GRANT ALL PRIVILEGES ON suivi_diag.* TO 'suivi_diag_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 5. Importer le schéma
mysql -u suivi_diag_user -p suivi_diag < database/schema.sql

# 6. Importer les données de démonstration
mysql -u suivi_diag_user -p suivi_diag < database/seeds/001_initial_data.sql
```

---

## ✅ Vérification

Après avoir importé la base de données, vérifiez :

```bash
# Vérifier que les utilisateurs sont créés
mysql -u suivi_diag_user -p suivi_diag -e "SELECT username, email FROM users;"
```

Devrait afficher :
```
+-------------+---------------------------+
| username    | email                     |
+-------------+---------------------------+
| admin       | admin@suivi-diag.fr       |
| secretariat | secretariat@suivi-diag.fr |
| tech1       | tech1@suivi-diag.fr       |
| tech2       | tech2@suivi-diag.fr       |
| client.pch  | contact@pch-immobilier.fr |
| ...         | ...                       |
+-------------+---------------------------+
```

---

## 🔐 Première Connexion

1. **Accédez à** : `https://gestion.d-evidences.fr/`

2. **Connectez-vous avec** :
   - Identifiant : `admin`
   - Mot de passe : `password`

3. **⚠️ CHANGEZ IMMÉDIATEMENT le mot de passe** :
   - Allez dans **Mon Profil**
   - Cliquez sur **Changer le mot de passe**
   - Choisissez un mot de passe fort

---

## 🔒 Changer les Mots de Passe

### Via l'interface (pour les utilisateurs)

1. Connectez-vous
2. Allez dans **Mon Profil**
3. Section **Changer le mot de passe**
4. Entrez l'ancien mot de passe : `password`
5. Entrez le nouveau mot de passe
6. Sauvegardez

### Via MySQL (pour l'administrateur)

```sql
-- Se connecter à MySQL
mysql -u suivi_diag_user -p suivi_diag

-- Changer le mot de passe de l'admin
UPDATE users
SET password_hash = '$2y$10$NOUVEAU_HASH_ICI'
WHERE username = 'admin';
```

Pour générer un hash bcrypt :
```php
<?php
echo password_hash('VotreNouveauMotDePasse', PASSWORD_DEFAULT);
?>
```

---

## 📊 Liste Complète des Utilisateurs de Démonstration

| ID | Username | Rôle | Nom | Client associé |
|----|----------|------|-----|----------------|
| 1 | admin | Administrateur | Administrateur Système | - |
| 2 | secretariat | Secrétariat | Sophie Leroux | - |
| 3 | tech1 | Technicien | Marc Dubois | - |
| 4 | tech2 | Technicien | Julie Martin | - |
| 5 | client.pch | Client | Jean Dupont | PCH Immobilier |
| 6 | client.mairie | Client | Marie Lambert | Mairie de Toulouse |
| 7 | client.hlm | Client | Pierre Bernard | Office HLM 31 |
| 8 | client.syndic | Client | Claire Moreau | Syndic ABC |
| 9 | client.cabinet | Client | Thomas Petit | Cabinet d'Architecture |
| 10 | tech3 | Technicien | Léa Rousseau | - |

**Mot de passe pour TOUS** : `password`

---

## 🚨 Sécurité - TRÈS IMPORTANT

### Après installation en production :

1. **Changez TOUS les mots de passe**
   - Via l'interface pour chaque utilisateur
   - Ou via MySQL pour réinitialiser en masse

2. **Supprimez les utilisateurs de test non nécessaires**
   ```sql
   DELETE FROM users WHERE username LIKE 'client.%' AND id > 5;
   DELETE FROM users WHERE username LIKE 'tech%' AND id > 3;
   ```

3. **Créez vos vrais utilisateurs**
   - Via l'interface d'administration
   - Avec des mots de passe forts

4. **Activez HTTPS**
   - Let's Encrypt via Virtualmin
   - Forcez la redirection HTTPS

5. **Passez en mode production**
   - Éditez `config/app.php`
   - `'environment' => 'production'`
   - `'debug' => false`

---

## ❓ Problèmes de Connexion

### "Identifiant ou mot de passe incorrect"

**Causes possibles** :

1. **Base de données non créée/importée**
   ```bash
   # Vérifier
   mysql -u suivi_diag_user -p -e "SHOW DATABASES;"

   # Solution
   bash setup-database.sh
   ```

2. **Mauvais mot de passe**
   - Essayez : `password` (et non `Demo2024!`)
   - Vérifiez les majuscules/minuscules

3. **Configuration base de données incorrecte**
   - Vérifiez `config/database.php`
   - Testez la connexion MySQL

4. **Utilisateur n'existe pas**
   ```bash
   # Vérifier
   mysql -u suivi_diag_user -p suivi_diag -e "SELECT * FROM users WHERE username='admin';"
   ```

### Debug : Vérifier les logs

```bash
# Logs de l'application
tail -f logs/error.log

# Logs Apache
tail -f /var/log/apache2/error.log
```

---

## 📞 Support

Si vous ne pouvez toujours pas vous connecter :

1. Vérifiez que la base de données existe
2. Vérifiez que les tables sont créées (schéma importé)
3. Vérifiez que les utilisateurs sont présents (seeds importés)
4. Vérifiez les logs d'erreur
5. Consultez **TROUBLESHOOTING.md**

---

**Note** : Les mots de passe sont hashés avec bcrypt (`password_hash()` PHP) pour la sécurité.
