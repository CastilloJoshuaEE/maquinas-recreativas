<?php
/**
 * Interfaces/Http/Controllers/AdministradorController.php
 *
 * Cambios respecto a la versión original:
 *  - Recibe CacheInterface (inyectado o desde CacheFactory).
 *  - Tras create / update / delete / cambiarEstado invalida:
 *      · usuario:id:{uuid}
 *      · admin:estadisticas
 *      · admin:usuarios:filters:*  (patrón)
 *      · usuarios:all:*            (patrón)
 *      · usuarios:tipo:*           (patrón)
 *  - Todos los #[OA\...] originales se conservan íntegros.
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminCommand;
use maquinas_recreativas\Application\Commands\Usuario\RegistrarUsuarioAdminHandler;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\ActualizarUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\CambiarEstadoUsuarioHandler;
use maquinas_recreativas\Application\Commands\Usuario\EliminarUsuarioCommand;
use maquinas_recreativas\Application\Commands\Usuario\EliminarUsuarioHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerUsuarioPorIdHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerTodosUsuariosHandler;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerHistorialActividadesQuery;
use maquinas_recreativas\Application\Queries\Usuario\ObtenerHistorialActividadesHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;
use maquinas_recreativas\Infrastructure\Cache\CacheFactory;
use maquinas_recreativas\Infrastructure\Cache\RedisCache;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

#[OA\Tag(name: "Administrador", description: "Operaciones exclusivas para administradores")]
class AdministradorController
{
    private RegistrarUsuarioAdminHandler      $registrarUsuarioAdminHandler;
    private ActualizarUsuarioHandler          $actualizarUsuarioHandler;
    private CambiarEstadoUsuarioHandler       $cambiarEstadoUsuarioHandler;
    private EliminarUsuarioHandler            $eliminarUsuarioHandler;
    private ObtenerUsuarioPorIdHandler        $obtenerUsuarioPorIdHandler;
    private ObtenerTodosUsuariosHandler       $obtenerTodosUsuariosHandler;
    private ObtenerHistorialActividadesHandler $historialHandler;
    private CacheInterface                    $cache;

    public function __construct(
        RegistrarUsuarioAdminHandler      $registrarUsuarioAdminHandler,
        ActualizarUsuarioHandler          $actualizarUsuarioHandler,
        CambiarEstadoUsuarioHandler       $cambiarEstadoUsuarioHandler,
        EliminarUsuarioHandler            $eliminarUsuarioHandler,
        ObtenerUsuarioPorIdHandler        $obtenerUsuarioPorIdHandler,
        ObtenerTodosUsuariosHandler       $obtenerTodosUsuariosHandler,
        ObtenerHistorialActividadesHandler $historialHandler,
        ?CacheInterface                   $cache = null
    ) {
        $this->registrarUsuarioAdminHandler = $registrarUsuarioAdminHandler;
        $this->actualizarUsuarioHandler     = $actualizarUsuarioHandler;
        $this->cambiarEstadoUsuarioHandler  = $cambiarEstadoUsuarioHandler;
        $this->eliminarUsuarioHandler       = $eliminarUsuarioHandler;
        $this->obtenerUsuarioPorIdHandler   = $obtenerUsuarioPorIdHandler;
        $this->obtenerTodosUsuariosHandler  = $obtenerTodosUsuariosHandler;
        $this->historialHandler             = $historialHandler;
        $this->cache                        = $cache ?? CacheFactory::create();
    }

    // =========================================================================
    // INVALIDACIÓN CENTRALIZADA
    // =========================================================================

    /**
     * Elimina todas las claves de caché relacionadas con usuarios/admin
     * que deben refrescarse tras cualquier operación de escritura.
     */
    private function invalidarCacheUsuario(string $uuid): void
    {
        $this->cache->delete("usuario:id:{$uuid}");
        $this->cache->delete("admin:estadisticas");

        if ($this->cache instanceof RedisCache) {
            $this->cache->deleteByPattern("admin:usuarios:filters:*");
            $this->cache->deleteByPattern("usuarios:all:*");
            $this->cache->deleteByPattern("usuarios:tipo:*");
            $this->cache->deleteByPattern("tecnicos:especialidad:*");
            $this->cache->deleteByPattern("tecnicos:disponibles:*");
        }
    }

    // =========================================================================
    // ENDPOINTS
    // =========================================================================

    #[OA\Get(
        path: "/v1/administrador/usuarios",
        summary: "Obtener todos los usuarios",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "tipo",   in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "estado", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "ci",     in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "limit",  in: "query", required: false, schema: new OA\Schema(type: "integer", default: 100)),
            new OA\Parameter(name: "offset", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 0))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de usuarios"),
            new OA\Response(response: 401, description: "No autorizado"),
            new OA\Response(response: 403, description: "Sin permisos suficientes")
        ]
    )]
    public function getAllUsers(Request $request): Response
    {
        if (!isset($_SESSION['ID_Usuario'])) {
            throw new DomainException('No autorizado', 401);
        }

        $userRole = $_SESSION['rol'] ?? '';
        error_log("Verificando permisos - Rol en sesión: '{$userRole}'");

        if (is_object($userRole) && method_exists($userRole, 'value')) {
            $userRole = $userRole->value();
        }

        if ($userRole !== 'Administrador') {
            throw new DomainException('No tiene permisos suficientes', 403);
        }

        $filters = [
            'tipo'   => $request->query('tipo'),
            'estado' => $request->query('estado'),
            'ci'     => $request->query('ci'),
            'limit'  => $request->query('limit')  ? (int)$request->query('limit')  : 100,
            'offset' => $request->query('offset') ? (int)$request->query('offset') : 0,
        ];

        $query    = new ObtenerTodosUsuariosQuery(
            $filters['tipo'], $filters['estado'], $filters['ci'],
            $filters['limit'], $filters['offset']
        );
        $usuarios = $this->obtenerTodosUsuariosHandler->handle($query);

        return (new Response())->json([
            'success'  => true,
            'usuarios' => $usuarios,
            'total'    => count($usuarios),
            'filtros'  => $filters,
        ]);
    }

    #[OA\Get(
        path: "/v1/administrador/usuarios/{uuid}",
        summary: "Obtener un usuario por ID",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Datos del usuario"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 404, description: "Usuario no encontrado")
        ]
    )]
    public function getUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $query   = new ObtenerUsuarioPorIdQuery($id, true);
        $usuario = $this->obtenerUsuarioPorIdHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuario' => $usuario]);
    }

    #[OA\Get(
        path: "/v1/administrador/usuarios/{uuid}/historial",
        summary: "Obtener historial de actividades de un usuario",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid",   in: "path",  required: true,  schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "limite", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 50))
        ],
        responses: [
            new OA\Response(response: 200, description: "Historial de actividades"),
            new OA\Response(response: 400, description: "UUID inválido")
        ]
    )]
    public function getHistorialActividades(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $query    = new ObtenerHistorialActividadesQuery(new Uuid($id));
        $historial = $this->historialHandler->handle($query);

        return (new Response())->json([
            'success'    => true,
            'historial'  => $historial,
            'usuario_id' => $id,
        ]);
    }

    #[OA\Post(
        path: "/v1/administrador/usuarios",
        summary: "Registrar un nuevo usuario (admin)",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "ci", "email", "contrasena", "tipo"],
                properties: [
                    new OA\Property(property: "nombre",           type: "string"),
                    new OA\Property(property: "apellido",         type: "string"),
                    new OA\Property(property: "ci",               type: "string"),
                    new OA\Property(property: "email",            type: "string", format: "email"),
                    new OA\Property(property: "usuario_asignado", type: "string"),
                    new OA\Property(property: "contrasena",       type: "string"),
                    new OA\Property(property: "tipo",             type: "string", enum: ["Administrador","Tecnico","Logistica","Contabilidad","Usuario"]),
                    new OA\Property(property: "estado",           type: "string", default: "Activo"),
                    new OA\Property(property: "especialidad",     type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Usuario creado correctamente"),
            new OA\Response(response: 400, description: "Datos inválidos")
        ]
    )]
    public function registerAdmin(Request $request): Response
    {
        $data     = $request->json();
        $required = ['nombre', 'apellido', 'ci', 'email', 'contrasena', 'tipo'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        if (!ValidationHelper::validateEmail($data['email'])) {
            throw new DomainException('Formato de email inválido', 400);
        }
        if (!ValidationHelper::validatePassword($data['contrasena'])) {
            throw new DomainException('La contraseña debe tener al menos 8 caracteres', 400);
        }

        $tiposPermitidos = ['Administrador', 'Tecnico', 'Logistica', 'Contabilidad', 'Usuario'];
        if (!in_array($data['tipo'], $tiposPermitidos, true)) {
            throw new DomainException('Tipo de usuario no válido', 400);
        }
        if ($data['tipo'] === 'Tecnico' && empty($data['especialidad'])) {
            throw new DomainException('La especialidad es requerida para técnicos', 400);
        }

        $usuarioAsignado = $data['usuario_asignado'] ?? null;

        $command = new RegistrarUsuarioAdminCommand(
            $data['nombre'], $data['apellido'], $data['ci'], $data['email'],
            $usuarioAsignado, $data['contrasena'], $data['tipo'],
            $data['estado'] ?? 'Activo', $data['especialidad'] ?? null
        );

        $usuario = $this->registrarUsuarioAdminHandler->handle($command);

        // Invalidar caché admin
        $this->invalidarCacheUsuario($usuario->getId()->value());

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'id'      => $usuario->getId()->value(),
            'usuario' => [
                'id'               => $usuario->getId()->value(),
                'nombre'           => $usuario->getNombre(),
                'apellido'         => $usuario->getApellido(),
                'email'            => $usuario->getEmail()->value(),
                'usuario_asignado' => $usuario->getUsuarioAsignado(),
                'tipo'             => $usuario->getTipo()->value(),
                'estado'           => $usuario->getEstado()->value(),
                'especialidad'     => $usuario->getEspecialidad(),
            ],
        ], 201);
    }

    #[OA\Put(
        path: "/v1/administrador/usuarios/{uuid}",
        summary: "Actualizar un usuario completo",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "apellido", "email", "ci", "tipo", "estado", "usuario_asignado"],
                properties: [
                    new OA\Property(property: "nombre",           type: "string"),
                    new OA\Property(property: "apellido",         type: "string"),
                    new OA\Property(property: "email",            type: "string", format: "email"),
                    new OA\Property(property: "ci",               type: "string"),
                    new OA\Property(property: "tipo",             type: "string"),
                    new OA\Property(property: "estado",           type: "string"),
                    new OA\Property(property: "usuario_asignado", type: "string"),
                    new OA\Property(property: "especialidad",     type: "string", nullable: true),
                    new OA\Property(property: "contrasena",       type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Usuario actualizado"),
            new OA\Response(response: 400, description: "Datos inválidos")
        ]
    )]
    public function updateUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data     = $request->json();
        $required = ['nombre', 'apellido', 'email', 'ci', 'tipo', 'estado', 'usuario_asignado'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        if (!ValidationHelper::validateEmail($data['email'])) {
            throw new DomainException('Formato de email inválido', 400);
        }

        $tiposPermitidos = ['Administrador', 'Tecnico', 'Logistica', 'Contabilidad', 'Usuario'];
        if (!in_array($data['tipo'], $tiposPermitidos, true)) {
            throw new DomainException('Tipo de usuario no válido', 400);
        }
        if ($data['tipo'] === 'Tecnico' && empty($data['especialidad'])) {
            throw new DomainException('La especialidad es requerida para técnicos', 400);
        }

        $command = new ActualizarUsuarioCommand(
            $id, $data['nombre'], $data['apellido'], $data['email'], $data['ci'],
            $data['tipo'], $data['estado'], $data['usuario_asignado'],
            $data['especialidad'] ?? null, $data['contrasena'] ?? null
        );

        $this->actualizarUsuarioHandler->handle($command);

        // Invalidar caché
        $this->invalidarCacheUsuario($id);

        return (new Response())->json(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    }

    #[OA\Patch(
        path: "/v1/administrador/usuarios/{uuid}",
        summary: "Actualizar parcialmente un usuario",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre",   type: "string"),
                    new OA\Property(property: "apellido", type: "string"),
                    new OA\Property(property: "email",    type: "string"),
                    new OA\Property(property: "estado",   type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Usuario actualizado"),
            new OA\Response(response: 400, description: "Sin datos para actualizar")
        ]
    )]
    public function partialUpdateUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data = $request->json();
        if (empty($data)) {
            throw new DomainException('No se enviaron datos para actualizar', 400);
        }

        if (count($data) === 1 && isset($data['estado'])) {
            $command = new CambiarEstadoUsuarioCommand(new Uuid($id), $data['estado']);
            $this->cambiarEstadoUsuarioHandler->handle($command);
            $this->invalidarCacheUsuario($id);
            return (new Response())->json(['success' => true, 'message' => 'Estado de usuario actualizado correctamente']);
        }

        $query        = new ObtenerUsuarioPorIdQuery($id, true);
        $usuarioActual = $this->obtenerUsuarioPorIdHandler->handle($query);

        $command = new ActualizarUsuarioCommand(
            $id,
            $data['nombre']           ?? $usuarioActual['nombre'],
            $data['apellido']         ?? $usuarioActual['apellido'],
            $data['email']            ?? $usuarioActual['email'],
            $data['ci']               ?? $usuarioActual['ci'],
            $data['tipo']             ?? $usuarioActual['tipo'],
            $data['estado']           ?? $usuarioActual['estado'],
            $data['usuario_asignado'] ?? $usuarioActual['usuario_asignado'],
            $data['especialidad']     ?? $usuarioActual['especialidad'] ?? null,
            $data['contrasena']       ?? null
        );

        $this->actualizarUsuarioHandler->handle($command);
        $this->invalidarCacheUsuario($id);

        return (new Response())->json(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    }

    #[OA\Patch(
        path: "/v1/administrador/usuarios/{uuid}/estado",
        summary: "Cambiar estado de un usuario",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["estado"],
                properties: [
                    new OA\Property(property: "estado", type: "string", enum: ["Activo","Inhabilitado","Suspendido","Pendiente_asignacion"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Estado actualizado"),
            new OA\Response(response: 400, description: "Estado no válido")
        ]
    )]
    public function cambiarEstado(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data = $request->json();
        if (!isset($data['estado'])) {
            throw new DomainException('Estado requerido', 400);
        }

        $estadosPermitidos = ['Activo', 'Inhabilitado', 'Suspendido', 'Pendiente_asignacion'];
        if (!in_array($data['estado'], $estadosPermitidos, true)) {
            throw new DomainException('Estado no válido', 400);
        }

        $command = new CambiarEstadoUsuarioCommand(new Uuid($id), $data['estado']);
        $this->cambiarEstadoUsuarioHandler->handle($command);

        $this->invalidarCacheUsuario($id);

        return (new Response())->json(['success' => true, 'message' => 'Estado de usuario actualizado correctamente']);
    }

    #[OA\Delete(
        path: "/v1/administrador/usuarios/{uuid}",
        summary: "Eliminar un usuario",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Usuario eliminado"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 403, description: "No puedes eliminarte a ti mismo")
        ]
    )]
    public function deleteUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }
        if (isset($_SESSION['ID_Usuario']) && $_SESSION['ID_Usuario'] === $id) {
            throw new DomainException('No puedes eliminar tu propia cuenta', 403);
        }

        $command = new EliminarUsuarioCommand(new Uuid($id));
        $this->eliminarUsuarioHandler->handle($command);

        $this->invalidarCacheUsuario($id);

        return (new Response())->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    }

    #[OA\Get(
        path: "/v1/administrador/estadisticas",
        summary: "Obtener estadísticas generales del sistema",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Estadísticas del sistema")
        ]
    )]
    public function getEstadisticas(Request $request): Response
    {
        // Las estadísticas se cachean 300 s en el repositorio (admin:estadisticas).
        // Aquí se reconstruyen desde los usuarios para mantener compatibilidad
        // con implementaciones que no usen MySQLAdministradorRepository directamente.
        $query    = new ObtenerTodosUsuariosQuery(null, null, null, 1000, 0);
        $usuarios = $this->obtenerTodosUsuariosHandler->handle($query);

        $estadisticas = [
            'total_usuarios' => count($usuarios),
            'por_tipo'       => [],
            'por_estado'     => [],
        ];

        foreach ($usuarios as $usuario) {
            $tipo   = $usuario['tipo'];
            $estado = $usuario['estado'];
            $estadisticas['por_tipo'][$tipo]     = ($estadisticas['por_tipo'][$tipo]     ?? 0) + 1;
            $estadisticas['por_estado'][$estado] = ($estadisticas['por_estado'][$estado] ?? 0) + 1;
        }

        return (new Response())->json(['success' => true, 'estadisticas' => $estadisticas]);
    }
}
