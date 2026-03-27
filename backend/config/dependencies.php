<?php
/**
 * backend/config/dependencies.php
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
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/constants.php';

// =============================================
// HELPERS Y UTILIDADES
// =============================================
require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/RateLimiter.php';
require_once __DIR__ . '/../Infrastructure/Security/UsuarioHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/ValidationHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/HistorialHelper.php';

// =============================================
// Domain - VALUE OBJECTS
// =============================================
require_once __DIR__ . '/../Domain/Shared/ValueObjects/Uuid.php';
require_once __DIR__ . '/../Domain/Shared/ValueObjects/Email.php';

// =============================================
// Domain - EXCEPTIONS
// =============================================
require_once __DIR__ . '/../Domain/Shared/Exceptions/DomainException.php';

// =============================================
// Domain - ENTIDADES
// =============================================
require_once __DIR__ . '/../Domain/Usuario/Usuario.php';
require_once __DIR__ . '/../Domain/Usuario/Tecnico.php';
require_once __DIR__ . '/../Domain/Usuario/Logistica.php';
require_once __DIR__ . '/../Domain/Usuario/TipoUsuario.php';
require_once __DIR__ . '/../Domain/Usuario/EstadoUsuario.php';

require_once __DIR__ . '/../Domain/comercio/Comercio.php';

require_once __DIR__ . '/../Domain/Maquina/MaquinaRecreativa.php';
require_once __DIR__ . '/../Domain/Maquina/EstadoMaquina.php';
require_once __DIR__ . '/../Domain/Maquina/EtapaMaquina.php';

require_once __DIR__ . '/../Domain/Componente/Componente.php';
require_once __DIR__ . '/../Domain/Componente/TipoComponente.php';
require_once __DIR__ . '/../Domain/Componente/ComponenteUsuario.php';

require_once __DIR__ . '/../Domain/Montaje/Montaje.php';

require_once __DIR__ . '/../Domain/Historial/HistorialMaquina.php';
require_once __DIR__ . '/../Domain/Historial/HistorialActividad.php';

require_once __DIR__ . '/../Domain/Distribucion/InformeDistribucion.php';

require_once __DIR__ . '/../Domain/Recaudacion/Recaudacion.php';
require_once __DIR__ . '/../Domain/Recaudacion/InformeRecaudacion.php';
require_once __DIR__ . '/../Domain/Recaudacion/DetalleInforme.php';

require_once __DIR__ . '/../Domain/Notificacion/NotificacionMaquina.php';
require_once __DIR__ . '/../Domain/Notificacion/NotificacionReporte.php';

require_once __DIR__ . '/../Domain/Reporte/Reporte.php';
require_once __DIR__ . '/../Domain/Reporte/EstadoReporte.php';

require_once __DIR__ . '/../Domain/comentario/Comentario.php';

// =============================================
// Domain - REPOSITORY INTERFACES
// =============================================
require_once __DIR__ . '/../Domain/Usuario/UsuarioRepository.php';
require_once __DIR__ . '/../Domain/comercio/ComercioRepository.php';
require_once __DIR__ . '/../Domain/Maquina/MaquinaRepository.php';
require_once __DIR__ . '/../Domain/Componente/ComponenteRepository.php';
require_once __DIR__ . '/../Domain/Montaje/MontajeRepository.php';
require_once __DIR__ . '/../Domain/Historial/HistorialRepository.php';
require_once __DIR__ . '/../Domain/Distribucion/DistribucionRepository.php';
require_once __DIR__ . '/../Domain/Recaudacion/RecaudacionRepository.php';
require_once __DIR__ . '/../Domain/Notificacion/NotificacionRepository.php';
require_once __DIR__ . '/../Domain/Reporte/ReporteRepository.php';
require_once __DIR__ . '/../Domain/comentario/ComentarioRepository.php';

// =============================================
// INFRASTRUCTURE - REPOSITORY IMPLEMENTATIONS
// =============================================
require_once __DIR__ . '/../Infrastructure/Database/Database.php';
require_once __DIR__ . '/../Infrastructure/Database/Inserter.php';

require_once __DIR__ . '/../Infrastructure/Repositories/MySQLUsuarioRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLAdministradorRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLComercioRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLMaquinaRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLComponenteRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLMontajeRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLHistorialRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLDistribucionRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLRecaudacionRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLNotificacionRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLReporteRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/MySQLComentarioRepository.php';

// =============================================
// INFRASTRUCTURE - SECURITY
// =============================================
require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/RateLimiter.php';
require_once __DIR__ . '/../Infrastructure/Security/UsuarioHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/ValidationHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/HistorialHelper.php';

// =============================================
// APPLICATION - COMMANDS
// =============================================
// Usuario Commands
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioAdminCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioAdminHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarPerfilHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioAsignadoCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioAsignadoHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RecuperarContrasenaCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RecuperarContrasenaHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/CambiarEstadoUsuarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/CambiarEstadoUsuarioHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/EliminarUsuarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/EliminarUsuarioHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/LoginCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/LoginHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/LogoutCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/LogoutHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarActividadCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarActividadHandler.php';

// Comercio Commands
require_once __DIR__ . '/../Application/Commands/comercio/RegistrarComercioCommand.php';
require_once __DIR__ . '/../Application/Commands/comercio/RegistrarComercioHandler.php';

// =============================================
// APPLICATION - QUERIES
// =============================================
// Usuario Queries
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuarioPorIdQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuarioPorIdHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTodosUsuariosHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTecnicosPorEspecialidadHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuariosPorTipoQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuariosPorTipoHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/BuscarPorEmailQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/BuscarPorEmailHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerHistorialActividadesQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerHistorialActividadesHandler.php';

// Comercio Queries
require_once __DIR__ . '/../Application/Queries/comercio/ObtenerComerciosQuery.php';
require_once __DIR__ . '/../Application/Queries/comercio/ObtenerComerciosHandler.php';

// =============================================
// INTERFACES - CONTROLLERS
// =============================================
require_once __DIR__ . '/../interfaces/Http/Controllers/UsuarioController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/AdministradorController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/ComercioController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/MaquinaController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/ComponenteController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/HistorialMaquinaController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/DistribucionController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/InformeController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/NotificacionController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/ReporteController.php';
require_once __DIR__ . '/../interfaces/Http/Controllers/ComentarioController.php';

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
// Controllers específicos por rol
Dependencies::register(TecnicoEnsambladorController::class, function() {
    return new TecnicoEnsambladorController(
        Dependencies::get(ObtenerMaquinasPorTecnicoEnsambladorHandler::class),
        Dependencies::get(RegistrarMontajeHandler::class),
        Dependencies::get(GenerarPlacaHandler::class)
    );
});

Dependencies::register(TecnicoComprobadorController::class, function() {
    return new TecnicoComprobadorController(
        Dependencies::get(ObtenerMaquinasPorTecnicoComprobadorHandler::class),
        Dependencies::get(MandarADistribucionHandler::class),
        Dependencies::get(MandarAReensamblarHandler::class)
    );
});

Dependencies::register(TecnicoMantenimientoController::class, function() {
    return new TecnicoMantenimientoController(
        Dependencies::get(ObtenerMaquinasPorTecnicoMantenimientoHandler::class),
        Dependencies::get(FinalizarMantenimientoHandler::class)
    );
});

Dependencies::register(LogisticoController::class, function() {
    return new LogisticoController(
        Dependencies::get(ObtenerMaquinasParaDistribucionHandler::class),
        Dependencies::get(ObtenerInformesDistribucionHandler::class),
        Dependencies::get(DarMantenimientoHandler::class)
    );
});

Dependencies::register(ContabilidadController::class, function() {
    return new ContabilidadController(
        Dependencies::get(RegistrarRecaudacionHandler::class),
        Dependencies::get(GuardarInformeHandler::class),
        Dependencies::get(ObtenerRecaudacionesHandler::class),
        Dependencies::get(ObtenerResumenRecaudacionesHandler::class),
        Dependencies::get(ObtenerRecaudacionPorIdHandler::class),
        Dependencies::get(ObtenerMaquinasRecaudacionHandler::class),
        Dependencies::get(ObtenerMaquinasOperativasPorComercioHandler::class)
    );
});