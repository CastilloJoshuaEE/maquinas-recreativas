<?php
/**
 * maquinas_recreativas - Controlador HTTP de Administrador
 *
 * Maneja todas las operaciones CRUD de usuarios y gestión administrativa.
 * Implementa CQRS separando comandos (escritura) de queries (lectura).
 * Solo accesible por usuarios con rol 'Administrador'.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 2.0.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

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
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

/**
 * Class AdministradorController
 *
 * Controlador exclusivo para administradores. Permite gestionar usuarios,
 * cambiar estados, eliminar cuentas y consultar historial de actividades.
 */
class AdministradorController
{
    private RegistrarUsuarioAdminHandler $registrarUsuarioAdminHandler;
    private ActualizarUsuarioHandler $actualizarUsuarioHandler;
    private CambiarEstadoUsuarioHandler $cambiarEstadoUsuarioHandler;
    private EliminarUsuarioHandler $eliminarUsuarioHandler;
    private ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler;
    private ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler;
    private ObtenerHistorialActividadesHandler $historialHandler;

    /**
     * Constructor con inyección de dependencias.
     *
     * @param RegistrarUsuarioAdminHandler $registrarUsuarioAdminHandler
     * @param ActualizarUsuarioHandler $actualizarUsuarioHandler
     * @param CambiarEstadoUsuarioHandler $cambiarEstadoUsuarioHandler
     * @param EliminarUsuarioHandler $eliminarUsuarioHandler
     * @param ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler
     * @param ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler
     * @param ObtenerHistorialActividadesHandler $historialHandler
     */
    public function __construct(
        RegistrarUsuarioAdminHandler $registrarUsuarioAdminHandler,
        ActualizarUsuarioHandler $actualizarUsuarioHandler,
        CambiarEstadoUsuarioHandler $cambiarEstadoUsuarioHandler,
        EliminarUsuarioHandler $eliminarUsuarioHandler,
        ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler,
        ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler,
        ObtenerHistorialActividadesHandler $historialHandler
    ) {
        $this->registrarUsuarioAdminHandler = $registrarUsuarioAdminHandler;
        $this->actualizarUsuarioHandler = $actualizarUsuarioHandler;
        $this->cambiarEstadoUsuarioHandler = $cambiarEstadoUsuarioHandler;
        $this->eliminarUsuarioHandler = $eliminarUsuarioHandler;
        $this->obtenerUsuarioPorIdHandler = $obtenerUsuarioPorIdHandler;
        $this->obtenerTodosUsuariosHandler = $obtenerTodosUsuariosHandler;
        $this->historialHandler = $historialHandler;
    }

    // =============================================
    // MÉTODOS DE LECTURA (QUERIES)
    // =============================================

    /**
     * Obtiene todos los usuarios con filtros opcionales.
     *
     * @route GET /administrador/usuarios
     * @param Request $request
     * @return Response
     * @throws DomainException
     */
    public function getAllUsers(Request $request): Response
    {
        // Verificar autenticación y rol (ya lo hace el middleware, pero validamos por seguridad)
        if (!isset($_SESSION['ID_Usuario'])) {
            throw new DomainException('No autorizado', 401);
        }

        if (($_SESSION['rol'] ?? '') !== 'Administrador') {
            throw new DomainException('No tiene permisos suficientes', 403);
        }

        // Obtener filtros de la query string
        $filters = [
            'tipo' => $request->query('tipo'),
            'estado' => $request->query('estado'),
            'ci' => $request->query('ci'),
            'limit' => $request->query('limit') ? (int) $request->query('limit') : 100,
            'offset' => $request->query('offset') ? (int) $request->query('offset') : 0
        ];

        $query = new ObtenerTodosUsuariosQuery(
            $filters['tipo'],
            $filters['estado'],
            $filters['ci'],
            $filters['limit'],
            $filters['offset']
        );

        $usuarios = $this->obtenerTodosUsuariosHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'usuarios' => $usuarios,
            'total' => count($usuarios),
            'filtros' => $filters
        ]);
    }

    /**
     * Obtiene un usuario específico por su ID.
     *
     * @route GET /administrador/usuarios/:uuid
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function getUser(Request $request, string $id): Response
    {
        // Validar UUID
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        // Incluir datos sensibles para administrador
        $query = new ObtenerUsuarioPorIdQuery($id, true);
        $usuario = $this->obtenerUsuarioPorIdHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'usuario' => $usuario
        ]);
    }

    /**
     * Obtiene el historial de actividades de un usuario.
     *
     * @route GET /administrador/usuarios/:uuid/historial
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function getHistorialActividades(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $limite = $request->query('limite') ? (int) $request->query('limite') : 50;

        $query = new ObtenerHistorialActividadesQuery(new Uuid($id), $limite);
        $historial = $this->historialHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'historial' => $historial,
            'usuario_id' => $id
        ]);
    }

    // =============================================
    // MÉTODOS DE ESCRITURA (COMMANDS)
    // =============================================

    /**
     * Registra un nuevo usuario (solo administradores).
     *
     * @route POST /administrador/usuarios
     * @param Request $request
     * @return Response
     * @throws DomainException
     */
    public function registerAdmin(Request $request): Response
    {
        $data = $request->json();

        // Validaciones básicas
        $required = ['nombre', 'apellido', 'ci', 'email', 'usuario_asignado', 'contrasena', 'tipo'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        // Validar formato de email
        if (!ValidationHelper::validateEmail($data['email'])) {
            throw new DomainException('Formato de email inválido', 400);
        }

        // Validar longitud de contraseña
        if (!ValidationHelper::validatePassword($data['contrasena'])) {
            throw new DomainException('La contraseña debe tener al menos 8 caracteres', 400);
        }

        // Validar que el tipo sea válido
        $tiposPermitidos = ['Administrador', 'Tecnico', 'Logistica', 'Contabilidad', 'Usuario'];
        if (!in_array($data['tipo'], $tiposPermitidos, true)) {
            throw new DomainException('Tipo de usuario no válido', 400);
        }

        // Validar especialidad para técnicos
        if ($data['tipo'] === 'Tecnico' && empty($data['especialidad'])) {
            throw new DomainException('La especialidad es requerida para técnicos', 400);
        }

        $command = new RegistrarUsuarioAdminCommand(
            $data['nombre'],
            $data['apellido'],
            $data['ci'],
            $data['email'],
            $data['usuario_asignado'],
            $data['contrasena'],
            $data['tipo'],
            $data['estado'] ?? 'Activo',
            $data['especialidad'] ?? null
        );

        $usuario = $this->registrarUsuarioAdminHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'id' => $usuario->getId()->value(),
            'usuario' => [
                'id' => $usuario->getId()->value(),
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
                'email' => $usuario->getEmail(),
                'usuario_asignado' => $usuario->getUsuarioAsignado(),
                'tipo' => $usuario->getTipo()->value(),
                'estado' => $usuario->getEstado()->value(),
                'especialidad' => $usuario->getEspecialidad()
            ]
        ], 201);
    }

    /**
     * Actualiza un usuario completo (todos los campos).
     *
     * @route PUT /administrador/usuarios/:uuid
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function updateUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data = $request->json();

        // Validaciones básicas
        $required = ['nombre', 'apellido', 'email', 'ci', 'tipo', 'estado', 'usuario_asignado'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        // Validar formato de email
        if (!ValidationHelper::validateEmail($data['email'])) {
            throw new DomainException('Formato de email inválido', 400);
        }

        // Validar que el tipo sea válido
        $tiposPermitidos = ['Administrador', 'Tecnico', 'Logistica', 'Contabilidad', 'Usuario'];
        if (!in_array($data['tipo'], $tiposPermitidos, true)) {
            throw new DomainException('Tipo de usuario no válido', 400);
        }

        // Validar especialidad para técnicos
        if ($data['tipo'] === 'Tecnico' && empty($data['especialidad'])) {
            throw new DomainException('La especialidad es requerida para técnicos', 400);
        }

        $command = new ActualizarUsuarioCommand(
            $id,
            $data['nombre'],
            $data['apellido'],
            $data['email'],
            $data['ci'],
            $data['tipo'],
            $data['estado'],
            $data['usuario_asignado'],
            $data['especialidad'] ?? null,
            $data['contrasena'] ?? null
        );

        $this->actualizarUsuarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente'
        ]);
    }

    /**
     * Actualiza parcialmente un usuario (solo los campos enviados).
     *
     * @route PATCH /administrador/usuarios/:uuid
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function partialUpdateUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data = $request->json();

        // Al menos un campo para actualizar
        if (empty($data)) {
            throw new DomainException('No se enviaron datos para actualizar', 400);
        }

        // Si solo se actualiza el estado, usar el comando específico
        if (count($data) === 1 && isset($data['estado'])) {
            $command = new CambiarEstadoUsuarioCommand(new Uuid($id), $data['estado']);
            $this->cambiarEstadoUsuarioHandler->handle($command);

            return (new Response())->json([
                'success' => true,
                'message' => 'Estado de usuario actualizado correctamente'
            ]);
        }

        // Para actualización parcial, obtenemos el usuario actual y fusionamos
        $query = new ObtenerUsuarioPorIdQuery($id, true);
        $usuarioActual = $this->obtenerUsuarioPorIdHandler->handle($query);

        $command = new ActualizarUsuarioCommand(
            $id,
            $data['nombre'] ?? $usuarioActual['nombre'],
            $data['apellido'] ?? $usuarioActual['apellido'],
            $data['email'] ?? $usuarioActual['email'],
            $data['ci'] ?? $usuarioActual['ci'],
            $data['tipo'] ?? $usuarioActual['tipo'],
            $data['estado'] ?? $usuarioActual['estado'],
            $data['usuario_asignado'] ?? $usuarioActual['usuario_asignado'],
            $data['especialidad'] ?? $usuarioActual['especialidad'] ?? null,
            $data['contrasena'] ?? null
        );

        $this->actualizarUsuarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente'
        ]);
    }

    /**
     * Cambia el estado de un usuario (Activo/Inactivo).
     *
     * @route PATCH /administrador/usuarios/:uuid/estado
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function cambiarEstado(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $data = $request->json();
        if (!isset($data['estado'])) {
            throw new DomainException('Estado requerido', 400);
        }

        $estadosPermitidos = ['Activo', 'Inactivo', 'Suspendido', 'Pendiente_asignacion'];
        if (!in_array($data['estado'], $estadosPermitidos, true)) {
            throw new DomainException('Estado no válido', 400);
        }

        $command = new CambiarEstadoUsuarioCommand(new Uuid($id), $data['estado']);
        $this->cambiarEstadoUsuarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Estado de usuario actualizado correctamente'
        ]);
    }

    /**
     * Elimina un usuario del sistema.
     *
     * @route DELETE /administrador/usuarios/:uuid
     * @param Request $request
     * @param string $id ID del usuario (UUID)
     * @return Response
     * @throws DomainException
     */
    public function deleteUser(Request $request, string $id): Response
    {
        if (!ValidationHelper::isValidUUID($id)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        // No permitir eliminar al propio administrador
        if (isset($_SESSION['ID_Usuario']) && $_SESSION['ID_Usuario'] === $id) {
            throw new DomainException('No puedes eliminar tu propia cuenta', 403);
        }

        $command = new EliminarUsuarioCommand(new Uuid($id));
        $this->eliminarUsuarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }

    // =============================================
    // MÉTODOS DE ESTADÍSTICAS Y REPORTES
    // =============================================

    /**
     * Obtiene estadísticas generales del sistema (solo administradores).
     *
     * @route GET /administrador/estadisticas
     * @param Request $request
     * @return Response
     */
    public function getEstadisticas(Request $request): Response
    {
        // Este método podría implementar consultas agregadas
        // Por ahora devolvemos una estructura básica
        $query = new ObtenerTodosUsuariosQuery(null, null, null, 1000, 0);
        $usuarios = $this->obtenerTodosUsuariosHandler->handle($query);

        $estadisticas = [
            'total_usuarios' => count($usuarios),
            'por_tipo' => [],
            'por_estado' => []
        ];

        foreach ($usuarios as $usuario) {
            $tipo = $usuario['tipo'];
            $estado = $usuario['estado'];

            if (!isset($estadisticas['por_tipo'][$tipo])) {
                $estadisticas['por_tipo'][$tipo] = 0;
            }
            $estadisticas['por_tipo'][$tipo]++;

            if (!isset($estadisticas['por_estado'][$estado])) {
                $estadisticas['por_estado'][$estado] = 0;
            }
            $estadisticas['por_estado'][$estado]++;
        }

        return (new Response())->json([
            'success' => true,
            'estadisticas' => $estadisticas
        ]);
    }
}