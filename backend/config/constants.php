<?php

declare(strict_types=1);

/**
 * Constantes globales de la aplicación.
 * 
 * @package RecreaSys\Config
 * @version 1.0
 */

// =============================================
// ENTORNO
// =============================================
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'America/Guayaquil');

// =============================================
// SEGURIDAD Y ENCRIPTACIÓN
// =============================================
define('ENCRYPT_METHOD', 'AES-256-CBC');
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'clave_super_segura');
define('SECRET_IV', $_ENV['SECRET_IV'] ?? 'vector_inicial_seguro');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// =============================================
// RATE LIMITING
// =============================================
define('RATE_LIMIT_GLOBAL_MAX', 60);
define('RATE_LIMIT_GLOBAL_WINDOW', 60); // segundos
define('RATE_LIMIT_LOGIN_MAX', 5);
define('RATE_LIMIT_LOGIN_WINDOW', 300); // 5 minutos
define('RATE_LIMIT_REGISTER_MAX', 3);
define('RATE_LIMIT_REGISTER_WINDOW', 3600); // 1 hora

// =============================================
// SESIÓN
// =============================================
define('SESSION_LIFETIME', 7200); // 2 horas
define('SESSION_NAME', 'recreasys_session');

// =============================================
// PAGINACIÓN
// =============================================
define('PAGINATION_DEFAULT_LIMIT', 20);
define('PAGINATION_MAX_LIMIT', 100);

// =============================================
// ESTADOS Y TIPOS PREDEFINIDOS
// =============================================
define('ESTADO_USUARIO_ACTIVO', 'Activo');
define('ESTADO_USUARIO_INACTIVO', 'Inactivo');
define('ESTADO_USUARIO_BLOQUEADO', 'Bloqueado');

define('TIPO_USUARIO_ADMIN', 'Administrador');
define('TIPO_USUARIO_TECNICO', 'Tecnico');
define('TIPO_USUARIO_LOGISTICA', 'Logistica');
define('TIPO_USUARIO_CONTABILIDAD', 'Contabilidad');
define('TIPO_USUARIO_USUARIO', 'Usuario');

define('ESPECIALIDAD_TECNICO_ENSAMBLADOR', 'Ensamblador');
define('ESPECIALIDAD_TECNICO_COMPROBADOR', 'Comprobador');
define('ESPECIALIDAD_TECNICO_MANTENIMIENTO', 'Mantenimiento');

// =============================================
// RUTAS DE ARCHIVOS
// =============================================
define('STORAGE_PATH', __DIR__ . '/../storage/');
define('RATE_LIMIT_STORAGE_FILE', STORAGE_PATH . 'rate_limits.json');
define('LOG_PATH', __DIR__ . '/../logs/');