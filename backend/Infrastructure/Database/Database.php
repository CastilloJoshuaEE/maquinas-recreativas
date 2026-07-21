<?php
/**
 * infrastructure/database/Database.php
 *
 * Capa de abstraccion de base de datos. Permite que el mismo codigo de
 * repositorio funcione tanto contra MySQL (procedimientos con CALL) como
 * contra PostgreSQL (funciones invocadas con SELECT / SELECT * FROM),
 * sin que los repositorios tengan que saber cual motor esta activo.
 */

namespace maquinas_recreativas\Infrastructure\Database;

use PDO;
use PDOStatement;

class Database
{
    public PDO $connection;
    private string $driver;

    /**
     * Funciones que en PostgreSQL fueron creadas con RETURNS VOID
     * (es decir, no devuelven filas: inserts, updates, deletes).
     * Para estas NO se puede usar "SELECT * FROM funcion(...)" porque
     * PostgreSQL exige que la funcion devuelva un conjunto de filas
     * (TABLE/SETOF) para poder usarse en una clausula FROM.
     *
     * Si agregas una nueva funcion PL/pgSQL que no retorna tabla,
     * agrega su nombre aqui.
     */
    private const VOID_FUNCTIONS = [
        'sp_insertar_usuario', 'sp_actualizar_usuario', 'sp_cambiar_estado_usuario',
        'sp_cambiar_contrasena_usuario', 'sp_actualizar_username', 'sp_eliminar_usuario',
        'sp_registrar_actividad', 'sp_registrar_logout', 'sp_insertar_tecnico',
        'sp_incrementar_actividades_tecnico', 'sp_insertar_comercio', 'sp_actualizar_comercio',
        'sp_eliminar_comercio', 'sp_insertar_maquina', 'sp_actualizar_estado_maquina',
        'sp_actualizar_maquina', 'sp_asignar_tecnico_mantenimiento', 'sp_asignar_componente',
        'sp_liberar_componente', 'sp_liberar_componentes_usuario', 'sp_insertar_montaje',
        'sp_crear_notificacion_maquina', 'sp_marcar_leida_maquina', 'sp_crear_notificacion_reporte',
        'sp_marcar_leida_reporte', 'sp_marcar_todas_leidas_reporte', 'sp_insertar_reporte',
        'sp_actualizar_estado_reporte', 'sp_insertar_comentario', 'sp_editar_comentario',
        'sp_eliminar_comentario', 'sp_eliminar_comentarios_reporte', 'sp_insertar_recaudacion',
        'sp_actualizar_recaudacion', 'sp_eliminar_recaudacion', 'sp_guardar_informe_recaudacion',
        'sp_guardar_detalle_informe', 'sp_guardar_informe_distribucion', 'sp_actualizar_estado_distribucion',
        'sp_insertar_historial_maquina',
    ];

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
        return preg_replace_callback('/\?/', function () use (&$count) {
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
    // ABSTRACCION UNIVERSAL: CALL (MySQL) <-> SELECT / SELECT * FROM (PostgreSQL)
    // ================================================================

    /**
     * Prepara una llamada escrita en sintaxis "CALL nombre(?, ?, ...)".
     *
     * - En MySQL se ejecuta literalmente como CALL, igual que antes.
     * - En PostgreSQL se traduce automaticamente a:
     *     - "SELECT * FROM nombre(?, ?, ...)"  si la funcion devuelve tabla
     *     - "SELECT nombre(?, ?, ...)"          si la funcion es VOID
     *     - "SELECT nombre(?, ?) AS resultado"  si el ultimo argumento es
     *       un parametro de salida estilo MySQL (@variable), que en
     *       PostgreSQL simplemente se descarta porque la funcion ya
     *       retorna el valor directamente.
     *
     * Esto permite que los repositorios sigan escribiendo
     *   $stmt = $this->db->prepareCall("CALL sp_x(?, ?)");
     * sin preocuparse de contra que motor estan corriendo.
     */
    public function prepareCall(string $sql): PDOStatement
    {
        if ($this->isPostgreSQL()) {
            $sql = $this->translateCallToPostgres($sql);
        }
        return $this->prepare($sql);
    }

    private function translateCallToPostgres(string $sql): string
    {
        if (!preg_match('/^\s*CALL\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*)\)\s*;?\s*$/is', trim($sql), $m)) {
            // No es una sentencia CALL reconocible: se deja intacta
            return $sql;
        }

        $funcName = $m[1];
        $argsStr = trim($m[2]);
        $args = $argsStr === '' ? [] : array_map('trim', explode(',', $argsStr));

        $inArgs = [];
        $hasOutParam = false;
        foreach ($args as $arg) {
            if (strpos($arg, '@') === 0) {
                // Parametro de salida estilo MySQL: no existe en PostgreSQL,
                // la funcion ya retorna el valor directamente.
                $hasOutParam = true;
                continue;
            }
            $inArgs[] = $arg;
        }

        $placeholders = implode(', ', $inArgs);

        if ($hasOutParam) {
            return "SELECT {$funcName}({$placeholders}) AS resultado";
        }

        if (in_array($funcName, self::VOID_FUNCTIONS, true)) {
            return "SELECT {$funcName}({$placeholders})";
        }

        return "SELECT * FROM {$funcName}({$placeholders})";
    }

    /**
     * Ejecuta una funcion/procedimiento que en MySQL usaba un parametro de
     * salida (OUT) via variable de sesion (@variable) y devuelve el valor
     * escalar resultante, sin que el repositorio tenga que preocuparse por
     * el motor de base de datos.
     *
     * Reemplaza el patron anterior:
     *   $stmt = $conn->prepare("CALL sp_x(?, @out)");
     *   $stmt->execute([...]);
     *   $result = $conn->query("SELECT @out as out");
     *
     * Por:
     *   $valor = $this->db->callScalarProcedure('sp_x', [...]);
     */
    public function callScalarProcedure(string $procName, array $params = []): mixed
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));

        if ($this->isPostgreSQL()) {
            $stmt = $this->prepare("SELECT {$procName}({$placeholders}) AS resultado");
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $row['resultado'] ?? null;
        }

        $stmt = $this->prepare("CALL {$procName}({$placeholders}, @resultado)");
        $stmt->execute($params);
        $stmt->closeCursor();

        $result = $this->connection->query('SELECT @resultado AS resultado');
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();

        return $row['resultado'] ?? null;
    }

    // ================================================================
    // METODOS AUXILIARES PARA PROCEDIMIENTOS ALMACENADOS CON PDO
    // (se mantienen por compatibilidad, ahora tambien funcionan con
    //  PostgreSQL porque internamente usan prepareCall)
    // ================================================================

    /**
     * Ejecuta un procedimiento/funcion con parametros y retorna todos los
     * conjuntos de resultados disponibles.
     */
    public function callStoredProcedure(string $name, array $params = []): array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";

        $stmt = $this->prepareCall($sql);
        $stmt->execute($params);

        $results = [];

        do {
            try {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $results[] = $rows;
                }
            } catch (\PDOException $e) {
                break;
            }
        } while (method_exists($stmt, 'nextRowset') && $stmt->nextRowset());

        $stmt->closeCursor();

        return $results;
    }

    /**
     * Ejecuta un procedimiento/funcion y retorna el primer conjunto de resultados
     */
    public function callStoredProcedureFirst(string $name, array $params = []): array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";

        $stmt = $this->prepareCall($sql);
        $stmt->execute($params);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->clearPendingResults();

        return $result;
    }

    /**
     * Ejecuta un procedimiento con un parametro de salida (OUT) usando el
     * mecanismo correcto segun el motor activo.
     */
    public function callStoredProcedureWithOut(string $name, array $params, string $outVariable): array
    {
        if ($this->isPostgreSQL()) {
            $resultado = $this->callScalarProcedure($name, $params);
            return [
                'result' => $resultado,
                'rows' => [],
            ];
        }

        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders}, @{$outVariable})";

        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        $result = $this->queryOne("SELECT @{$outVariable} as result");

        $stmt->closeCursor();
        $this->clearPendingResults();

        return [
            'result' => $result['result'] ?? null,
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [],
        ];
    }

    /**
     * Ejecuta un procedimiento con parametro OUT (version simple)
     */
    public function callWithOut(string $name, array $params, string $outVariable): mixed
    {
        return $this->callScalarProcedure($name, $params);
    }

    /**
     * Ejecuta un procedimiento/funcion y retorna solo el primer resultado
     */
    public function callStoredProcedureSingle(string $name, array $params = []): ?array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "CALL {$name}({$placeholders})";

        $stmt = $this->prepareCall($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $this->clearPendingResults();

        return $result ?: null;
    }
}