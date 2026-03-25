<?php
/**
 * maquinas_recreativas - Controlador HTTP de Usuario
 *
 * Maneja las operaciones CRUD y autenticación de usuarios.
 * Implementa CQRS separando comandos (escritura) de queries (lectura).
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 2.0.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Usuario\LoginCommand;
use maquinas_recreativas\Application\Commands\Usuario\LoginHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\LogoutCommand;
use maquinas_recreativas\Application\Commands\Usuario\LogoutHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarPerfilCommand;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarPerfilHandler;
use maquinas_recreativas\Application\Commands\Usuario\RecuperarContrasenaCommand;
use maquinas_recreativas\Application\Commands\Usuario\RecuperarContrasenaHandler;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarActividadCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarActividadHandler;

use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuariosPorTipoQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuariosPorTipoHandler;
use maquinas_recreativas\Application\Queries\Usuario\BuscarPorEmailQuery;
use maquinas_recreativas\Application\Queries\Usuario\BuscarPorEmailHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerHistorialActividadesQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerHistorialActividadesHandler;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

/**
 * Class UsuarioController
 */
class UsuarioController
{
    private LoginHandler $loginHandler;
    private RegistrarUsuarioHandler $registrarUsuarioHandler;
    private ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler;
    private LogoutHandler $logoutHandler;
    private ActualizarPerfilHandler $actualizarPerfilHandler;
    private RecuperarContrasenaHandler $recuperarContrasenaHandler;
    private ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler;
    private ObtenerTecnicosPorEspecialidadHandler $obtenerTecnicosHandler;
    private ObtenerUsuariosPorTipoHandler $obtenerUsuariosPorTipoHandler;
    private BuscarPorEmailHandler $buscarPorEmailHandler;
    private ObtenerHistorialActividadesHandler $historialHandler;
    private RegistrarActividadHandler $registrarActividadHandler;

    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        LoginHandler $loginHandler,
        RegistrarUsuarioHandler $registrarUsuarioHandler,
        ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler,
        LogoutHandler $logoutHandler,
        ActualizarPerfilHandler $actualizarPerfilHandler,
        RecuperarContrasenaHandler $recuperarContrasenaHandler,
        ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler,
        ObtenerTecnicosPorEspecialidadHandler $obtenerTecnicosHandler,
        ObtenerUsuariosPorTipoHandler $obtenerUsuariosPorTipoHandler,
        BuscarPorEmailHandler $buscarPorEmailHandler,
        ObtenerHistorialActividadesHandler $historialHandler,
        RegistrarActividadHandler $registrarActividadHandler
    ) {
        $this->loginHandler = $loginHandler;
        $this->registrarUsuarioHandler = $registrarUsuarioHandler;
        $this->obtenerUsuarioPorIdHandler = $obtenerUsuarioPorIdHandler;
        $this->logoutHandler = $logoutHandler;
        $this->actualizarPerfilHandler = $actualizarPerfilHandler;
        $this->recuperarContrasenaHandler = $recuperarContrasenaHandler;
        $this->obtenerTodosUsuariosHandler = $obtenerTodosUsuariosHandler;
        $this->obtenerTecnicosHandler = $obtenerTecnicosHandler;
        $this->obtenerUsuariosPorTipoHandler = $obtenerUsuariosPorTipoHandler;
        $this->buscarPorEmailHandler = $buscarPorEmailHandler;
        $this->historialHandler = $historialHandler;
        $this->registrarActividadHandler = $registrarActividadHandler;
    }

    // --- Métodos públicos (endpoints) ---

    /**
     * Registro de nuevo usuario.
     * @route POST /usuario/register
     */
    public function register(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['contrasena'])) {
            throw new DomainException('Datos incompletos', 400);
        }

        $command = new RegistrarUsuarioCommand(
            $data['nombre'] ?? '',
            $data['apellido'] ?? '',
            $data['ci'] ?? '',
            $data['email'] ?? '',
            $data['contrasena'],
            $data['tipo'] ?? 'Usuario',
            $data['especialidad'] ?? null
        );

        $result = $this->registrarUsuarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'userId' => $result->value(),
            'usuario_asignado' => $result->getUsuarioAsignado() // Asumiendo que el handler retorna un array
        ], 201);
    }

    /**
     * Inicio de sesión.
     * @route POST /usuario/login
     */
    public function login(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['usuario_asignado'], $data['contrasena'])) {
            throw new DomainException('Usuario y contraseña son requeridos', 400);
        }

        $ip = $request->getClientIp();
        $userAgent = $request->header('USER_AGENT');

        $command = new LoginCommand(
            $data['usuario_asignado'],
            $data['contrasena'],
            $ip,
            $userAgent
        );

        $usuario = $this->loginHandler->handle($command);

        // Iniciar sesión PHP
        session_regenerate_id(true);
        $_SESSION['ID_Usuario'] = $usuario['id'];
        $_SESSION['usuario_asignado'] = $usuario['usuario_asignado'];
        $_SESSION['rol'] = $usuario['tipo'];

        return (new Response())->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'usuario' => $usuario
        ]);
    }

    /**
     * Cierre de sesión.
     * @route POST /usuario/logout
     */
    public function logout(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if ($userId) {
            $command = new LogoutCommand($userId);
            $this->logoutHandler->handle($command);
            session_destroy();
        }
        return (new Response())->json(['success' => true, 'message' => 'Sesión cerrada']);
    }

    /**
     * Obtener perfil de usuario.
     * @route GET /usuario/perfil/{id}
     */
    public function getProfile(Request $request, ?string $id = null): Response
    {
        if (!$id) $id = $request->query('id');
        if (!$id) throw new DomainException('ID de usuario no proporcionado', 400);

        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $includeSensitive = ($_SESSION['rol'] ?? '') === 'Administrador' || ($_SESSION['rol'] ?? '') === 'Contabilidad';
        $query = new ObtenerUsuarioPorIdQuery($id, $includeSensitive);
        $usuario = $this->obtenerUsuarioPorIdHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuario' => $usuario]);
    }

    /**
     * Actualizar perfil del usuario autenticado.
     * @route POST /usuario/actualizar-perfil
     */
    public function updateProfile(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['id'])) throw new DomainException('ID de usuario requerido', 400);
        if ($_SESSION['ID_Usuario'] !== $data['id']) throw new DomainException('No autorizado', 403);

        $command = new ActualizarPerfilCommand(
            $data['id'],
            $data['nombre'] ?? '',
            $data['apellido'] ?? '',
            $data['email'] ?? '',
            $data['ci'] ?? '',
            $data['tipo'] ?? '',
            $data['estado'] ?? 'Activo',
            $data['especialidad'] ?? null,
            $data['contrasena'] ?? null
        );
        $this->actualizarPerfilHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Perfil actualizado']);
    }

    /**
     * Buscar usuario por email.
     * @route POST /usuario/buscar-email
     */
    public function buscarPorEmail(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['email'])) throw new DomainException('Correo requerido', 400);
        $query = new BuscarPorEmailQuery($data['email']);
        $usuario = $this->buscarPorEmailHandler->handle($query);
        return (new Response())->json(['success' => true, 'usuario' => $usuario]);
    }

    /**
     * Recuperar contraseña.
     * @route POST /usuario/recuperar-contrasena
     */
    public function resetPassword(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['email'], $data['nueva_contrasena'])) {
            throw new DomainException('Email y nueva contraseña requeridos', 400);
        }
        $command = new RecuperarContrasenaCommand($data['email'], $data['nueva_contrasena']);
        $this->recuperarContrasenaHandler->handle($command);
        return (new Response())->json(['success' => true, 'message' => 'Contraseña actualizada']);
    }

    /**
     * Obtener técnicos por especialidad.
     * @route GET /usuario/tecnicos/{especialidad}
     */
    public function obtenerTecnicos(Request $request, string $especialidad): Response
    {
        $query = new ObtenerTecnicosPorEspecialidadQuery($especialidad);
        $tecnicos = $this->obtenerTecnicosHandler->handle($query);
        return (new Response())->json(['success' => true, 'tecnicos' => $tecnicos]);
    }

    /**
     * Obtener usuarios por tipo.
     * @route GET /usuarios/por-tipo
     */
    public function getByTipo(Request $request): Response
    {
        $tipo = $request->query('tipo');
        $excluirId = $request->query('excluirId');
        if (!$tipo) throw new DomainException('Tipo de usuario requerido', 400);
        $query = new ObtenerUsuariosPorTipoQuery($tipo, $excluirId);
        $usuarios = $this->obtenerUsuariosPorTipoHandler->handle($query);
        return (new Response())->json(['success' => true, 'usuarios' => $usuarios]);
    }

    /**
     * Registrar actividad.
     * @route POST /historial-actividades
     */
    public function registrarActividad(Request $request): Response
    {
        $data = $request->json();
        $descripcion = $data['descripcion'] ?? 'Actividad no especificada';
        $command = new RegistrarActividadCommand($_SESSION['ID_Usuario'], $descripcion);
        $this->registrarActividadHandler->handle($command);
        return (new Response())->json(['success' => true, 'message' => 'Actividad registrada']);
    }

    /**
     * Obtener historial de actividades.
     * @route GET /historial-actividades
     */
    public function obtenerHistorialActividades(Request $request): Response
    {
        $usuarioId = $request->query('usuarioId') ?? $_SESSION['ID_Usuario'] ?? null;
        if (!$usuarioId) throw new DomainException('ID de usuario requerido', 400);
        $query = new ObtenerHistorialActividadesQuery($usuarioId);
        $historial = $this->historialHandler->handle($query);
        return (new Response())->json(['success' => true, 'historial' => $historial]);
    }
}