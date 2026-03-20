<?php
/**
 * RecreaSys - Global Constants
 * 
 * Define constantes globales utilizadas en toda la aplicación.
 * 
 * @package RecreaSys\Config
 * @author Tu Equipo
 * @version 1.0
 */

// Rutas del sistema
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
defined('SRC_PATH') or define('SRC_PATH', ROOT_PATH . '/src');
defined('CONFIG_PATH') or define('CONFIG_PATH', ROOT_PATH . '/config');
defined('STORAGE_PATH') or define('STORAGE_PATH', ROOT_PATH . '/storage');

// Fecha y hora
defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y-m-d H:i:s');
defined('DATE_FORMAT_SHORT') or define('DATE_FORMAT_SHORT', 'Y-m-d');

// Paginación por defecto
defined('DEFAULT_PAGE_SIZE') or define('DEFAULT_PAGE_SIZE', 50);
defined('MAX_PAGE_SIZE') or define('MAX_PAGE_SIZE', 1000);

// Estados y tipos comunes
defined('USER_TYPES') or define('USER_TYPES', ['Tecnico', 'Logistica', 'Contabilidad', 'Administrador', 'Usuario']);
defined('USER_STATUSES') or define('USER_STATUSES', ['Activo', 'Inactivo']);

// Especialidades de técnicos
defined('TECNICO_ESPECIALIDADES') or define('TECNICO_ESPECIALIDADES', ['Ensamblador', 'Comprobador', 'Mantenimiento']);

// Estados de máquina
defined('MAQUINA_ESTADOS') or define('MAQUINA_ESTADOS', [
    'Ensamblandose', 'Reensamblandose', 'Comprobandose', 
    'Distribuyendose', 'Operativa', 'No operativa', 'Retirada'
]);

// Etapas de máquina
defined('MAQUINA_ETAPAS') or define('MAQUINA_ETAPAS', ['Montaje', 'Distribucion', 'Recaudacion']);