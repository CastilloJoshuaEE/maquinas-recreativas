<?php
/**
 * infrastructure/database/Database.php
 */

namespace maquinas_recreativas\Infrastructure\Database;

use PDO;
use PDOStatement;

class Database
{
    public PDO $connection;
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

    public function isPostgres(): bool
    {
        return $this->isPostgreSQL();
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

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

    public function getLastInsertId(?string $sequence = null): string
    {
        if ($this->isPostgreSQL()) {
            return $this->connection->lastInsertId($sequence ?? '');
        }
        return $this->connection->lastInsertId();
    }

    public function getUuidFunction(): string
    {
        return $this->isPostgreSQL() ? 'gen_random_uuid()' : 'UUID()';
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    public function clearPendingResults(): void
    {
        // PDO no necesita limpieza, pero mantenemos por compatibilidad
    }

    public function escapeString(string $string): string
    {
        return $this->connection->quote($string);
    }

    // ================================================================
    // MÉTODOS AUXILIARES PARA PROCEDIMIENTOS ALMACENADOS CON PDO
    // ================================================================

    /**
     * Ejecuta un procedimiento almacenado con parámetros
     */
    public function callStoredProcedure(string $name, array $params = []): array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        
        do {
            try {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $results[] = $rows;
                }
            } catch (\PDOException $e) {
                // No hay más resultados
                break;
            }
        } while ($stmt->nextRowset());
        
        $stmt->closeCursor();
        
        return $results;
    }

    /**
     * Ejecuta un procedimiento almacenado y retorna el primer conjunto de resultados
     */
    public function callStoredProcedureFirst(string $name, array $params = []): array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->clearPendingResults();
        
        return $result;
    }

    /**
     * Ejecuta un procedimiento almacenado con parámetros OUT usando variables de sesión
     */
    public function callStoredProcedureWithOut(string $name, array $params, string $outVariable): array
    {
        // Construir la llamada con parámetros
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders}, @{$outVariable})";
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        // Recuperar el valor de la variable OUT
        $result = $this->queryOne("SELECT @{$outVariable} as result");
        
        $stmt->closeCursor();
        $this->clearPendingResults();
        
        return [
            'result' => $result['result'] ?? null,
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?? []
        ];
    }

    /**
     * Ejecuta un procedimiento almacenado con parámetros OUT (versión simple)
     */
    public function callWithOut(string $name, array $params, string $outVariable): mixed
    {
        $result = $this->callStoredProcedureWithOut($name, $params, $outVariable);
        return $result['result'];
    }

    /**
     * Ejecuta un procedimiento almacenado y retorna solo el primer resultado
     */
    public function callStoredProcedureSingle(string $name, array $params = []): ?array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->clearPendingResults();
        
        return $result ?: null;
    }
}