<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Gestionnaire de connexion à la base de données
 */
class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Initialise la connexion à la base de données
     */
    public static function init($config)
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
    }

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct($config)
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? []
            );

            log_message('Database connection established', 'info');
        } catch (PDOException $e) {
            log_message('Database connection failed: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Retourne l'instance de connexion PDO
     */
    public static function getConnection()
    {
        if (self::$instance === null) {
            throw new \Exception('Database not initialized. Call Database::init() first.');
        }
        return self::$instance->connection;
    }

    /**
     * Exécute une requête SELECT
     */
    public static function select($query, $params = [])
    {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Exécute une requête SELECT et retourne une seule ligne
     */
    public static function selectOne($query, $params = [])
    {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Exécute une requête INSERT, UPDATE, DELETE
     */
    public static function execute($query, $params = [])
    {
        $stmt = self::getConnection()->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Exécute un INSERT et retourne l'ID inséré
     */
    public static function insert($query, $params = [])
    {
        self::execute($query, $params);
        return self::getConnection()->lastInsertId();
    }

    /**
     * Démarre une transaction
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }

    /**
     * Annule une transaction
     */
    public static function rollback()
    {
        return self::getConnection()->rollBack();
    }
}
