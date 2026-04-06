<?php
/**
 * Interfaces/Http/Controllers/HealthController.php
 *
 * Endpoints añadidos:
 *   GET  /v1/health/redis          → verifica la conexión Redis
 *   DELETE /v1/health/cache/flush  → vacía toda la caché (solo Administrador)
 *
 * Los endpoints originales (/v1/health y /v1/health/db) se conservan sin cambios.
 */
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class HealthController
{
    private CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? CacheFactory::create();
    }

    // =========================================================================
    // ENDPOINTS ORIGINALES
    // =========================================================================

    #[OA\Get(
        path: "/v1/health",
        summary: "Verificar estado de la API",
        tags: ["Health"],
        responses: [
            new OA\Response(
                response: 200,
                description: "API funcionando correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success",   type: "boolean", example: true),
                        new OA\Property(property: "status",    type: "string",  example: "ok"),
                        new OA\Property(property: "message",   type: "string"),
                        new OA\Property(property: "timestamp", type: "string",  format: "date-time"),
                        new OA\Property(property: "version",   type: "string",  example: "1.0.0")
                    ]
                )
            )
        ]
    )]
    public function check(Request $request): Response
    {
        return (new Response())->json([
            'success'   => true,
            'status'    => 'ok',
            'message'   => 'API de Máquinas Recreativas funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0.0',
            'cache'     => $this->cache->isAvailable() ? 'redis' : 'disabled',
        ]);
    }

    #[OA\Get(
        path: "/v1/health/db",
        summary: "Verificar conexión a la base de datos",
        tags: ["Health"],
        responses: [
            new OA\Response(response: 200, description: "Conexión exitosa"),
            new OA\Response(response: 500, description: "Error de conexión")
        ]
    )]
    public function testDb(Request $request): Response
    {
        try {
            $db     = new \maquinas_recreativas\Infrastructure\Database\Database();
            $conn   = $db->getConnection();
            $result = $conn->query("SELECT 1 as test");

            if ($result) {
                return (new Response())->json([
                    'success'  => true,
                    'message'  => 'Conexión a base de datos exitosa',
                    'database' => DB_NAME ?? 'unknown',
                ]);
            }

            return (new Response())->json([
                'success' => false,
                'message' => 'Error en la consulta de prueba',
            ], 500);
        } catch (\Exception $e) {
            return (new Response())->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // ENDPOINTS NUEVOS (Redis)
    // =========================================================================

    #[OA\Get(
        path: "/v1/health/redis",
        summary: "Verificar estado de la conexión Redis",
        tags: ["Health"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Estado de Redis",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success",   type: "boolean"),
                        new OA\Property(property: "available", type: "boolean"),
                        new OA\Property(property: "message",   type: "string"),
                        new OA\Property(property: "timestamp", type: "string", format: "date-time")
                    ]
                )
            )
        ]
    )]
    public function checkRedis(Request $request): Response
    {
        $available = $this->cache->isAvailable();

        if ($available) {
            // Prueba de escritura/lectura para confirmar que Redis responde
            $testKey = 'health:ping:' . time();
            $wrote   = $this->cache->set($testKey, 'pong', 5);
            $read    = $this->cache->get($testKey);
            $this->cache->delete($testKey);

            $working = $wrote && $read === 'pong';

            return (new Response())->json([
                'success'   => true,
                'available' => true,
                'working'   => $working,
                'message'   => $working
                    ? 'Redis conectado y operativo'
                    : 'Redis conectado pero las operaciones de lectura/escritura fallaron',
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        }

        return (new Response())->json([
            'success'   => true,   // La API funciona aunque Redis no esté
            'available' => false,
            'working'   => false,
            'message'   => 'Redis no disponible. El sistema opera sin caché (NullCache).',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    #[OA\Delete(
        path: "/v1/health/cache/flush",
        summary: "Vaciar toda la caché Redis (solo Administrador)",
        tags: ["Health"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Caché vaciada correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success",   type: "boolean"),
                        new OA\Property(property: "message",   type: "string"),
                        new OA\Property(property: "timestamp", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 403, description: "Solo administradores"),
            new OA\Response(response: 503, description: "Redis no disponible")
        ]
    )]
    public function flushCache(Request $request): Response
    {
        // Solo administradores autenticados pueden vaciar la caché
        if (!isset($_SESSION['ID_Usuario'])) {
            throw new DomainException('No autenticado', 401);
        }

        $rol = $_SESSION['rol'] ?? '';
        if (is_object($rol) && method_exists($rol, 'value')) {
            $rol = $rol->value();
        }
        if ($rol !== 'Administrador') {
            throw new DomainException('Solo los administradores pueden vaciar la caché', 403);
        }

        if (!$this->cache->isAvailable()) {
            return (new Response())->json([
                'success'   => false,
                'message'   => 'Redis no está disponible. No hay caché que vaciar.',
                'timestamp' => date('Y-m-d H:i:s'),
            ], 503);
        }

        $flushed = $this->cache->flush();

        return (new Response())->json([
            'success'   => $flushed,
            'message'   => $flushed
                ? 'Caché vaciada correctamente'
                : 'No se pudo vaciar la caché',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
