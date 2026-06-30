<?php
/**
 * infrastructure/database/Database.php
 */

namespace maquinas_recreativas\Infrastructure\Database;

use PDO;
use PDOStatement;

class Database
{
    private PDO $connection;
    private string $driver;

    public function __construct()
    {
        $this->connection = DatabaseFactory::getConnection();
        $this->driver = DatabaseFactory::getDriver();
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function isMySQL(): bool
    {
        return $this->driver === 'mysql';
    }

    public function isPostgreSQL(): bool
    {
        return $this->driver === 'pgsql';
    }

    /**
     * Alias de isPostgreSQL() para compatibilidad
     */
    public function isPostgres(): bool
    {
        return $this->isPostgreSQL();
    }

    /**
     * Ejecuta una consulta y retorna los resultados
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta una consulta y retorna una fila
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ejecuta una consulta que no retorna datos
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Prepara una consulta con placeholders compatibles
     */
    public function prepare(string $sql): PDOStatement
    {
        if ($this->isPostgreSQL()) {
            $sql = $this->convertPlaceholders($sql);
        }
        return $this->connection->prepare($sql);
    }

    private function convertPlaceholders(string $sql): string
    {
        $count = 0;
        return preg_replace_callback('/\?/', function() use (&$count) {
            $count++;
            return '$' . $count;
        }, $sql);
    }

    /**
     * Obtiene el último ID insertado
     * 
     * @param string|null $sequence Nombre de la secuencia (para PostgreSQL)
     */
    public function getLastInsertId(?string $sequence = null): string
    {
        if ($this->isPostgreSQL()) {
            return $this->connection->lastInsertId($sequence ?? '');
        }
        return $this->connection->lastInsertId();
    }

    /**
     * Obtiene el nombre de la función UUID
     */
    public function getUuidFunction(): string
    {
        return $this->isPostgreSQL() ? 'gen_random_uuid()' : 'UUID()';
    }

    /**
     * Inicia una transacción
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma una transacción
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revierte una transacción
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Verifica si hay una transacción activa
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Limpia resultados pendientes (compatibilidad con MySQL)
     */
    public function clearPendingResults(): void
    {
        // En PDO no es necesario, pero mantenemos por compatibilidad
    }

    /**
     * Escapa un string para SQL
     */
    public function escapeString(string $string): string
    {
        return $this->connection->quote($string);
    }
}