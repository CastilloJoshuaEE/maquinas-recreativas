<?php
/**
 * Controlador HTTP para operaciones de usuario
 * 
 * @package Interfaces\Http\Controllers
 * @author Tu Nombre
 * @version 2.0.0 (DDD + CQRS)
 */

namespace Interfaces\Http\Controllers;

use Application\Commands\Usuario\LoginCommand;
use Application\Commands\Usuario\LoginHandler;
use Application\Commands\Usuario\RegistrarUsuarioCommand;
use Application\Commands\Usuario\RegistrarUsuarioHandler;
use Application\Commands\Usuario\LogoutCommand;
use Application\Commands\Usuario\LogoutHandler;
use Application\Commands\Usuario\ActualizarPerfilHandler;
use Application\Commands\Usuario\RecuperarContrasenaCommand;
use Application\Commands\Usuario\RecuperarContrasenaHandler;
use Application\Commands\Usuario\RegistrarActividadCommand;
use Application\Commands\Usuario\RegistrarActividadHandler;

use Application\Queries\Usuario\ObtenerUsuarioPorIdQuery;
use Application\Queries\Usuario\ObtenerUsuarioPorIdHandler;
use Application\Queries\Usuario\ObtenerTodosUsuariosHandler;
use Application\Queries\Usuario\ObtenerTecnicosPorEspecialidadHandler;
use Application\Queries\Usuario\ObtenerUsuariosPorTipoQuery;
use Application\Queries\Usuario\ObtenerUsuariosPorTipoHandler;
use Application\Queries\Usuario\BuscarPorEmailQuery;
use Application\Queries\Usuario\BuscarPorEmailHandler;
use Application\Queries\Usuario\ObtenerHistorialActividadesQuery;
use Application\Queries\Usuario\ObtenerHistorialActividadesHandler;

use Domain\Shared\Exceptions\DomainException;
use Infrastructure\Security\ValidationHelper;

/**
 * @package Interfaces\Http\Controllers
 * 
 * Controlador que maneja las peticiones HTTP relacionadas con usuarios.
 * Implementa el patrón CQRS separando comandos (escritura) de queries (lectura).
 */
class UsuarioController {
    
    /**
     * @var LoginHandler
     */
    private $loginHandler;
    
    /**
     * @var RegistrarUsuarioHandler
     */
    private $registrarUsuarioHandler;
    
    /**
     * @var ObtenerUsuarioPorIdHandler
     */
    private $obtenerUsuarioPorIdHandler;
    
    /**
     * @var LogoutHandler
     */
    private $logoutHandler;
    
    /**
     * @var ActualizarPerfilHandler
     */
    private $actualizarPerfilHandler;
    
    /**
     * @var RecuperarContrasenaHandler
     */
    private $recuperarContrasenaHandler;
    
    /**
     * @var ObtenerTodosUsuariosHandler
     */
    private $obtenerTodosUsuariosHandler;
    
    /**
     * @var ObtenerTecnicosPorEspecialidadHandler
     */
    private $obtenerTecnicosHandler;
    
    /**
     * @var ObtenerUsuariosPorTipoHandler
     */
    private $obtenerUsuariosPorTipoHandler;
    
    /**
     * @var BuscarPorEmailHandler
     */
    private $buscarPorEmailHandler;
    
    /**
     * @var ObtenerHistorialActividadesHandler
     */
    private $historialHandler;
    
    /**
     * @var RegistrarActividadHandler
     */
    private $registrarActividadHandler;
    
    /**
     * Constructor con inyección de dependencias
     * 
     * @param LoginHandler $loginHandler
     * @param RegistrarUsuarioHandler $registrarUsuarioHandler
     * @param ObtenerUsuarioPorIdHandler $obtenerUsuarioPorIdHandler
     * @param LogoutHandler $logoutHandler
     * @param ActualizarPerfilHandler $actualizarPerfilHandler
     * @param RecuperarContrasenaHandler $recuperarContrasenaHandler
     * @param ObtenerTodosUsuariosHandler $obtenerTodosUsuariosHandler
     * @param ObtenerTecnicosPorEspecialidadHandler $obtenerTecnicosHandler
     * @param ObtenerUsuariosPorTipoHandler $obtenerUsuariosPorTipoHandler
     * @param BuscarPorEmailHandler $buscarPorEmailHandler
     * @param ObtenerHistorialActividadesHandler $historialHandler
     * @param RegistrarActividadHandler $registrarActividadHandler
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
    
    /**
     * Registra un nuevo usuario en el sistema
     * 
     * @route POST /usuario/register
     * @return void
     */
    public function register(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DomainException(
                    'Datos JSON inválidos',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Sanitizar entrada
            $data = ValidationHelper::sanitizeInput($data);
            
            // Crear comando
            $command = new RegistrarUsuarioCommand(
                $data['nombre'] ?? '',
                $data['apellido'] ?? '',
                $data['ci'] ?? '',
                $data['email'] ?? '',
                $data['contrasena'] ?? '',
                $data['tipo'] ?? '',
                $data['especialidad'] ?? null
            );
            
            // Ejecutar handler
            $result = $this->registrarUsuarioHandler->handle($command);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'userId' => $result['id'],
                'usuario_asignado' => $result['usuario_asignado']
            ], 201);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en register: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Inicia sesión de usuario
     * 
     * @route POST /usuario/login
     * @return void
     */
    public function login(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DomainException(
                    'Datos JSON inválidos',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Obtener IP y User Agent
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Crear comando
            $command = new LoginCommand(
                $data['usuario_asignado'] ?? '',
                $data['contrasena'] ?? '',
                $ipAddress,
                $userAgent
            );
            
            // Ejecutar handler
            $usuario = $this->loginHandler->handle($command);
            
            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            
            $_SESSION['ID_Usuario'] = $usuario['id'];
            $_SESSION['usuario_asignado'] = $usuario['usuario_asignado'];
            $_SESSION['rol'] = $usuario['tipo'];
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'usuario' => $usuario
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Cierra sesión de usuario
     * 
     * @route POST /usuario/logout
     * @return void
     */
    public function logout(): void {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $userId = $_SESSION['ID_Usuario'] ?? null;
            
            if ($userId) {
                // Crear comando
                $command = new LogoutCommand($userId);
                
                // Ejecutar handler
                $this->logoutHandler->handle($command);
                
                // Limpiar sesión
                $_SESSION = array();
                
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params["path"],
                        $params["domain"],
                        $params["secure"],
                        $params["httponly"]
                    );
                }
                
                session_destroy();
            }
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en logout: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }
    
    /**
     * Obtiene perfil de usuario por ID
     * 
     * @route GET /usuario/profile/{id}
     * @param string|null $id ID del usuario
     * @return void
     */
    public function getProfile(?string $id = null): void {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new DomainException(
                    'Método no permitido',
                    DomainException::HTTP_METHOD_NOT_ALLOWED
                );
            }
            
            // Obtener ID de parámetro o query
            if ($id === null) {
                $id = $_GET['id'] ?? null;
            }
            
            if (!$id) {
                throw new DomainException(
                    'ID de usuario no proporcionado',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Validar UUID
            if (!ValidationHelper::isValidUUID($id)) {
                throw new DomainException(
                    'ID de usuario inválido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Verificar autenticación
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['ID_Usuario'])) {
                throw new DomainException(
                    'No autorizado',
                    DomainException::HTTP_UNAUTHORIZED
                );
            }
            
            // Verificar permisos (solo propio perfil o contabilidad/admin)
            $includeSensitive = false;
            if ($_SESSION['ID_Usuario'] !== $id) {
                $tipo = $_SESSION['rol'] ?? null;
                if (!in_array($tipo, ['Contabilidad', 'Administrador'])) {
                    throw new DomainException(
                        'No autorizado para ver este perfil',
                        DomainException::HTTP_FORBIDDEN
                    );
                }
                $includeSensitive = true; // Admin/Contabilidad ven datos sensibles
            }
            
            // Crear query
            $query = new ObtenerUsuarioPorIdQuery($id, $includeSensitive);
            
            // Ejecutar handler
            $usuario = $this->obtenerUsuarioPorIdHandler->handle($query);
            
            $this->sendResponse([
                'success' => true,
                'usuario' => $usuario
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en getProfile: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener perfil'
            ], 500);
        }
    }
    
    /**
     * Busca usuario por email
     * 
     * @route POST /usuario/buscar-email
     * @return void
     */
    public function buscarPorEmail(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['email'])) {
                throw new DomainException(
                    'Correo electrónico requerido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Validar email
            if (!ValidationHelper::validateEmail($data['email'])) {
                throw new DomainException(
                    'Formato de email inválido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Crear query
            $query = new BuscarPorEmailQuery($data['email']);
            
            // Ejecutar handler
            $result = $this->buscarPorEmailHandler->handle($query);
            
            $this->sendResponse([
                'success' => true,
                'usuario' => $result
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en buscarPorEmail: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al buscar usuario'
            ], 500);
        }
    }
    
    /**
     * Obtiene técnicos por especialidad
     * 
     * @route GET /usuario/tecnicos/{especialidad}
     * @param string $especialidad Especialidad del técnico
     * @return void
     */
    public function obtenerTecnicos(string $especialidad): void {
        try {
            if (!in_array($especialidad, ESPECIALIDADES_TECNICO)) {
                throw new DomainException(
                    'Especialidad no válida',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            $tecnicos = $this->obtenerTecnicosHandler->handle($especialidad);
            
            $this->sendResponse([
                'success' => true,
                'tecnicos' => $tecnicos
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en obtenerTecnicos: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener técnicos'
            ], 500);
        }
    }
    
    /**
     * Obtiene usuarios por tipo
     * 
     * @route GET /usuarios/por-tipo
     * @return void
     */
    public function getByTipo(): void {
        try {
            $tipo = $_GET['tipo'] ?? null;
            $excluirId = $_GET['excluirId'] ?? null;
            
            if (!$tipo) {
                throw new DomainException(
                    'Tipo de usuario requerido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            if (!in_array($tipo, ROLES_PERMITIDOS)) {
                throw new DomainException(
                    'Tipo de usuario no válido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Crear query
            $query = new ObtenerUsuariosPorTipoQuery($tipo, $excluirId);
            
            // Ejecutar handler
            $usuarios = $this->obtenerUsuariosPorTipoHandler->handle($query);
            
            $this->sendResponse([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en getByTipo: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener usuarios'
            ], 500);
        }
    }
    
    /**
     * Obtiene historial de actividades de un usuario
     * 
     * @route GET /historial-actividades
     * @return void
     */
    public function obtenerHistorialActividades(): void {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['ID_Usuario'])) {
                throw new DomainException(
                    'No autorizado',
                    DomainException::HTTP_UNAUTHORIZED
                );
            }
            
            $usuarioId = $_GET['usuarioId'] ?? $_SESSION['ID_Usuario'];
            
            // Validar UUID
            if (!ValidationHelper::isValidUUID($usuarioId)) {
                throw new DomainException(
                    'ID de usuario inválido',
                    DomainException::HTTP_BAD_REQUEST
                );
            }
            
            // Verificar permisos
            if ($_SESSION['ID_Usuario'] !== $usuarioId && $_SESSION['rol'] !== 'Administrador') {
                throw new DomainException(
                    'No autorizado para ver este historial',
                    DomainException::HTTP_FORBIDDEN
                );
            }
            
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
            
            // Crear query
            $query = new ObtenerHistorialActividadesQuery($usuarioId, $limite);
            
            // Ejecutar handler
            $historial = $this->historialHandler->handle($query);
            
            $this->sendResponse([
                'success' => true,
                'historial' => $historial
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en obtenerHistorialActividades: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al obtener historial'
            ], 500);
        }
    }
    
    /**
     * Registra una actividad de usuario
     * 
     * @route POST /historial-actividades
     * @return void
     */
    public function registrarActividad(): void {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['ID_Usuario'])) {
                throw new DomainException(
                    'No autorizado',
                    DomainException::HTTP_UNAUTHORIZED
                );
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $descripcion = $data['descripcion'] ?? 'Actividad no especificada';
            
            // Crear comando
            $command = new RegistrarActividadCommand(
                $_SESSION['ID_Usuario'],
                $descripcion
            );
            
            // Ejecutar handler
            $this->registrarActividadHandler->handle($command);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Actividad registrada correctamente'
            ]);
            
        } catch (DomainException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getHttpCode());
        } catch (\Exception $e) {
            error_log("Error en registrarActividad: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Error al registrar actividad'
            ], 500);
        }
    }
    
    /**
     * Envía respuesta JSON al cliente
     * 
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código HTTP
     * @return void
     */
    private function sendResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}