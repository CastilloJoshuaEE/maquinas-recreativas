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
    public static function getUsuarioRepository(?CacheInterface $cache = null): PDOUsuarioRepository
    {
        $key = 'usuario_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOUsuarioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de administradores
     */
    public static function getAdministradorRepository(?CacheInterface $cache = null): PDOAdministradorRepository
    {
        $key = 'admin_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOAdministradorRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de comercios
     */
    public static function getComercioRepository(?CacheInterface $cache = null): PDOComercioRepository
    {
        $key = 'comercio_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOComercioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de máquinas
     */
    public static function getMaquinaRepository(?CacheInterface $cache = null): PDOMaquinaRepository
    {
        $key = 'maquina_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOMaquinaRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de componentes
     */
    public static function getComponenteRepository(?CacheInterface $cache = null): PDOComponenteRepository
    {
        $key = 'componente_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOComponenteRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de recaudaciones
     */
    public static function getRecaudacionRepository(?CacheInterface $cache = null): PDORecaudacionRepository
    {
        $key = 'recaudacion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDORecaudacionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de reportes
     */
    public static function getReporteRepository(?CacheInterface $cache = null): PDOReporteRepository
    {
        $key = 'reporte_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOReporteRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de comentarios
     */
    public static function getComentarioRepository(?CacheInterface $cache = null): PDOComentarioRepository
    {
        $key = 'comentario_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOComentarioRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de notificaciones
     */
    public static function getNotificacionRepository(?CacheInterface $cache = null): PDONotificacionRepository
    {
        $key = 'notificacion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDONotificacionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de distribución
     */
    public static function getDistribucionRepository(?CacheInterface $cache = null): PDODistribucionRepository
    {
        $key = 'distribucion_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDODistribucionRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de historial
     */
    public static function getHistorialRepository(?CacheInterface $cache = null): PDOHistorialRepository
    {
        $key = 'historial_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOHistorialRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de técnicos
     */
    public static function getTecnicoRepository(?CacheInterface $cache = null): PDOTecnicoRepository
    {
        $key = 'tecnico_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOTecnicoRepository($db, $cache);
        }
        return self::$instances[$key];
    }

    /**
     * Obtiene el repositorio de montaje
     */
    public static function getMontajeRepository(?CacheInterface $cache = null): PDOMontajeRepository
    {
        $key = 'montaje_repo';
        if (!isset(self::$instances[$key])) {
            $db = self::getDatabase();
            $cache = $cache ?? self::getCache();
            self::$instances[$key] = new PDOMontajeRepository($db, $cache);
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