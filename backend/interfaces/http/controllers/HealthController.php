<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use maquinas_recreativas\Core\Response;

class HealthController
{
    private CacheInterface $cache;
    private static array $metrics = [
        'http_requests_total' => [],
        'http_request_duration_seconds' => []
    ];

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? CacheFactory::create();
        define('DB_NAME', $_ENV['DB_NAME'] ?? 'bd_recrea_sys');
    }

    public function check(): Response
    {
        $this->incrementMetric('health_check');
        
        return (new Response())->json([
            'success'   => true,
            'status'    => 'ok',
            'message'   => 'API de Máquinas Recreativas funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0.0',
            'cache'     => $this->cache->isAvailable() ? 'redis' : 'disabled',
        ]);
    }

    public function testDb(): Response
    {
        $this->incrementMetric('db_check');
        $startTime = microtime(true);

        try {
            $db     = new \maquinas_recreativas\Infrastructure\Database\Database();
            $conn   = $db->getConnection();
            $result = $conn->query("SELECT 1 as test");

            $duration = (microtime(true) - $startTime) * 1000;
            $this->recordDuration('db_check', $duration);

            if ($result) {
                return (new Response())->json([
                    'success'  => true,
                    'message'  => 'Conexión a base de datos exitosa',
                    'database' => DB_NAME ?? 'unknown',
                    'duration_ms' => round($duration, 2),
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

    private function incrementMetric(string $endpoint): void
    {
        if (!isset(self::$metrics['http_requests_total'][$endpoint])) {
            self::$metrics['http_requests_total'][$endpoint] = 0;
        }
        self::$metrics['http_requests_total'][$endpoint]++;
    }

    private function recordDuration(string $endpoint, float $durationMs): void
    {
        if (!isset(self::$metrics['http_request_duration_seconds'][$endpoint])) {
            self::$metrics['http_request_duration_seconds'][$endpoint] = [];
        }
        self::$metrics['http_request_duration_seconds'][$endpoint][] = $durationMs;
        
        if (count(self::$metrics['http_request_duration_seconds'][$endpoint]) > 1000) {
            array_shift(self::$metrics['http_request_duration_seconds'][$endpoint]);
        }
    }
}