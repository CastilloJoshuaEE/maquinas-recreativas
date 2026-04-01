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
require_once __DIR__ . '/constants.php';

// =============================================
// APPLICATION - BASE INTERFACES
// =============================================
require_once __DIR__ . '/../Application/Commands/Command.php';
require_once __DIR__ . '/../Application/Commands/CommandHandler.php';
require_once __DIR__ . '/../Application/Queries/Query.php';
require_once __DIR__ . '/../Application/Queries/QueryHandler.php';

// =============================================
// HELPERS Y UTILIDADES
// =============================================
require_once __DIR__ . '/../Infrastructure/Security/CifradoHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/RateLimiter.php';
require_once __DIR__ . '/../Infrastructure/Security/UsuarioHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/ValidationHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/HistorialHelper.php';
require_once __DIR__ . '/../Infrastructure/Security/PasswordHasher.php';
require_once __DIR__ . '/../Infrastructure/Security/BcryptPasswordHasher.php';

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

require_once __DIR__ . '/../Domain/Comercio/Comercio.php';

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

require_once __DIR__ . '/../Domain/Comentario/Comentario.php';

// =============================================
// Domain - REPOSITORY INTERFACES
// =============================================
require_once __DIR__ . '/../Domain/Usuario/UsuarioRepository.php';
require_once __DIR__ . '/../Domain/Usuario/AdministradorRepository.php';
require_once __DIR__ . '/../Domain/Usuario/TecnicoRepository.php';
require_once __DIR__ . '/../Domain/Comercio/ComercioRepository.php';
require_once __DIR__ . '/../Domain/Maquina/MaquinaRepository.php';
require_once __DIR__ . '/../Domain/Componente/ComponenteRepository.php';
require_once __DIR__ . '/../Domain/Montaje/MontajeRepository.php';
require_once __DIR__ . '/../Domain/Historial/HistorialRepository.php';
require_once __DIR__ . '/../Domain/Distribucion/DistribucionRepository.php';
require_once __DIR__ . '/../Domain/Recaudacion/RecaudacionRepository.php';
require_once __DIR__ . '/../Domain/Notificacion/NotificacionRepository.php';
require_once __DIR__ . '/../Domain/Reporte/ReporteRepository.php';
require_once __DIR__ . '/../Domain/Comentario/ComentarioRepository.php';

// =============================================
// INFRASTRUCTURE - DATABASE
// =============================================
require_once __DIR__ . '/../Infrastructure/Database/Database.php';
require_once __DIR__ . '/../Infrastructure/Database/Inserter.php';

// =============================================
// INFRASTRUCTURE - REPOSITORY IMPLEMENTATIONS
// =============================================
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
require_once __DIR__ . '/../Infrastructure/Security/PasswordHasher.php';
require_once __DIR__ . '/../Infrastructure/Security/BcryptPasswordHasher.php';

// =============================================
// APPLICATION - COMMANDS (Usuario)
// =============================================
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioAdminCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/RegistrarUsuarioAdminHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarPerfilCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarPerfilHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioAsignadoCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioAsignadoHandler.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Usuario/ActualizarUsuarioHandler.php';
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

// =============================================
// APPLICATION - COMMANDS (Comercio)
// =============================================
require_once __DIR__ . '/../Application/Commands/Comercio/RegistrarComercioCommand.php';
require_once __DIR__ . '/../Application/Commands/Comercio/RegistrarComercioHandler.php';

// =============================================
// APPLICATION - COMMANDS (Componente)
// =============================================
require_once __DIR__ . '/../Application/Commands/Componente/AsignarCarcasaCommand.php';
require_once __DIR__ . '/../Application/Commands/Componente/AsignarCarcasaHandler.php';
require_once __DIR__ . '/../Application/Commands/Componente/LiberarComponenteCommand.php';
require_once __DIR__ . '/../Application/Commands/Componente/LiberarComponenteHandler.php';
require_once __DIR__ . '/../Application/Commands/Componente/LiberarComponentesCancelacionCommand.php';
require_once __DIR__ . '/../Application/Commands/Componente/LiberarComponentesCancelacionHandler.php';
require_once __DIR__ . '/../Application/Commands/Componente/UsarComponenteCommand.php';
require_once __DIR__ . '/../Application/Commands/Componente/UsarComponenteHandler.php';

// =============================================
// APPLICATION - COMMANDS (Maquina)
// =============================================
require_once __DIR__ . '/../Application/Commands/Maquina/DarMantenimientoCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/DarMantenimientoHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/FinalizarMantenimientoCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/FinalizarMantenimientoHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/GenerarPlacaCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/GenerarPlacaHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarAComprobacionCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarAComprobacionHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarADistribucionCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarADistribucionHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarAReensamblarCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/MandarAReensamblarHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/PonerOperativaCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/PonerOperativaHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/RegistrarMaquinaCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/RegistrarMaquinaHandler.php';
require_once __DIR__ . '/../Application/Commands/Maquina/RegistrarMontajeCommand.php';
require_once __DIR__ . '/../Application/Commands/Maquina/RegistrarMontajeHandler.php';

// =============================================
// APPLICATION - COMMANDS (Notificacion)
// =============================================
require_once __DIR__ . '/../Application/Commands/Notificacion/CrearNotificacionMaquinaCommand.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/CrearNotificacionMaquinaHandler.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/CrearNotificacionReporteCommand.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/CrearNotificacionReporteHandler.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/MarcarComoLeidaCommand.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/MarcarComoLeidaHandler.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/MarcarTodasComoLeidasCommand.php';
require_once __DIR__ . '/../Application/Commands/Notificacion/MarcarTodasComoLeidasHandler.php';

// =============================================
// APPLICATION - COMMANDS (Recaudacion)
// =============================================
require_once __DIR__ . '/../Application/Commands/Recaudacion/ActualizarRecaudacionCommand.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/ActualizarRecaudacionHandler.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/EliminarRecaudacionCommand.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/EliminarRecaudacionHandler.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/GuardarInformeCommand.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/GuardarInformeHandler.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/RegistrarRecaudacionCommand.php';
require_once __DIR__ . '/../Application/Commands/Recaudacion/RegistrarRecaudacionHandler.php';

// =============================================
// APPLICATION - COMMANDS (Reporte)
// =============================================
require_once __DIR__ . '/../Application/Commands/Reporte/ActualizarEstadoReporteCommand.php';
require_once __DIR__ . '/../Application/Commands/Reporte/ActualizarEstadoReporteHandler.php';
require_once __DIR__ . '/../Application/Commands/Reporte/CrearReporteCommand.php';
require_once __DIR__ . '/../Application/Commands/Reporte/CrearReporteHandler.php';

// =============================================
// APPLICATION - COMMANDS (Comentario)
// =============================================
require_once __DIR__ . '/../Application/Commands/Comentario/CrearComentarioCommand.php';
require_once __DIR__ . '/../Application/Commands/Comentario/CrearComentarioHandler.php';

// =============================================
// APPLICATION - QUERIES (Usuario)
// =============================================
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuarioPorIdQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuarioPorIdHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTodosUsuariosQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTodosUsuariosHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTecnicosPorEspecialidadQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerTecnicosPorEspecialidadHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuariosPorTipoQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerUsuariosPorTipoHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/BuscarPorEmailQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/BuscarPorEmailHandler.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerHistorialActividadesQuery.php';
require_once __DIR__ . '/../Application/Queries/Usuario/ObtenerHistorialActividadesHandler.php';

// =============================================
// APPLICATION - QUERIES (Comercio)
// =============================================
require_once __DIR__ . '/../Application/Queries/Comercio/ObtenerComerciosQuery.php';
require_once __DIR__ . '/../Application/Queries/Comercio/ObtenerComerciosHandler.php';

// =============================================
// APPLICATION - QUERIES (Componente)
// =============================================
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesQuery.php';
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesHandler.php';
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesDisponiblesQuery.php';
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesDisponiblesHandler.php';
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesEnUsoQuery.php';
require_once __DIR__ . '/../Application/Queries/Componente/ObtenerComponentesEnUsoHandler.php';

// =============================================
// APPLICATION - QUERIES (Distribucion)
// =============================================
require_once __DIR__ . '/../Application/Queries/Distribucion/ObtenerInformesDistribucionQuery.php';
require_once __DIR__ . '/../Application/Queries/Distribucion/ObtenerInformesDistribucionHandler.php';

// =============================================
// APPLICATION - QUERIES (Historial)
// =============================================
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialGeneralQuery.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialGeneralHandler.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialPorMaquinaQuery.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialPorMaquinaHandler.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialPorUsuarioQuery.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerHistorialPorUsuarioHandler.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerResumenRecienteQuery.php';
require_once __DIR__ . '/../Application/Queries/Historial/ObtenerResumenRecienteHandler.php';

// =============================================
// APPLICATION - QUERIES (Maquina)
// =============================================
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerComponentesMaquinaQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerComponentesMaquinaHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasParaDistribucionQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasParaDistribucionHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorEstadoQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorEstadoHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorEtapaQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorEtapaHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoComprobadorQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoComprobadorHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoEnsambladorQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoEnsambladorHandler.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoMantenimientoQuery.php';
require_once __DIR__ . '/../Application/Queries/Maquina/ObtenerMaquinasPorTecnicoMantenimientoHandler.php';

// =============================================
// APPLICATION - QUERIES (Notificacion)
// =============================================
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerCantidadNoLeidasQuery.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerCantidadNoLeidasHandler.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNoLeidasQuery.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNoLeidasHandler.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNotificacionesMaquinaQuery.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNotificacionesMaquinaHandler.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNotificacionesReporteQuery.php';
require_once __DIR__ . '/../Application/Queries/Notificacion/ObtenerNotificacionesReporteHandler.php';

// =============================================
// APPLICATION - QUERIES (Recaudacion)
// =============================================
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerComercioRecaudacionQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerComercioRecaudacionHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerInformePorRecaudacionQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerInformePorRecaudacionHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerMaquinasOperativasPorComercioQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerMaquinasOperativasPorComercioHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerMaquinasRecaudacionQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerMaquinasRecaudacionHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerRecaudacionesQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerRecaudacionesHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerRecaudacionPorIdQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerRecaudacionPorIdHandler.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerResumenRecaudacionesQuery.php';
require_once __DIR__ . '/../Application/Queries/Recaudacion/ObtenerResumenRecaudacionesHandler.php';

// =============================================
// APPLICATION - QUERIES (Reporte)
// =============================================
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerChatCompletoQuery.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerChatCompletoHandler.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerChatQuery.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerChatHandler.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerReportePorIdQuery.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerReportePorIdHandler.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerReportesPorUsuarioQuery.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerReportesPorUsuarioHandler.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerUsuariosChatQuery.php';
require_once __DIR__ . '/../Application/Queries/Reporte/ObtenerUsuariosChatHandler.php';

// =============================================
// APPLICATION - QUERIES (Comentario)
// =============================================
require_once __DIR__ . '/../Application/Queries/Comentario/ObtenerComentariosPorReporteQuery.php';
require_once __DIR__ . '/../Application/Queries/Comentario/ObtenerComentariosPorReporteHandler.php';

// =============================================
// USE STATEMENTS PARA CLASES COMUNES
// =============================================
use maquinas_recreativas\Infrastructure\Repositories\MySQLUsuarioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComercioRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMaquinaRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComponenteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLHistorialRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLMontajeRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLDistribucionRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLRecaudacionRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLNotificacionRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLReporteRepository;
use maquinas_recreativas\Infrastructure\Repositories\MySQLComentarioRepository;
use maquinas_recreativas\Infrastructure\Security\BcryptPasswordHasher;
use maquinas_recreativas\Infrastructure\Security\HistorialHelper;

use maquinas_recreativas\Application\Commands\Usuario\LoginHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\EliminarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarPerfilHandler;
use maquinas_recreativas\Application\Commands\Usuario\RecuperarContrasenaHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarUsuarioAsignadoHandler;
use maquinas_recreativas\Application\Commands\Usuario\LogoutHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarActividadHandler;

use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuariosPorTipoHandler;
use maquinas_recreativas\Application\Queries\Usuario\BuscarPorEmailHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerHistorialActividadesHandler;

use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Queries\Comercio\ObtenerComerciosHandler;

use maquinas_recreativas\Application\Commands\Componente\UsarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\AsignarCarcasaHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponentesCancelacionHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesDisponiblesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesEnUsoHandler;

use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAComprobacionHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionHandler;
use maquinas_recreativas\Application\Commands\Maquina\PonerOperativaHandler;
use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoHandler;
use maquinas_recreativas\Application\Commands\Maquina\FinalizarMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerComponentesMaquinaHandler;

use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaHandler;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionReporteHandler;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarComoLeidaHandler;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarTodasComoLeidasHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesMaquinaHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesReporteHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerCantidadNoLeidasHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNoLeidasHandler;

use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\ActualizarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\EliminarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\GuardarInformeHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerResumenRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionPorIdHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasOperativasPorComercioHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerComercioRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerInformePorRecaudacionHandler;

use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Reporte\ActualizarEstadoReporteHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportesPorUsuarioHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerUsuariosChatHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatCompletoHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportePorIdHandler;

use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteHandler;

use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorMaquinaHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorUsuarioHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialGeneralHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerResumenRecienteHandler;

use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionHandler;

use maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController;
use maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController;
use maquinas_recreativas\Interfaces\Http\Controllers\ComercioController;
use maquinas_recreativas\Interfaces\Http\Controllers\MaquinaController;
use maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController;
use maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController;
use maquinas_recreativas\Interfaces\Http\Controllers\ReporteController;
use maquinas_recreativas\Interfaces\Http\Controllers\ComentarioController;
use maquinas_recreativas\Interfaces\Http\Controllers\InformeController;
use maquinas_recreativas\Interfaces\Http\Controllers\HistorialMaquinaController;
use maquinas_recreativas\Interfaces\Http\Controllers\DistribucionController;
use maquinas_recreativas\Interfaces\Http\Controllers\TecnicoEnsambladorController;
use maquinas_recreativas\Interfaces\Http\Controllers\TecnicoComprobadorController;
use maquinas_recreativas\Interfaces\Http\Controllers\TecnicoMantenimientoController;
use maquinas_recreativas\Interfaces\Http\Controllers\LogisticoController;

/**
 * Clase contenedor de dependencias (Service Container)
 */
class Dependencies {
    
    private static $instances = [];
    private static $factories = [];
    
    private function __construct() {}
    
    public static function register($class, callable $factory) {
        self::$factories[$class] = $factory;
    }
    
    public static function get($class) {
        if (!isset(self::$instances[$class])) {
            if (!isset(self::$factories[$class])) {
                throw new Exception("No factory registered for class: {$class}");
            }
            self::$instances[$class] = self::$factories[$class]();
        }
        return self::$instances[$class];
    }
    
    public static function reset() {
        self::$instances = [];
    }
}

// =============================================
// CREAR INSTANCIA DE DATABASE
// =============================================
$database = new \maquinas_recreativas\Infrastructure\Database\Database();

// =============================================
// REGISTRO DE FÁBRICAS POR DEFECTO
// =============================================

// Database
Dependencies::register(\maquinas_recreativas\Infrastructure\Database\Database::class, function() use ($database) {
    return $database;
});

// Password Hasher
Dependencies::register(BcryptPasswordHasher::class, function() {
    return new BcryptPasswordHasher();
});

// HistorialHelper
Dependencies::register(HistorialHelper::class, function() {
    return HistorialHelper::getInstance();
});

// Repositories
Dependencies::register(MySQLUsuarioRepository::class, function() use ($database) {
    return new MySQLUsuarioRepository($database);
});

Dependencies::register(MySQLComercioRepository::class, function() use ($database) {
    return new MySQLComercioRepository($database);
});

Dependencies::register(MySQLMaquinaRepository::class, function() use ($database) {
    return new MySQLMaquinaRepository($database);
});

Dependencies::register(MySQLComponenteRepository::class, function() use ($database) {
    return new MySQLComponenteRepository($database);
});

Dependencies::register(MySQLHistorialRepository::class, function() use ($database) {
    return new MySQLHistorialRepository($database);
});

Dependencies::register(MySQLMontajeRepository::class, function() use ($database) {
    return new MySQLMontajeRepository($database);
});

Dependencies::register(MySQLDistribucionRepository::class, function() use ($database) {
    return new MySQLDistribucionRepository($database);
});

Dependencies::register(MySQLRecaudacionRepository::class, function() use ($database) {
    return new MySQLRecaudacionRepository($database);
});

Dependencies::register(MySQLNotificacionRepository::class, function() use ($database) {
    return new MySQLNotificacionRepository($database);
});

Dependencies::register(MySQLReporteRepository::class, function() use ($database) {
    return new MySQLReporteRepository($database);
});

Dependencies::register(MySQLComentarioRepository::class, function() use ($database) {
    return new MySQLComentarioRepository($database);
});

// =============================================
// REGISTRO DE HANDLERS Y CONTROLLERS
// =============================================

// Handlers de Usuario
Dependencies::register(LoginHandler::class, function() {
    return new LoginHandler(Dependencies::get(MySQLUsuarioRepository::class));
});
Dependencies::register(RegistrarUsuarioHandler::class, function() {
    return new RegistrarUsuarioHandler(Dependencies::get(MySQLUsuarioRepository::class), Dependencies::get(BcryptPasswordHasher::class));
});

Dependencies::register(RegistrarUsuarioAdminHandler::class, function() {
    return new RegistrarUsuarioAdminHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ActualizarUsuarioHandler::class, function() {
    return new ActualizarUsuarioHandler(Dependencies::get(MySQLUsuarioRepository::class), Dependencies::get(BcryptPasswordHasher::class));
});

Dependencies::register(CambiarEstadoUsuarioHandler::class, function() {
    return new CambiarEstadoUsuarioHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(EliminarUsuarioHandler::class, function() {
    return new EliminarUsuarioHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ActualizarPerfilHandler::class, function() {
    return new ActualizarPerfilHandler(Dependencies::get(MySQLUsuarioRepository::class), Dependencies::get(BcryptPasswordHasher::class));
});

Dependencies::register(RecuperarContrasenaHandler::class, function() {
    return new RecuperarContrasenaHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ActualizarUsuarioAsignadoHandler::class, function() {
    return new ActualizarUsuarioAsignadoHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(LogoutHandler::class, function() {
    return new LogoutHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(RegistrarActividadHandler::class, function() {
    return new RegistrarActividadHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

// Queries de Usuario
Dependencies::register(ObtenerUsuarioPorIdHandler::class, function() {
    return new ObtenerUsuarioPorIdHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerTodosUsuariosHandler::class, function() {
    return new ObtenerTodosUsuariosHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerTecnicosPorEspecialidadHandler::class, function() {
    return new ObtenerTecnicosPorEspecialidadHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerUsuariosPorTipoHandler::class, function() {
    return new ObtenerUsuariosPorTipoHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(BuscarPorEmailHandler::class, function() {
    return new BuscarPorEmailHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

Dependencies::register(ObtenerHistorialActividadesHandler::class, function() {
    return new ObtenerHistorialActividadesHandler(Dependencies::get(MySQLUsuarioRepository::class));
});

// Handlers de Comercio
Dependencies::register(RegistrarComercioHandler::class, function() {
    return new RegistrarComercioHandler(Dependencies::get(MySQLComercioRepository::class), Dependencies::get(HistorialHelper::class));
});

Dependencies::register(ObtenerComerciosHandler::class, function() {
    return new ObtenerComerciosHandler(Dependencies::get(MySQLComercioRepository::class));
});

// Handlers de Componente
Dependencies::register(UsarComponenteHandler::class, function() {
    return new UsarComponenteHandler(
        Dependencies::get(MySQLComponenteRepository::class),
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLMontajeRepository::class)
    );
});

Dependencies::register(LiberarComponenteHandler::class, function() {
    return new LiberarComponenteHandler(Dependencies::get(MySQLComponenteRepository::class));
});

Dependencies::register(AsignarCarcasaHandler::class, function() {
    return new AsignarCarcasaHandler(Dependencies::get(MySQLComponenteRepository::class));
});

Dependencies::register(LiberarComponentesCancelacionHandler::class, function() {
    return new LiberarComponentesCancelacionHandler(Dependencies::get(MySQLComponenteRepository::class));
});

Dependencies::register(ObtenerComponentesHandler::class, function() {
    return new ObtenerComponentesHandler(Dependencies::get(MySQLComponenteRepository::class));
});

Dependencies::register(ObtenerComponentesDisponiblesHandler::class, function() {
    return new ObtenerComponentesDisponiblesHandler(Dependencies::get(MySQLComponenteRepository::class));
});

Dependencies::register(ObtenerComponentesEnUsoHandler::class, function() {
    return new ObtenerComponentesEnUsoHandler(
        Dependencies::get(MySQLComponenteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

// Handlers de Maquina
Dependencies::register(RegistrarMaquinaHandler::class, function() {
    return new RegistrarMaquinaHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLComercioRepository::class),
        Dependencies::get(MySQLComponenteRepository::class)
    );
});

Dependencies::register(GenerarPlacaHandler::class, function() {
    return new GenerarPlacaHandler(
        Dependencies::get(MySQLComponenteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(RegistrarMontajeHandler::class, function() {
    return new RegistrarMontajeHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLComponenteRepository::class),
        Dependencies::get(MySQLMontajeRepository::class),
        Dependencies::get(MySQLHistorialRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(MandarAComprobacionHandler::class, function() {
    return new MandarAComprobacionHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

Dependencies::register(MandarAReensamblarHandler::class, function() {
    return new MandarAReensamblarHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

Dependencies::register(MandarADistribucionHandler::class, function() {
    return new MandarADistribucionHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLComercioRepository::class),
        Dependencies::get(MySQLDistribucionRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

Dependencies::register(PonerOperativaHandler::class, function() {
    return new PonerOperativaHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLDistribucionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

Dependencies::register(DarMantenimientoHandler::class, function() {
    return new DarMantenimientoHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLComercioRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLDistribucionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

Dependencies::register(FinalizarMantenimientoHandler::class, function() {
    return new FinalizarMantenimientoHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLHistorialRepository::class)
    );
});

// Queries de Maquina
Dependencies::register(ObtenerMaquinasPorTecnicoEnsambladorHandler::class, function() {
    return new ObtenerMaquinasPorTecnicoEnsambladorHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerMaquinasPorTecnicoComprobadorHandler::class, function() {
    return new ObtenerMaquinasPorTecnicoComprobadorHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerMaquinasPorTecnicoMantenimientoHandler::class, function() {
    return new ObtenerMaquinasPorTecnicoMantenimientoHandler(
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerMaquinasPorEstadoHandler::class, function() {
    return new ObtenerMaquinasPorEstadoHandler(Dependencies::get(MySQLMaquinaRepository::class));
});

Dependencies::register(ObtenerMaquinasPorEtapaHandler::class, function() {
    return new ObtenerMaquinasPorEtapaHandler(Dependencies::get(MySQLMaquinaRepository::class));
});

Dependencies::register(ObtenerMaquinasParaDistribucionHandler::class, function() {
    return new ObtenerMaquinasParaDistribucionHandler(Dependencies::get(MySQLMaquinaRepository::class));
});

Dependencies::register(ObtenerComponentesMaquinaHandler::class, function() {
    return new ObtenerComponentesMaquinaHandler(Dependencies::get(MySQLMaquinaRepository::class));
});

// Handlers de Notificacion
Dependencies::register(CrearNotificacionMaquinaHandler::class, function() {
    return new CrearNotificacionMaquinaHandler(
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class),
        Dependencies::get(MySQLMaquinaRepository::class)
    );
});

Dependencies::register(CrearNotificacionReporteHandler::class, function() {
    return new CrearNotificacionReporteHandler(
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(MarcarComoLeidaHandler::class, function() {
    return new MarcarComoLeidaHandler(Dependencies::get(MySQLNotificacionRepository::class));
});

Dependencies::register(MarcarTodasComoLeidasHandler::class, function() {
    return new MarcarTodasComoLeidasHandler(Dependencies::get(MySQLNotificacionRepository::class));
});

// Queries de Notificacion
Dependencies::register(ObtenerNotificacionesMaquinaHandler::class, function() {
    return new ObtenerNotificacionesMaquinaHandler(
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerNotificacionesReporteHandler::class, function() {
    return new ObtenerNotificacionesReporteHandler(
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerCantidadNoLeidasHandler::class, function() {
    return new ObtenerCantidadNoLeidasHandler(Dependencies::get(MySQLNotificacionRepository::class));
});

Dependencies::register(ObtenerNoLeidasHandler::class, function() {
    return new ObtenerNoLeidasHandler(Dependencies::get(MySQLNotificacionRepository::class));
});

// Handlers de Recaudacion
Dependencies::register(RegistrarRecaudacionHandler::class, function() {
    return new RegistrarRecaudacionHandler(
        Dependencies::get(MySQLRecaudacionRepository::class),
        Dependencies::get(MySQLMaquinaRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ActualizarRecaudacionHandler::class, function() {
    return new ActualizarRecaudacionHandler(
        Dependencies::get(MySQLRecaudacionRepository::class),
        Dependencies::get(MySQLMaquinaRepository::class)
    );
});

Dependencies::register(EliminarRecaudacionHandler::class, function() {
    return new EliminarRecaudacionHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

Dependencies::register(GuardarInformeHandler::class, function() {
    return new GuardarInformeHandler(
        Dependencies::get(MySQLRecaudacionRepository::class),
        Dependencies::get(MySQLComponenteRepository::class)
    );
});

// Queries de Recaudacion
Dependencies::register(ObtenerRecaudacionesHandler::class, function() {
    return new ObtenerRecaudacionesHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

Dependencies::register(ObtenerResumenRecaudacionesHandler::class, function() {
    return new ObtenerResumenRecaudacionesHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

Dependencies::register(ObtenerRecaudacionPorIdHandler::class, function() {
    return new ObtenerRecaudacionPorIdHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

Dependencies::register(ObtenerMaquinasRecaudacionHandler::class, function() {
    return new ObtenerMaquinasRecaudacionHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

Dependencies::register(ObtenerMaquinasOperativasPorComercioHandler::class, function() {
    return new ObtenerMaquinasOperativasPorComercioHandler(
        Dependencies::get(MySQLRecaudacionRepository::class),
        Dependencies::get(MySQLComercioRepository::class)
    );
});

Dependencies::register(ObtenerComercioRecaudacionHandler::class, function() {
    return new ObtenerComercioRecaudacionHandler(Dependencies::get(MySQLComercioRepository::class));
});

Dependencies::register(ObtenerInformePorRecaudacionHandler::class, function() {
    return new ObtenerInformePorRecaudacionHandler(Dependencies::get(MySQLRecaudacionRepository::class));
});

// Handlers de Reporte
Dependencies::register(CrearReporteHandler::class, function() {
    return new CrearReporteHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ActualizarEstadoReporteHandler::class, function() {
    return new ActualizarEstadoReporteHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class)
    );
});

// Queries de Reporte
Dependencies::register(ObtenerReportesPorUsuarioHandler::class, function() {
    return new ObtenerReportesPorUsuarioHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerChatHandler::class, function() {
    return new ObtenerChatHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerUsuariosChatHandler::class, function() {
    return new ObtenerUsuariosChatHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerChatCompletoHandler::class, function() {
    return new ObtenerChatCompletoHandler(
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLComentarioRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerReportePorIdHandler::class, function() {
    return new ObtenerReportePorIdHandler(Dependencies::get(MySQLReporteRepository::class));
});

// Handlers de Comentario
Dependencies::register(CrearComentarioHandler::class, function() {
    return new CrearComentarioHandler(
        Dependencies::get(MySQLComentarioRepository::class),
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLNotificacionRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerComentariosPorReporteHandler::class, function() {
    return new ObtenerComentariosPorReporteHandler(
        Dependencies::get(MySQLComentarioRepository::class),
        Dependencies::get(MySQLReporteRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

// Handlers de Historial
Dependencies::register(ObtenerHistorialPorMaquinaHandler::class, function() {
    return new ObtenerHistorialPorMaquinaHandler(
        Dependencies::get(MySQLHistorialRepository::class),
        Dependencies::get(MySQLMaquinaRepository::class)
    );
});

Dependencies::register(ObtenerHistorialPorUsuarioHandler::class, function() {
    return new ObtenerHistorialPorUsuarioHandler(
        Dependencies::get(MySQLHistorialRepository::class),
        Dependencies::get(MySQLUsuarioRepository::class)
    );
});

Dependencies::register(ObtenerHistorialGeneralHandler::class, function() {
    return new ObtenerHistorialGeneralHandler(Dependencies::get(MySQLHistorialRepository::class));
});

Dependencies::register(ObtenerResumenRecienteHandler::class, function() {
    return new ObtenerResumenRecienteHandler(Dependencies::get(MySQLHistorialRepository::class));
});

// Handlers de Distribucion
Dependencies::register(ObtenerInformesDistribucionHandler::class, function() {
    return new ObtenerInformesDistribucionHandler(Dependencies::get(MySQLDistribucionRepository::class));
});

// =============================================
// CONTROLLERS
// =============================================
Dependencies::register(UsuarioController::class, function() {
    return new UsuarioController(
        Dependencies::get(LoginHandler::class),
        Dependencies::get(RegistrarUsuarioHandler::class),
        Dependencies::get(ObtenerUsuarioPorIdHandler::class),
        Dependencies::get(LogoutHandler::class),
        Dependencies::get(ActualizarPerfilHandler::class),
        Dependencies::get(RecuperarContrasenaHandler::class),
        Dependencies::get(ObtenerTodosUsuariosHandler::class),
        Dependencies::get(ObtenerTecnicosPorEspecialidadHandler::class),
        Dependencies::get(ObtenerUsuariosPorTipoHandler::class),
        Dependencies::get(BuscarPorEmailHandler::class),
        Dependencies::get(ObtenerHistorialActividadesHandler::class),
        Dependencies::get(RegistrarActividadHandler::class),
        Dependencies::get(ActualizarUsuarioAsignadoHandler::class)
    );
});

Dependencies::register(AdministradorController::class, function() {
    return new AdministradorController(
        Dependencies::get(RegistrarUsuarioAdminHandler::class),
        Dependencies::get(ActualizarUsuarioHandler::class),
        Dependencies::get(CambiarEstadoUsuarioHandler::class),
        Dependencies::get(EliminarUsuarioHandler::class),
        Dependencies::get(ObtenerUsuarioPorIdHandler::class),
        Dependencies::get(ObtenerTodosUsuariosHandler::class),
        Dependencies::get(ObtenerHistorialActividadesHandler::class)
    );
});

Dependencies::register(ComercioController::class, function() {
    return new ComercioController(
        Dependencies::get(ObtenerComerciosHandler::class),
        Dependencies::get(RegistrarComercioHandler::class)
    );
});

Dependencies::register(MaquinaController::class, function() {
    return new MaquinaController(
        Dependencies::get(RegistrarMaquinaHandler::class),
        Dependencies::get(GenerarPlacaHandler::class),
        Dependencies::get(RegistrarMontajeHandler::class),
        Dependencies::get(MandarAComprobacionHandler::class),
        Dependencies::get(MandarAReensamblarHandler::class),
        Dependencies::get(MandarADistribucionHandler::class),
        Dependencies::get(PonerOperativaHandler::class),
        Dependencies::get(DarMantenimientoHandler::class),
        Dependencies::get(FinalizarMantenimientoHandler::class),
        Dependencies::get(ObtenerMaquinasPorTecnicoEnsambladorHandler::class),
        Dependencies::get(ObtenerMaquinasPorTecnicoComprobadorHandler::class),
        Dependencies::get(ObtenerMaquinasPorTecnicoMantenimientoHandler::class),
        Dependencies::get(ObtenerMaquinasPorEstadoHandler::class),
        Dependencies::get(ObtenerMaquinasPorEtapaHandler::class),
        Dependencies::get(ObtenerMaquinasParaDistribucionHandler::class),
        Dependencies::get(ObtenerComponentesMaquinaHandler::class)
    );
});

Dependencies::register(ComponenteController::class, function() {
    return new ComponenteController(
        Dependencies::get(ObtenerComponentesHandler::class),
        Dependencies::get(ObtenerComponentesDisponiblesHandler::class),
        Dependencies::get(UsarComponenteHandler::class),
        Dependencies::get(LiberarComponenteHandler::class),
        Dependencies::get(AsignarCarcasaHandler::class),
        Dependencies::get(LiberarComponentesCancelacionHandler::class),
        Dependencies::get(ObtenerComponentesEnUsoHandler::class)
    );
});

Dependencies::register(NotificacionController::class, function() {
    return new NotificacionController(
        Dependencies::get(ObtenerNotificacionesMaquinaHandler::class),
        Dependencies::get(ObtenerNotificacionesReporteHandler::class),
        Dependencies::get(ObtenerCantidadNoLeidasHandler::class),
        Dependencies::get(ObtenerNoLeidasHandler::class),
        Dependencies::get(CrearNotificacionMaquinaHandler::class),
        Dependencies::get(MarcarComoLeidaHandler::class),
        Dependencies::get(MarcarTodasComoLeidasHandler::class)
    );
});

Dependencies::register(ReporteController::class, function() {
    return new ReporteController(
        Dependencies::get(CrearReporteHandler::class),
        Dependencies::get(ActualizarEstadoReporteHandler::class),
        Dependencies::get(ObtenerReportesPorUsuarioHandler::class),
        Dependencies::get(ObtenerChatHandler::class),
        Dependencies::get(ObtenerUsuariosChatHandler::class),
        Dependencies::get(ObtenerChatCompletoHandler::class),
        Dependencies::get(ObtenerReportePorIdHandler::class)
    );
});

Dependencies::register(ComentarioController::class, function() {
    return new ComentarioController(
        Dependencies::get(CrearComentarioHandler::class),
        Dependencies::get(ObtenerComentariosPorReporteHandler::class)
    );
});

Dependencies::register(InformeController::class, function() {
    return new InformeController(
        Dependencies::get(RegistrarRecaudacionHandler::class),
        Dependencies::get(ActualizarRecaudacionHandler::class),
        Dependencies::get(EliminarRecaudacionHandler::class),
        Dependencies::get(GuardarInformeHandler::class),
        Dependencies::get(ObtenerRecaudacionesHandler::class),
        Dependencies::get(ObtenerResumenRecaudacionesHandler::class),
        Dependencies::get(ObtenerRecaudacionPorIdHandler::class),
        Dependencies::get(ObtenerMaquinasRecaudacionHandler::class),
        Dependencies::get(ObtenerMaquinasOperativasPorComercioHandler::class),
        Dependencies::get(ObtenerComercioRecaudacionHandler::class),
        Dependencies::get(ObtenerInformePorRecaudacionHandler::class)
    );
});

Dependencies::register(HistorialMaquinaController::class, function() {
    return new HistorialMaquinaController(
        Dependencies::get(ObtenerHistorialPorMaquinaHandler::class),
        Dependencies::get(ObtenerHistorialPorUsuarioHandler::class),
        Dependencies::get(ObtenerHistorialGeneralHandler::class),
        Dependencies::get(ObtenerResumenRecienteHandler::class)
    );
});

Dependencies::register(DistribucionController::class, function() {
    return new DistribucionController(
        Dependencies::get(ObtenerInformesDistribucionHandler::class)
    );
});

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