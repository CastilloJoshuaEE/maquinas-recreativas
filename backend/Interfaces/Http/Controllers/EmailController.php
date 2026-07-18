<?php
/**
 * Interfaces/Http/Controllers/EmailController.php
 *
 * Controlador para el envío de emails desde el panel de administrador.
 * Solo accesible para el rol Administrador.
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Application\Commands\Email\EnviarEmailCommand;
use maquinas_recreativas\Application\Commands\Email\EnviarEmailHandler;
use maquinas_recreativas\Infrastructure\Services\Email\BrevoEmailService;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class EmailController
{
    private EnviarEmailHandler $enviarEmailHandler;
    private BrevoEmailService  $emailService;

    public function __construct(
        EnviarEmailHandler $enviarEmailHandler,
        BrevoEmailService  $emailService
    ) {
        $this->enviarEmailHandler = $enviarEmailHandler;
        $this->emailService       = $emailService;
    }

    #[OA\Post(
        path: "/v1/administrador/enviar-email",
        summary: "Enviar un email a un destinatario (solo Administrador)",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["destinatarioEmail", "asunto", "mensaje"],
                properties: [
                    new OA\Property(property: "destinatarioEmail",  type: "string", format: "email",
                        example: "usuario@ejemplo.com"),
                    new OA\Property(property: "destinatarioNombre", type: "string",
                        example: "Juan Pérez"),
                    new OA\Property(property: "asunto",  type: "string",
                        example: "Notificación del sistema"),
                    new OA\Property(property: "mensaje", type: "string",
                        example: "Estimado usuario, le informamos que...")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Email enviado exitosamente"),
            new OA\Response(response: 400, description: "Datos inválidos"),
            new OA\Response(response: 401, description: "No autenticado"),
            new OA\Response(response: 403, description: "Solo Administradores pueden enviar emails"),
            new OA\Response(response: 500, description: "Error al enviar el email")
        ]
    )]
    public function enviarEmail(Request $request): Response
    {
        // Solo administradores
        $rol = $_SESSION['rol'] ?? '';
        if (is_object($rol) && method_exists($rol, 'value')) {
            $rol = $rol->value();
        }
        if ($rol !== 'Administrador') {
            throw new DomainException('Solo los administradores pueden enviar emails', 403);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('No autenticado', 401);
        }

        $data = $request->json();

        // Validar campos requeridos
        foreach (['destinatarioEmail', 'asunto', 'mensaje'] as $campo) {
            if (empty($data[$campo])) {
                throw new DomainException("El campo '{$campo}' es requerido", 400);
            }
        }

        // Obtener nombre del admin remitente desde la sesión
        $remitenteNombre = $_SESSION['usuario_asignado'] ?? 'Administrador';

        $command = new EnviarEmailCommand(
            $data['destinatarioEmail'],
            $data['destinatarioNombre'] ?? '',
            $data['asunto'],
            $data['mensaje'],
            $remitenteNombre
        );

        try {
            $resultado = $this->enviarEmailHandler->handle($command);

            error_log("Email enviado desde admin {$remitenteNombre} a {$data['destinatarioEmail']}");

            return (new Response())->json([
                'success'   => true,
                'message'   => 'Email enviado correctamente',
                'messageId' => $resultado['messageId'] ?? ''
            ]);

        } catch (DomainException $e) {
            return (new Response())->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], $e->getCode() >= 400 ? $e->getCode() : 500);
        }
    }

    #[OA\Get(
        path: "/v1/administrador/email/estado",
        summary: "Verificar si el servicio de email está configurado",
        tags: ["Administrador"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Estado del servicio de email")
        ]
    )]
    public function estadoServicio(Request $request): Response
    {
        $apiKeyConfigurada = !empty($_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY'));
        $conectado         = $apiKeyConfigurada ? $this->emailService->verificarConexion() : false;

        return (new Response())->json([
            'success'           => true,
            'apiKeyConfigurada' => $apiKeyConfigurada,
            'conectado'         => $conectado,
            'fromEmail'         => $_ENV['EMAIL_FROM_EMAIL'] ?? getenv('EMAIL_FROM_EMAIL') ?? '(no configurado)'
        ]);
    }
}