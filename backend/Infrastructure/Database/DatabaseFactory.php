<?php
/**
 * infrastructure/database/DatabaseFactory.php
 */

namespace maquinas_recreativas\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseFactory
{
    private static ?PDO $instance = null;
    private static string $driver;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$driver = $_ENV['DB_DRIVER'] ?? 'mysql';
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $driver = self::$driver;
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? '6543' : '3306');
        $dbname = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = match($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$dbname}",
            'mysql' => "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            default => throw new RuntimeException("Driver no soportado: {$driver}")
        };

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            if ($driver === 'pgsql') {
                $pdo->exec("SET timezone TO 'America/Guayaquil'");
            }

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("Error de conexión a base de datos: " . $e->getMessage());
        }
    }

    public static function getDriver(): string
    {
        return self::$driver;
    }

    public static function isPostgreSQL(): bool
    {
        return self::$driver === 'pgsql';
    }

    public static function isMySQL(): bool
    {
        return self::$driver === 'mysql';
    }
}