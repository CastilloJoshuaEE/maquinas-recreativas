<?php
/**
 * RecreaSys - Dependencies Container
 * 
 * Configuración del contenedor de dependencias.
 * 
 * @package RecreaSys\Config
 * @author Tu Equipo
 * @version 1.0
 */

use RecreaSys\Infrastructure\Persistence\Repository\MySQLUsuarioRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLAdministradorRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLComercioRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLMaquinaRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLComponenteRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLMontajeRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLHistorialRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLDistribucionRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLRecaudacionRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLNotificacionRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLReporteRepository;
use RecreaSys\Infrastructure\Persistence\Repository\MySQLComentarioRepository;

use RecreaSys\Infrastructure\Security\BcryptPasswordHasher;
use RecreaSys\Infrastructure\Security\RateLimiter;
use RecreaSys\Infrastructure\Security\HistorialHelper;

use RecreaSys\Application\Command\Usuario\RegistrarUsuarioHandler;
use RecreaSys\Application\Command\Usuario\LoginHandler;
use RecreaSys\Application\Command\Usuario\LogoutHandler;
use RecreaSys\Application\Command\Administrador\RegistrarUsuarioAdminHandler;
use RecreaSys\Application\Command\Administrador\ActualizarUsuarioHandler;
use RecreaSys\Application\Command\Administrador\EliminarUsuarioHandler;

use RecreaSys\Application\Query\Usuario\ObtenerUsuarioPorIdHandler;
use RecreaSys\Application\Query\Usuario\ObtenerTodosUsuariosHandler;
use RecreaSys\Application\Query\Usuario\ObtenerTecnicosPorEspecialidadHandler;

use RecreaSys\Interfaces\Http\Controller\UsuarioController;
use RecreaSys\Interfaces\Http\Controller\AdministradorController;
use RecreaSys\Interfaces\Http\Controller\ComercioController;
use RecreaSys\Interfaces\Http\Controller\MaquinaController;
use RecreaSys\Interfaces\Http\Controller\ComponenteController;
use RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController;
use RecreaSys\Interfaces\Http\Controller\DistribucionController;
use RecreaSys\Interfaces\Http\Controller\InformeController;
use RecreaSys\Interfaces\Http\Controller\NotificacionController;
use RecreaSys\Interfaces\Http\Controller\ReporteController;
use RecreaSys\Interfaces\Http\Controller\ComentarioController;
use RecreaSys\Interfaces\Http\Controller\HealthController;

// Obtener conexión a la base de datos
require_once __DIR__ . '/database.php';
$database = new Database();
$connection = $database->getConnection();

// =============================================
// REPOSITORIOS
// =============================================
$usuarioRepository = new MySQLUsuarioRepository($connection);
$administradorRepository = new MySQLAdministradorRepository($connection);
$comercioRepository = new MySQLComercioRepository($connection);
$maquinaRepository = new MySQLMaquinaRepository($connection);
$componenteRepository = new MySQLComponenteRepository($connection);
$montajeRepository = new MySQLMontajeRepository($connection);
$historialRepository = new MySQLHistorialRepository($connection);
$distribucionRepository = new MySQLDistribucionRepository($connection);
$recaudacionRepository = new MySQLRecaudacionRepository($connection);
$notificacionRepository = new MySQLNotificacionRepository($connection);
$reporteRepository = new MySQLReporteRepository($connection);
$comentarioRepository = new MySQLComentarioRepository($connection);

// =============================================
// SERVICIOS DE INFRAESTRUCTURA
// =============================================
$passwordHasher = new BcryptPasswordHasher(12);
$rateLimiter = RateLimiter::getInstance();
$historialHelper = HistorialHelper::getInstance();

// =============================================
// HANDLERS DE APLICACIÓN - COMMANDS
// =============================================
$registrarUsuarioHandler = new RegistrarUsuarioHandler($usuarioRepository, $passwordHasher);
$loginHandler = new LoginHandler($usuarioRepository, $passwordHasher);
$logoutHandler = new LogoutHandler($usuarioRepository);
$registrarUsuarioAdminHandler = new RegistrarUsuarioAdminHandler($administradorRepository, $passwordHasher);
$actualizarUsuarioHandler = new ActualizarUsuarioHandler($administradorRepository, $passwordHasher);
$eliminarUsuarioHandler = new EliminarUsuarioHandler($administradorRepository);

// =============================================
// HANDLERS DE APLICACIÓN - QUERIES
// =============================================
$obtenerUsuarioPorIdHandler = new ObtenerUsuarioPorIdHandler($usuarioRepository);
$obtenerTodosUsuariosHandler = new ObtenerTodosUsuariosHandler($usuarioRepository);
$obtenerTecnicosPorEspecialidadHandler = new ObtenerTecnicosPorEspecialidadHandler($usuarioRepository);

// =============================================
// CONTROLADORES
// =============================================
$usuarioController = new UsuarioController(
    $registrarUsuarioHandler,
    $loginHandler,
    $logoutHandler,
    $obtenerUsuarioPorIdHandler,
    $obtenerTecnicosPorEspecialidadHandler,
    $usuarioRepository
);

$administradorController = new AdministradorController(
    $obtenerTodosUsuariosHandler,
    $obtenerUsuarioPorIdHandler,
    $registrarUsuarioAdminHandler,
    $actualizarUsuarioHandler,
    $eliminarUsuarioHandler,
    $historialHelper
);

$comercioController = new ComercioController($comercioRepository);
$maquinaController = new MaquinaController($maquinaRepository, $historialHelper);
$componenteController = new ComponenteController($componenteRepository);
$historialMaquinaController = new HistorialMaquinaController($historialRepository);
$distribucionController = new DistribucionController($distribucionRepository);
$informeController = new InformeController($recaudacionRepository);
$notificacionController = new NotificacionController($notificacionRepository);
$reporteController = new ReporteController($reporteRepository, $comentarioRepository);
$comentarioController = new ComentarioController($comentarioRepository, $reporteRepository, $notificacionRepository);
$healthController = new HealthController($connection);

// =============================================
// CONTENEDOR GLOBAL
// =============================================
$container = [
    // Controladores
    \RecreaSys\Interfaces\Http\Controller\UsuarioController::class => $usuarioController,
    \RecreaSys\Interfaces\Http\Controller\AdministradorController::class => $administradorController,
    \RecreaSys\Interfaces\Http\Controller\ComercioController::class => $comercioController,
    \RecreaSys\Interfaces\Http\Controller\MaquinaController::class => $maquinaController,
    \RecreaSys\Interfaces\Http\Controller\ComponenteController::class => $componenteController,
    \RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController::class => $historialMaquinaController,
    \RecreaSys\Interfaces\Http\Controller\DistribucionController::class => $distribucionController,
    \RecreaSys\Interfaces\Http\Controller\InformeController::class => $informeController,
    \RecreaSys\Interfaces\Http\Controller\NotificacionController::class => $notificacionController,
    \RecreaSys\Interfaces\Http\Controller\ReporteController::class => $reporteController,
    \RecreaSys\Interfaces\Http\Controller\ComentarioController::class => $comentarioController,
    \RecreaSys\Interfaces\Http\Controller\HealthController::class => $healthController,
    
    // Middlewares
    \RecreaSys\Middleware\AuthMiddleware::class => new \RecreaSys\Middleware\AuthMiddleware(),
    \RecreaSys\Middleware\CorsMiddleware::class => new \RecreaSys\Middleware\CorsMiddleware(),
    \RecreaSys\Middleware\RateLimitMiddleware::class => new \RecreaSys\Middleware\RateLimitMiddleware(),
    \RecreaSys\Middleware\JsonResponseMiddleware::class => new \RecreaSys\Middleware\JsonResponseMiddleware(),
    
    // Repositorios
    \RecreaSys\Domain\Usuario\UsuarioRepository::class => $usuarioRepository,
    \RecreaSys\Domain\Comercio\ComercioRepository::class => $comercioRepository,
    \RecreaSys\Domain\Maquina\MaquinaRepository::class => $maquinaRepository,
    
    // Servicios
    \RecreaSys\Infrastructure\Security\PasswordHasher::class => $passwordHasher,
    \RecreaSys\Infrastructure\Security\RateLimiter::class => $rateLimiter,
    \RecreaSys\Infrastructure\Security\HistorialHelper::class => $historialHelper
];