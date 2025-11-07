<?php

namespace Core;

use PDO;

/**
 * Modèle de base avec méthodes CRUD génériques
 */
abstract class Model
{
    /**
     * Nom de la table (à définir dans les classes enfants)
     */
    protected $table;

    /**
     * Clé primaire
     */
    protected $primaryKey = 'id';

    /**
     * Timestamps automatiques
     */
    protected $timestamps = true;

    /**
     * Connexion à la base de données
     */
    protected $db;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Trouve un enregistrement par ID
     */
    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return Database::selectOne($query, [$id]);
    }

    /**
     * Récupère tous les enregistrements
     */
    public function all($orderBy = null)
    {
        $query = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }
        return Database::select($query);
    }

    /**
     * Récupère des enregistrements avec conditions
     */
    public function where($conditions, $orderBy = null, $limit = null)
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Support pour les opérateurs: ['>', 10]
                $whereClauses[] = "{$column} {$value[0]} ?";
                $params[] = $value[1];
            } else {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
        }

        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $query .= " LIMIT {$limit}";
        }

        return Database::select($query, $params);
    }

    /**
     * Récupère un seul enregistrement avec conditions
     */
    public function whereOne($conditions)
    {
        $results = $this->where($conditions, null, 1);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Compte les enregistrements
     */
    public function count($conditions = [])
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $result = Database::selectOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Crée un nouvel enregistrement
     */
    public function create($data)
    {
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        return Database::insert($query, array_values($data));
    }

    /**
     * Met à jour un enregistrement
     */
    public function update($id, $data)
    {
        if ($this->timestamps && !isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setClauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $query = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $setClauses),
            $this->primaryKey
        );

        return Database::execute($query, $params);
    }

    /**
     * Supprime un enregistrement
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::execute($query, [$id]);
    }

    /**
     * Exécute une requête SQL personnalisée
     */
    public function query($sql, $params = [])
    {
        return Database::select($sql, $params);
    }

    /**
     * Exécute une requête et retourne un seul résultat
     */
    public function queryOne($sql, $params = [])
    {
        return Database::selectOne($sql, $params);
    }

    /**
     * Pagine les résultats
     */
    public function paginate($page = 1, $perPage = null, $conditions = [], $orderBy = null)
    {
        $perPage = $perPage ?? config('pagination.per_page', 25);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Compte total
        $total = $this->count($conditions);

        // Requête avec pagination
        $whereClauses = [];
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
        }

        $query = "SELECT * FROM {$this->table}";
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }
        $query .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = Database::select($query, $params);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Commence une transaction
     */
    public function beginTransaction()
    {
        return Database::beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit()
    {
        return Database::commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback()
    {
        return Database::rollback();
    }
}
