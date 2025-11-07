<?php

namespace Helpers;

/**
 * Validateur de données
 */
class Validator
{
    private $data;
    private $rules;
    private $errors = [];
    private $validated = [];

    /**
     * Constructeur
     */
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    /**
     * Valide les données
     */
    private function validate()
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }

            // Si pas d'erreur, ajouter aux données validées
            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }
    }

    /**
     * Applique une règle de validation
     */
    private function applyRule($field, $value, $rule)
    {
        // Parser la règle (ex: "min:3" ou "required")
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $ruleParam = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "Le champ {$field} est obligatoire.");
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Le champ {$field} doit être une adresse email valide.");
                }
                break;

            case 'min':
                if ($value && mb_strlen($value) < $ruleParam) {
                    $this->addError($field, "Le champ {$field} doit contenir au moins {$ruleParam} caractères.");
                }
                break;

            case 'max':
                if ($value && mb_strlen($value) > $ruleParam) {
                    $this->addError($field, "Le champ {$field} ne peut pas dépasser {$ruleParam} caractères.");
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, "Le champ {$field} doit être numérique.");
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "Le champ {$field} doit être un entier.");
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->addError($field, "Le champ {$field} doit être une date valide.");
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleParam);
                if ($value && !in_array($value, $allowedValues)) {
                    $this->addError($field, "Le champ {$field} doit être l'une des valeurs suivantes : " . implode(', ', $allowedValues));
                }
                break;

            case 'unique':
                // Format: unique:table,column
                $params = explode(',', $ruleParam);
                $table = $params[0];
                $column = $params[1] ?? $field;
                $exceptId = $params[2] ?? null;

                if ($value && $this->existsInDatabase($table, $column, $value, $exceptId)) {
                    $this->addError($field, "Cette valeur pour {$field} existe déjà.");
                }
                break;

            case 'exists':
                // Format: exists:table,column
                $params = explode(',', $ruleParam);
                $table = $params[0];
                $column = $params[1] ?? $field;

                if ($value && !$this->existsInDatabase($table, $column, $value)) {
                    $this->addError($field, "La valeur {$value} n'existe pas.");
                }
                break;

            case 'confirmed':
                // Le champ doit avoir un champ de confirmation (ex: password et password_confirmation)
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, "La confirmation ne correspond pas.");
                }
                break;

            case 'alpha':
                if ($value && !preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, "Le champ {$field} ne doit contenir que des lettres.");
                }
                break;

            case 'alpha_num':
                if ($value && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $this->addError($field, "Le champ {$field} ne doit contenir que des lettres et chiffres.");
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "Le champ {$field} doit être une URL valide.");
                }
                break;

            case 'file':
                // Validation de fichier uploadé
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                        $this->addError($field, "Erreur lors de l'upload du fichier {$field}.");
                    }
                }
                break;

            case 'mimes':
                // Validation des types MIME
                $allowedMimes = explode(',', $ruleParam);
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $_FILES[$field]['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mimeType, $allowedMimes)) {
                        $this->addError($field, "Le type de fichier {$field} n'est pas autorisé.");
                    }
                }
                break;

            case 'max_size':
                // Taille max en Ko
                $maxSize = $ruleParam * 1024; // Convertir en octets
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES[$field]['size'] > $maxSize) {
                        $this->addError($field, "Le fichier {$field} est trop volumineux (max: {$ruleParam} Ko).");
                    }
                }
                break;

            case 'regex':
                if ($value && !preg_match($ruleParam, $value)) {
                    $this->addError($field, "Le format du champ {$field} est invalide.");
                }
                break;

            case 'strong_password':
                if ($value) {
                    $errors = [];
                    if (strlen($value) < 8) {
                        $errors[] = "au moins 8 caractères";
                    }
                    if (!preg_match('/[A-Z]/', $value)) {
                        $errors[] = "une majuscule";
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $errors[] = "une minuscule";
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $errors[] = "un chiffre";
                    }

                    if (!empty($errors)) {
                        $this->addError($field, "Le mot de passe doit contenir " . implode(', ', $errors) . ".");
                    }
                }
                break;
        }
    }

    /**
     * Vérifie si une valeur existe dans la base de données
     */
    private function existsInDatabase($table, $column, $value, $exceptId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        if ($exceptId) {
            $query .= " AND id != ?";
            $params[] = $exceptId;
        }

        $result = \Core\Database::selectOne($query, $params);
        return $result['count'] > 0;
    }

    /**
     * Ajoute une erreur
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Vérifie si la validation a échoué
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Vérifie si la validation a réussi
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Retourne les erreurs
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Retourne la première erreur pour un champ
     */
    public function firstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Retourne toutes les erreurs en une seule chaîne
     */
    public function allErrors()
    {
        $allErrors = [];
        foreach ($this->errors as $field => $messages) {
            $allErrors = array_merge($allErrors, $messages);
        }
        return $allErrors;
    }

    /**
     * Retourne les données validées
     */
    public function validated()
    {
        return $this->validated;
    }
}
