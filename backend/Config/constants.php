<?php
/**
 * backend/Config/constants.php
 * Archivo de constantes globales del sistema
 * 
 * Define todas las constantes utilizadas en la aplicación para
 * centralizar la configuración y evitar valores mágicos.
 * 
 * @package Config
 * @author Tu Nombre
 * @version 1.0.0
 */
/**
 * @var string CONFIG_PATH
 * Ruta del directorio de configuración
 */
define('CONFIG_PATH', __DIR__);

/**
 * @var string DATE_FORMAT
 * Formato de fecha por defecto
 */
define('DATE_FORMAT', 'Y-m-d H:i:s');
// =============================================
// CONFIGURACIÓN DE SEGURIDAD
// =============================================

/**
 * @var string MÉTODO DE ENCRIPTACIÓN
 * Algoritmo utilizado para encriptar datos sensibles
 */
define('ENCRYPT_METHOD', 'AES-256-CBC');

/**
 * @var string CLAVE SECRETA
 * Clave para encriptación (debe cambiarse en producción)
 */
define('SECRET_KEY', 'clave_super_segura_cambiar_en_produccion_2024');

/**
 * @var string VECTOR DE INICIALIZACIÓN
 * IV para encriptación (16 bytes)
 */
define('SECRET_IV', 'vector_inicial_16_abcdefghijk');
if (!defined('API_BASE_PATH')) {
    define('API_BASE_PATH', '/api/public');
}
// =============================================
// CONFIGURACIÓN DE SESIÓN
// =============================================

/**
 * @var int TIEMPO DE VIDA DE SESIÓN
 * Tiempo máximo de inactividad en segundos (2 horas)
 */
define('SESSION_LIFETIME', 7200);

/**
 * @var string NOMBRE DE LA COOKIE DE SESIÓN
 */
define('SESSION_NAME', 'maquinas_recreativas_session');

// =============================================
// ROLES DE USUARIO
// =============================================

/**
 * @var array ROLES_PERMITIDOS
 * Lista de roles válidos en el sistema
 */
define('ROLES_PERMITIDOS', [
    'Administrador',
    'Tecnico',
    'Logistica',
    'Contabilidad',
    'Usuario'
]);

/**
 * @var array ESPECIALIDADES_TECNICO
 * Especialidades válidas para técnicos
 */
define('ESPECIALIDADES_TECNICO', [
    'Ensamblador',
    'Comprobador',
    'Mantenimiento'
]);

// =============================================
// ESTADOS DE USUARIO
// =============================================

/**
 * @var array ESTADOS_USUARIO
 * Estados posibles para un usuario
 */
define('ESTADOS_USUARIO', [
    'Activo',
    'Inhabilitado',
    'Suspendido',
    'Pendiente_asignacion'
]);

// =============================================
// ESTADOS DE MÁQUINA
// =============================================

/**
 * @var array ESTADOS_MAQUINA
 * Estados físicos/operativos de la máquina
 */
define('ESTADOS_MAQUINA', [
    'Ensamblandose',
    'Reensamblandose',
    'Comprobandose',
    'Distribuyendose',
    'Operativa',
    'No operativa',
    'Retirada'
]);

/**
 * @var array ETAPAS_MAQUINA
 * Etapas del ciclo de vida de la máquina
 */
define('ETAPAS_MAQUINA', [
    'Montaje',
    'Distribucion',
    'Recaudacion'
]);

// =============================================
// TIPOS DE COMPONENTES
// =============================================

/**
 * @var array TIPOS_COMPONENTE
 * Tipos de componentes disponibles
 */
define('TIPOS_COMPONENTE', [
    'Logistico',
    'Electronico',
    'Estructural',
    'Carcasa'
]);

// =============================================
// CONFIGURACIÓN DE PAGOS
// =============================================

/**
 * @var float PAGO_ENSAMBLADOR
 * Pago fijo por máquina para ensamblador
 */
define('PAGO_ENSAMBLADOR', 400.00);

/**
 * @var float PAGO_COMPROBADOR
 * Pago fijo por máquina para comprobador
 */
define('PAGO_COMPROBADOR', 400.00);

/**
 * @var float PAGO_MANTENIMIENTO
 * Pago fijo por mantenimiento
 */
define('PAGO_MANTENIMIENTO', 400.00);

/**
 * @var string EMPRESA_NOMBRE
 * Nombre de la empresa para informes
 */
define('EMPRESA_NOMBRE', 'Recrea Sys S.A.');

/**
 * @var string EMPRESA_DESCRIPCION
 * Descripción de la empresa para informes
 */
define('EMPRESA_DESCRIPCION', 'Empresa especializada en el ciclo de vida de máquinas recreativas');

// =============================================
// CONFIGURACIÓN DE PAGINACIÓN
// =============================================

/**
 * @var int ITEMS_POR_PAGINA
 * Cantidad de items por página por defecto
 */
define('ITEMS_POR_PAGINA', 10);

/**
 * @var int MAX_ITEMS_POR_PAGINA
 * Máximo permitido de items por página
 */
define('MAX_ITEMS_POR_PAGINA', 100);

// =============================================
// CONFIGURACIÓN DE RATE LIMITING
// =============================================

/**
 * @var int RATE_LIMIT_PUBLICO_MAX
 * Máximo de solicitudes para endpoints públicos
 */
define('RATE_LIMIT_PUBLICO_MAX', 5);

/**
 * @var int RATE_LIMIT_PUBLICO_TIEMPO
 * Ventana de tiempo para rate limiting público (segundos)
 */
define('RATE_LIMIT_PUBLICO_TIEMPO', 300);

/**
 * @var int RATE_LIMIT_PRIVADO_MAX
 * Máximo de solicitudes para endpoints privados
 */
define('RATE_LIMIT_PRIVADO_MAX', 60);

/**
 * @var int RATE_LIMIT_PRIVADO_TIEMPO
 * Ventana de tiempo para rate limiting privado (segundos)
 */
define('RATE_LIMIT_PRIVADO_TIEMPO', 60);

/**
 * @var int RATE_LIMIT_LOCAL_MAX
 * Máximo para localhost (pruebas)
 */
define('RATE_LIMIT_LOCAL_MAX', 300);

// =============================================
// ESTADOS DE REPORTE
// =============================================

/**
 * @var array ESTADOS_REPORTE
 * Estados posibles para un reporte
 */
define('ESTADOS_REPORTE', [
    'Pendiente',
    'En proceso',
    'Resuelto'
]);

// =============================================
// TIPOS DE NOTIFICACIÓN
// =============================================

/**
 * @var array TIPOS_NOTIFICACION
 * Tipos de notificaciones del sistema
 */
define('TIPOS_NOTIFICACION', [
    'Nuevo montaje',
    'Comprobar máquina',
    'Reensamblar máquina',
    'Distribuir máquina',
    'Mantenimiento',
    'Reporte',
    'Comentario'
]);

// =============================================
// CONFIGURACIÓN DE ARCHIVOS
// =============================================

/**
 * @var string RUTA_STORAGE
 * Ruta base para almacenamiento de archivos
 */
define('RUTA_STORAGE', __DIR__ . '/../storage/');

/**
 * @var string RUTA_RATE_LIMITS
 * Ruta del archivo de rate limits
 */
define('RUTA_RATE_LIMITS', RUTA_STORAGE . 'rate_limits.json');

/**
 * @var string LOCK_USUARIOS_INICIALES
 * Archivo de lock para usuarios iniciales
 */
define('LOCK_USUARIOS_INICIALES', __DIR__ . '/.usuarios_iniciales.lock');

// =============================================
// VALIDACIONES
// =============================================

/**
 * @var int LONGITUD_MIN_CONTRASENA
 * Longitud mínima para contraseñas
 */
define('LONGITUD_MIN_CONTRASENA', 8);

/**
 * @var int LONGITUD_MIN_CI
 * Longitud mínima para cédula
 */
define('LONGITUD_MIN_CI', 6);

/**
 * @var int LONGITUD_MIN_USUARIO
 * Longitud mínima para nombre de usuario
 */
define('LONGITUD_MIN_USUARIO', 3);