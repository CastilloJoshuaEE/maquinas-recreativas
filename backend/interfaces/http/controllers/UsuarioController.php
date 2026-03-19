<?php
/**
 * RecreaSys - UI HTTP Controller
 *
 * Controlador para las peticiones HTTP relacionadas con Usuarios.
 *
 * @package RecreaSys\UI\Http\Controller
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\UI\Http\Controller;

use RecreaSys\Application\Command\Usuario\RegistrarUsuarioCommand;
use RecreaSys\Application\Command\Usuario\RegistrarUsuarioHandler;
use RecreaSys\Domain\Usuario\UsuarioRepository;
use InvalidArgumentException;

/**
 * Class UsuarioController
 */
class UsuarioController
{
    private RegistrarUsuarioHandler $registrarUsuarioHandler;
    private UsuarioRepository $usuarioRepository;

    /**
     * UsuarioController constructor.
     *
     * @param RegistrarUsuarioHandler $registrarUsuarioHandler
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(
        RegistrarUsuarioHandler $registrarUsuarioHandler,
        UsuarioRepository $usuarioRepository
    ) {
        $this->registrarUsuarioHandler = $registrarUsuarioHandler;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja la petición POST para registrar un usuario.
     *
     * @return void
     */
    public function register(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError('Datos JSON inválidos.', 400);
                return;
            }

            // 1. Crear el comando
            $command = new RegistrarUsuarioCommand(
                $input['nombre'] ?? '',
                $input['apellido'] ?? '',
                $input['ci'] ?? '',
                $input['email'] ?? '',
                $input['contrasena'] ?? '',
                $input['tipo'] ?? '',
                $input['especialidad'] ?? null
            );

            // 2. Ejecutar el caso de uso
            $nuevoUsuarioId = $this->registrarUsuarioHandler->handle($command);

            // 3. Respuesta
            $this->sendResponse([
                'success' => true,
                'userId' => $nuevoUsuarioId->value(),
                'message' => 'Usuario registrado correctamente.'
            ]);

        } catch (InvalidArgumentException $e) {
            $this->sendError($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            $this->sendError('Error interno del servidor.', 500);
        }
    }

    /**
     * Envía una respuesta JSON.
     *
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    private function sendResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Envía una respuesta de error.
     *
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    private function sendError(string $message, int $statusCode = 400): void
    {
        $this->sendResponse(['success' => false, 'message' => $message], $statusCode);
    }
}