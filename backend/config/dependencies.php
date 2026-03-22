<?php
/**
 * Archivo de configuración de dependencias (DI Container)
 * 
 * Centraliza la creación de objetos y sus dependencias para
 * facilitar el testing y el mantenimiento.
 * 
 * @package Config
 * @author Tu Nombre
 * @version 1.0.0
 */

// =============================================
// CARGAR CONFIGURACIÓN BASE
// =============================================
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';

// =============================================
// HELPERS Y UTILIDADES
// =============================================
require_once __DIR__ . '/../helper/CifradoHelper.php';
require_once __DIR__ . '/../helper/RateLimiter.php';
require_once __DIR__ . '/../helper/UsuarioHelper.php';
require_once __DIR__ . '/../helper/ValidationHelper.php';
require_once __DIR__ . '/../helper/HistorialHelper.php';

// =============================================
// DOMAIN - VALUE OBJECTS
// =============================================
require_once __DIR__ . '/../domain/shared/ValueObjects/Uuid.php';
require_once __DIR__ . '/../domain/shared/ValueObjects/Email.php';

// =============================================
// DOMAIN - EXCEPTIONS
// =============================================
require_once __DIR__ . '/../domain/shared/Exceptions/DomainException.php';

// =============================================
// DOMAIN - ENTIDADES
// =============================================
require_once __DIR__ . '/../domain/usuario/Usuario.php';
require_once __DIR__ . '/../domain/usuario/Tecnico.php';
require_once __DIR__ . '/../domain/usuario/Logistica.php';
require_once __DIR__ . '/../domain/usuario/TipoUsuario.php';
require_once __DIR__ . '/../domain/usuario/EstadoUsuario.php';

require_once __DIR__ . '/../domain/comercio/Comercio.php';

require_once __DIR__ . '/../domain/maquina/MaquinaRecreativa.php';
require_once __DIR__ . '/../domain/maquina/EstadoMaquina.php';
require_once __DIR__ . '/../domain/maquina/EtapaMaquina.php';

require_once __DIR__ . '/../domain/componente/Componente.php';
require_once __DIR__ . '/../domain/componente/TipoComponente.php';
require_once __DIR__ . '/../domain/componente/ComponenteUsuario.php';

require_once __DIR__ . '/../domain/montaje/Montaje.php';

require_once __DIR__ . '/../domain/historial/HistorialMaquina.php';
require_once __DIR__ . '/../domain/historial/HistorialActividad.php';

require_once __DIR__ . '/../domain/distribucion/InformeDistribucion.php';

require_once __DIR__ . '/../domain/recaudacion/Recaudacion.php';
require_once __DIR__ . '/../domain/recaudacion/InformeRecaudacion.php';
require_once __DIR__ . '/../domain/recaudacion/DetalleInforme.php';

require_once __DIR__ . '/../domain/notificacion/NotificacionMaquina.php';
require_once __DIR__ . '/../domain/notificacion/NotificacionReporte.php';

require_once __DIR__ . '/../domain/reporte/Reporte.php';
require_once __DIR__ . '/../domain/reporte/EstadoReporte.php';

require_once __DIR__ . '/../domain/comentario/Comentario.php';

// =============================================
// DOMAIN - REPOSITORY INTERFACES
// =============================================
require_once __DIR__ . '/../domain/usuario/UsuarioRepository.php';
require_once __DIR__ . '/../domain/comercio/ComercioRepository.php';
require_once __DIR__ . '/../domain/maquina/MaquinaRepository.php';
require_once __DIR__ . '/../domain/componente/ComponenteRepository.php';
require_once __DIR__ . '/../domain/montaje/MontajeRepository.php';
require_once __DIR__ . '/../domain/historial/HistorialRepository.php';
require_once __DIR__ . '/../domain/distribucion/DistribucionRepository.php';
require_once __DIR__ . '/../domain/recaudacion/RecaudacionRepository.php';
require_once __DIR__ . '/../domain/notificacion/NotificacionRepository.php';
require_once __DIR__ . '/../domain/reporte/ReporteRepository.php';
require_once __DIR__ . '/../domain/comentario/ComentarioRepository.php';

// =============================================
// INFRASTRUCTURE - REPOSITORY IMPLEMENTATIONS
// =============================================
require_once __DIR__ . '/../infrastructure/database/Database.php';
require_once __DIR__ . '/../infrastructure/database/Inserter.php';

require_once __DIR__ . '/../infrastructure/repositories/MySQLUsuarioRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLAdministradorRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLComercioRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLMaquinaRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLComponenteRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLMontajeRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLHistorialRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLDistribucionRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLRecaudacionRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLNotificacionRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLReporteRepository.php';
require_once __DIR__ . '/../infrastructure/repositories/MySQLComentarioRepository.php';

// =============================================
// INFRASTRUCTURE - SECURITY
// =============================================
require_once __DIR__ . '/../infrastructure/security/CifradoHelper.php';
require_once __DIR__ . '/../infrastructure/security/RateLimiter.php';
require_once __DIR__ . '/../infrastructure/security/UsuarioHelper.php';
require_once __DIR__ . '/../infrastructure/security/ValidationHelper.php';
require_once __DIR__ . '/../infrastructure/security/HistorialHelper.php';

// =============================================
// APPLICATION - COMMANDS
// =============================================
// Usuario commands
require_once __DIR__ . '/../application/commands/usuario/RegistrarUsuarioCommand.php';
require_once __DIR__ . '/../application/commands/usuario/RegistrarUsuarioHandler.php';
require_once __DIR__ . '/../application/commands/usuario/RegistrarUsuarioAdminCommand.php';
require_once __DIR__ . '/../application/commands/usuario/RegistrarUsuarioAdminHandler.php';
require_once __DIR__ . '/../application/commands/usuario/ActualizarPerfilHandler.php';
require_once __DIR__ . '/../application/commands/usuario/ActualizarUsuarioAsignadoCommand.php';
require_once __DIR__ . '/../application/commands/usuario/ActualizarUsuarioAsignadoHandler.php';
require_once __DIR__ . '/../application/commands/usuario/RecuperarContrasenaCommand.php';
require_once __DIR__ . '/../application/commands/usuario/RecuperarContrasenaHandler.php';
require_once __DIR__ . '/../application/commands/usuario/CambiarEstadoUsuarioCommand.php';
require_once __DIR__ . '/../application/commands/usuario/CambiarEstadoUsuarioHandler.php';
require_once __DIR__ . '/../application/commands/usuario/EliminarUsuarioCommand.php';
require_once __DIR__ . '/../application/commands/usuario/EliminarUsuarioHandler.php';
require_once __DIR__ . '/../application/commands/usuario/LoginCommand.php';
require_once __DIR__ . '/../application/commands/usuario/LoginHandler.php';
require_once __DIR__ . '/../application/commands/usuario/LogoutCommand.php';
require_once __DIR__ . '/../application/commands/usuario/LogoutHandler.php';
require_once __DIR__ . '/../application/commands/usuario/RegistrarActividadCommand.php';
require_once __DIR__ . '/../application/commands/usuario/RegistrarActividadHandler.php';

// Comercio commands
require_once __DIR__ . '/../application/commands/comercio/RegistrarComercioCommand.php';
require_once __DIR__ . '/../application/commands/comercio/RegistrarComercioHandler.php';

// =============================================
// APPLICATION - QUERIES
// =============================================
// Usuario queries
require_once __DIR__ . '/../application/queries/usuario/ObtenerUsuarioPorIdQuery.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerUsuarioPorIdHandler.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerTodosUsuariosHandler.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerTecnicosPorEspecialidadHandler.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerUsuariosPorTipoQuery.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerUsuariosPorTipoHandler.php';
require_once __DIR__ . '/../application/queries/usuario/BuscarPorEmailQuery.php';
require_once __DIR__ . '/../application/queries/usuario/BuscarPorEmailHandler.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerHistorialActividadesQuery.php';
require_once __DIR__ . '/../application/queries/usuario/ObtenerHistorialActividadesHandler.php';

// Comercio queries
require_once __DIR__ . '/../application/queries/comercio/ObtenerComerciosQuery.php';
require_once __DIR__ . '/../application/queries/comercio/ObtenerComerciosHandler.php';

// =============================================
// INTERFACES - CONTROLLERS
// =============================================
require_once __DIR__ . '/../interfaces/http/controllers/UsuarioController.php';
require_once __DIR__ . '/../interfaces/http/controllers/AdministradorController.php';
require_once __DIR__ . '/../interfaces/http/controllers/ComercioController.php';
require_once __DIR__ . '/../interfaces/http/controllers/MaquinaController.php';
require_once __DIR__ . '/../interfaces/http/controllers/ComponenteController.php';
require_once __DIR__ . '/../interfaces/http/controllers/HistorialMaquinaController.php';
require_once __DIR__ . '/../interfaces/http/controllers/DistribucionController.php';
require_once __DIR__ . '/../interfaces/http/controllers/InformeController.php';
require_once __DIR__ . '/../interfaces/http/controllers/NotificacionController.php';
require_once __DIR__ . '/../interfaces/http/controllers/ReporteController.php';
require_once __DIR__ . '/../interfaces/http/controllers/ComentarioController.php';

/**
 * Clase contenedor de dependencias (Service Container)
 * 
 * Implementa un contenedor simple para gestionar las dependencias
 * y facilitar la inyección en tests y producción.
 * 
 * @package Config
 */
class Dependencies {
    
    /**
     * @var array Instancias de servicios
     */
    private static $instances = [];
    
    /**
     * @var array Definiciones de fábricas
     */
    private static $factories = [];
    
    /**
     * Constructor privado para evitar instanciación
     */
    private function __construct() {}
    
    /**
     * Registra una fábrica para una clase
     * 
     * @param string $class Nombre de la clase
     * @param callable $factory Función fábrica
     * @return void
     */
    public static function register($class, callable $factory) {
        self::$factories[$class] = $factory;
    }
    
    /**
     * Obtiene una instancia de una clase
     * 
     * @param string $class Nombre de la clase
     * @return mixed Instancia de la clase
     * @throws Exception Si no hay fábrica registrada
     */
    public static function get($class) {
        if (!isset(self::$instances[$class])) {
            if (!isset(self::$factories[$class])) {
                throw new Exception("No factory registered for class: {$class}");
            }
            self::$instances[$class] = self::$factories[$class]();
        }
        return self::$instances[$class];
    }
    
    /**
     * Resetea todas las instancias (útil para tests)
     * 
     * @return void
     */
    public static function reset() {
        self::$instances = [];
    }
}

// =============================================
// REGISTRO DE FÁBRICAS POR DEFECTO
// =============================================

// Database
Dependencies::register(Database::class, function() {
    return new Database();
});

// Repositories
Dependencies::register(MySQLUsuarioRepository::class, function() {
    return new MySQLUsuarioRepository(Dependencies::get(Database::class));
});

Dependencies::register(MySQLComercioRepository::class, function() {
    return new MySQLComercioRepository(Dependencies::get(Database::class));
});

Dependencies::register(MySQLMaquinaRepository::class, function() {
    return new MySQLMaquinaRepository(Dependencies::get(Database::class));
});

Dependencies::register(MySQLComponenteRepository::class, function() {
    return new MySQLComponenteRepository(Dependencies::get(Database::class));
});

Dependencies::register(MySQLHistorialRepository::class, function() {
    return new MySQLHistorialRepository(Dependencies::get(Database::class));
});

// Handlers
Dependencies::register(LoginHandler::class, function() {
    return new LoginHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(RegistrarUsuarioHandler::class, function() {
    return new RegistrarUsuarioHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerUsuarioPorIdHandler::class, function() {
    return new ObtenerUsuarioPorIdHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerComerciosHandler::class, function() {
    return new ObtenerComerciosHandler(Dependencies::get(MySQLComercioRepository::class));
});

Dependencies::register(RegistrarComercioHandler::class, function() {
    return new RegistrarComercioHandler(Dependencies::get(MySQLComercioRepository::class));
});

// Controllers
Dependencies::register(UsuarioController::class, function() {
    return new UsuarioController(
        Dependencies::get(LoginHandler::class),
        Dependencies::get(RegistrarUsuarioHandler::class),
        Dependencies::get(ObtenerUsuarioPorIdHandler::class)
    );
});

Dependencies::register(ComercioController::class, function() {
    return new ComercioController(
        Dependencies::get(ObtenerComerciosHandler::class),
        Dependencies::get(RegistrarComercioHandler::class)
    );
});