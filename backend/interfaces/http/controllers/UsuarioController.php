<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

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
        $this->loginHandler                  = $loginHandler;
        $this->registrarUsuarioHandler       = $registrarUsuarioHandler;
        $this->obtenerUsuarioPorIdHandler    = $obtenerUsuarioPorIdHandler;
        $this->logoutHandler                 = $logoutHandler;
        $this->actualizarPerfilHandler       = $actualizarPerfilHandler;
        $this->recuperarContrasenaHandler    = $recuperarContrasenaHandler;
        $this->obtenerTodosUsuariosHandler   = $obtenerTodosUsuariosHandler;
        $this->obtenerTecnicosHandler        = $obtenerTecnicosHandler;
        $this->obtenerUsuariosPorTipoHandler = $obtenerUsuariosPorTipoHandler;
        $this->buscarPorEmailHandler         = $buscarPorEmailHandler;
        $this->historialHandler              = $historialHandler;
        $this->registrarActividadHandler     = $registrarActividadHandler;
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/register",
     *     summary="Registrar un nuevo usuario",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contrasena"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="apellido", type="string"),
     *             @OA\Property(property="ci", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="contrasena", type="string"),
     *             @OA\Property(property="tipo", type="string", default="Usuario"),
     *             @OA\Property(property="especialidad", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado correctamente"),
     *     @OA\Response(response=400, description="Datos incompletos")
     * )
     */
    public function register(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['contrasena'])) {
            throw new DomainException('Datos incompletos', 400);
        }

        $command = new RegistrarUsuarioCommand(
            $data['nombre'] ?? '', $data['apellido'] ?? '', $data['ci'] ?? '',
            $data['email'] ?? '', $data['contrasena'], $data['tipo'] ?? 'Usuario',
            $data['especialidad'] ?? null
        );

        $result = $this->registrarUsuarioHandler->handle($command);

        return (new Response())->json([
            'success'          => true,
            'message'          => 'Usuario registrado correctamente',
            'userId'           => $result->value(),
            'usuario_asignado' => $result->getUsuarioAsignado(),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/login",
     *     summary="Iniciar sesión",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"usuario_asignado","contrasena"},
     *             @OA\Property(property="usuario_asignado", type="string"),
     *             @OA\Property(property="contrasena", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Inicio de sesión exitoso"),
     *     @OA\Response(response=400, description="Credenciales requeridas"),
     *     @OA\Response(response=401, description="Credenciales incorrectas")
     * )
     */
    public function login(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['usuario_asignado'], $data['contrasena'])) {
            throw new DomainException('Usuario y contraseña son requeridos', 400);
        }

        $ip        = $request->getClientIp();
        $userAgent = $request->header('USER_AGENT');

        $command = new LoginCommand($data['usuario_asignado'], $data['contrasena'], $ip, $userAgent);
        $usuario = $this->loginHandler->handle($command);

        session_regenerate_id(true);
        $_SESSION['ID_Usuario']       = $usuario['id'];
        $_SESSION['usuario_asignado'] = $usuario['usuario_asignado'];
        $_SESSION['rol']              = $usuario['tipo'];

        return (new Response())->json(['success' => true, 'message' => 'Inicio de sesión exitoso', 'usuario' => $usuario]);
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/logout",
     *     summary="Cerrar sesión",
     *     tags={"Usuarios"},
     *     @OA\Response(response=200, description="Sesión cerrada correctamente")
     * )
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
     * @OA\Get(
     *     path="/v1/usuario/perfil/{id}",
     *     summary="Obtener perfil de un usuario",
     *     tags={"Usuarios"},
     *     @OA\Parameter(name="id", in="path", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Datos del usuario"),
     *     @OA\Response(response=400, description="ID no proporcionado o inválido")
     * )
     */
    public function getProfile(Request $request, ?string $id = null): Response
    {
        if (!$id) {
            $id = $request->query('id');
        }
        if (!$id) {
            throw new DomainException('ID de usuario no proporcionado', 400);
        }
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $includeSensitive = in_array($_SESSION['rol'] ?? '', ['Administrador', 'Contabilidad']);
        $query   = new ObtenerUsuarioPorIdQuery($id, $includeSensitive);
        $usuario = $this->obtenerUsuarioPorIdHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuario' => $usuario]);
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/actualizar-perfil",
     *     summary="Actualizar perfil del usuario autenticado",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="apellido", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="ci", type="string"),
     *             @OA\Property(property="contrasena", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Perfil actualizado"),
     *     @OA\Response(response=403, description="No autorizado para editar este perfil")
     * )
     */
    public function updateProfile(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['id'])) {
            throw new DomainException('ID de usuario requerido', 400);
        }
        if ($_SESSION['ID_Usuario'] !== $data['id']) {
            throw new DomainException('No autorizado', 403);
        }

        $command = new ActualizarPerfilCommand(
            $data['id'], $data['nombre'] ?? '', $data['apellido'] ?? '', $data['email'] ?? '',
            $data['ci'] ?? '', $data['tipo'] ?? '', $data['estado'] ?? 'Activo',
            $data['especialidad'] ?? null, $data['contrasena'] ?? null
        );
        $this->actualizarPerfilHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Perfil actualizado']);
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/buscar-email",
     *     summary="Buscar un usuario por su correo electrónico",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email"))),
     *     @OA\Response(response=200, description="Usuario encontrado"),
     *     @OA\Response(response=400, description="Correo requerido")
     * )
     */
    public function buscarPorEmail(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['email'])) {
            throw new DomainException('Correo requerido', 400);
        }

        $query   = new BuscarPorEmailQuery($data['email']);
        $usuario = $this->buscarPorEmailHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuario' => $usuario]);
    }

    /**
     * @OA\Post(
     *     path="/v1/usuario/recuperar-contrasena",
     *     summary="Recuperar / restablecer contraseña",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","nueva_contrasena"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="nueva_contrasena", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Contraseña actualizada"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes")
     * )
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
     * @OA\Get(
     *     path="/v1/usuario/tecnicos/{especialidad}",
     *     summary="Obtener técnicos filtrados por especialidad",
     *     tags={"Usuarios"},
     *     @OA\Parameter(name="especialidad", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de técnicos por especialidad")
     * )
     */
    public function obtenerTecnicos(Request $request, string $especialidad): Response
    {
        $query    = new ObtenerTecnicosPorEspecialidadQuery($especialidad);
        $tecnicos = $this->obtenerTecnicosHandler->handle($query);

        return (new Response())->json(['success' => true, 'tecnicos' => $tecnicos]);
    }

    /**
     * @OA\Get(
     *     path="/v1/usuarios/por-tipo",
     *     summary="Obtener usuarios filtrados por tipo",
     *     tags={"Usuarios"},
     *     @OA\Parameter(name="tipo", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="excluirId", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de usuarios por tipo"),
     *     @OA\Response(response=400, description="Tipo requerido")
     * )
     */
    public function getByTipo(Request $request): Response
    {
        $tipo      = $request->query('tipo');
        $excluirId = $request->query('excluirId');
        if (!$tipo) {
            throw new DomainException('Tipo de usuario requerido', 400);
        }

        $query    = new ObtenerUsuariosPorTipoQuery($tipo, $excluirId);
        $usuarios = $this->obtenerUsuariosPorTipoHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuarios' => $usuarios]);
    }

    /**
     * @OA\Post(
     *     path="/v1/historial-actividades",
     *     summary="Registrar una actividad del usuario autenticado",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="descripcion", type="string"))),
     *     @OA\Response(response=200, description="Actividad registrada")
     * )
     */
    public function registrarActividad(Request $request): Response
    {
        $data        = $request->json();
        $descripcion = $data['descripcion'] ?? 'Actividad no especificada';

        $command = new RegistrarActividadCommand($_SESSION['ID_Usuario'], $descripcion);
        $this->registrarActividadHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Actividad registrada']);
    }

    /**
     * @OA\Get(
     *     path="/v1/historial-actividades",
     *     summary="Obtener historial de actividades del usuario",
     *     tags={"Usuarios"},
     *     @OA\Parameter(name="usuarioId", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Historial de actividades"),
     *     @OA\Response(response=400, description="ID de usuario requerido")
     * )
     */
    public function obtenerHistorialActividades(Request $request): Response
    {
        $usuarioId = $request->query('usuarioId') ?? $_SESSION['ID_Usuario'] ?? null;
        if (!$usuarioId) {
            throw new DomainException('ID de usuario requerido', 400);
        }

        $query    = new ObtenerHistorialActividadesQuery($usuarioId);
        $historial = $this->historialHandler->handle($query);

        return (new Response())->json(['success' => true, 'historial' => $historial]);
    }
}