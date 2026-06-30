<?php
/**
 * infrastructure/repositories/RepositoryFactory.php
 * 
 * Factory para crear instancias de repositorios con el driver correcto
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Database\DatabaseFactory;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;

class RepositoryFactory
{
    private static array $instances = [];
    private static ?Database $db = null;

    /**
     * Obtiene la instancia de Database
     */
    private static function getDatabase(): Database
    {
        if (self::$db === null) {
            self::$db = new Database();
        }
        return self::$db;
    }

    /**
     * Obtiene la instancia de Cache
     */
    private static function getCache(): CacheInterface
    {
        return CacheFactory::create();
    }

    /**
     * Obtiene el repositorio de usuarios
     */
    public static function getUsuarioRepository(?CacheInterface $cache = null): MySQLUsuarioRepository
    {
        $key = 'usuario_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLUsuarioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de administradores
     */
    public static function getAdministradorRepository(?CacheInterface $cache = null): MySQLAdministradorRepository
    {
        $key = 'admin_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLAdministradorRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de comercios
     */
    public static function getComercioRepository(?CacheInterface $cache = null): MySQLComercioRepository
    {
        $key = 'comercio_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLComercioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de máquinas
     */
    public static function getMaquinaRepository(?CacheInterface $cache = null): MySQLMaquinaRepository
    {
        $key = 'maquina_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLMaquinaRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de componentes
     */
    public static function getComponenteRepository(?CacheInterface $cache = null): MySQLComponenteRepository
    {
        $key = 'componente_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLComponenteRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de recaudaciones
     */
    public static function getRecaudacionRepository(?CacheInterface $cache = null): MySQLRecaudacionRepository
    {
        $key = 'recaudacion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLRecaudacionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de reportes
     */
    public static function getReporteRepository(?CacheInterface $cache = null): MySQLReporteRepository
    {
        $key = 'reporte_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLReporteRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de comentarios
     */
    public static function getComentarioRepository(?CacheInterface $cache = null): MySQLComentarioRepository
    {
        $key = 'comentario_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLComentarioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de notificaciones
     */
    public static function getNotificacionRepository(?CacheInterface $cache = null): MySQLNotificacionRepository
    {
        $key = 'notificacion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLNotificacionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de distribución
     */
    public static function getDistribucionRepository(?CacheInterface $cache = null): MySQLDistribucionRepository
    {
        $key = 'distribucion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLDistribucionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de historial
     */
    public static function getHistorialRepository(?CacheInterface $cache = null): MySQLHistorialRepository
    {
        $key = 'historial_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLHistorialRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de técnicos
     */
    public static function getTecnicoRepository(?CacheInterface $cache = null): MySQLTecnicoRepository
    {
        $key = 'tecnico_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLTecnicoRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de montaje
     */
    public static function getMontajeRepository(?CacheInterface $cache = null): MySQLMontajeRepository
    {
        $key = 'montaje_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new MySQLMontajeRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Limpia todas las instancias cacheadas
     */
    public static function clearInstances(): void
    {
        self::$instances = [];
        self::$db = null;
    }
}