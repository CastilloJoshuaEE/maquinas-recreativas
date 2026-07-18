<?php
/**
 * infrastructure/database/QueryBuilder.php
 */

namespace maquinas_recreativas\Infrastructure\Database;

use PDO;

class QueryBuilder
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getDb(): Database
    {
        return $this->db;
    }

    public function getDriver(): string
    {
        return $this->db->getDriver();
    }

    public function isMySQL(): bool
    {
        return $this->db->isMySQL();
    }

    public function isPostgreSQL(): bool
    {
        return $this->db->isPostgreSQL();
    }

    /**
     * Obtiene el nombre de la función UUID según el driver
     */
    public function getUuidFunction(): string
    {
        return $this->db->getUuidFunction();
    }

    /**
     * Obtiene el tipo de dato para UUID
     */
    public function getUuidType(): string
    {
        return $this->isPostgreSQL() ? 'UUID' : 'CHAR(36)';
    }

    /**
     * Obtiene el tipo de dato para JSON
     */
    public function getJsonType(): string
    {
        return $this->isPostgreSQL() ? 'JSONB' : 'JSON';
    }

    /**
     * Construye una cláusula LIMIT/OFFSET
     */
    public function buildLimitOffset(int $limit, int $offset = 0): string
    {
        if ($this->isPostgreSQL()) {
            return "LIMIT {$limit} OFFSET {$offset}";
        }
        return "LIMIT {$limit} OFFSET {$offset}";
    }

    /**
     * Construye una cláusula LIKE con escape
     */
    public function buildLike(string $field, string $value): string
    {
        if ($this->isPostgreSQL()) {
            return "{$field} ILIKE ?";
        }
        return "{$field} LIKE ?";
    }

    /**
     * Obtiene el SQL para UUID en SELECT
     */
    public function getUuidSelect(): string
    {
        if ($this->isPostgreSQL()) {
            return "gen_random_uuid()";
        }
        return "UUID()";
    }

    /**
     * Obtiene el SQL para UUID en DEFAULT de columna
     */
    public function getUuidDefault(): string
    {
        if ($this->isPostgreSQL()) {
            return "DEFAULT gen_random_uuid()";
        }
        return "DEFAULT (UUID())";
    }
}