<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Reporte\CrearReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\CrearReporteHandler;
use maquinas_recreativas\Application\Commands\Reporte\ActualizarEstadoReporteCommand;
use maquinas_recreativas\Application\Commands\Reporte\ActualizarEstadoReporteHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportesPorUsuarioQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportesPorUsuarioHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerUsuariosChatQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerUsuariosChatHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatCompletoQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerChatCompletoHandler;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportePorIdQuery;
use maquinas_recreativas\Application\Queries\Reporte\ObtenerReportePorIdHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class ReporteController
{
    private CrearReporteHandler $crearReporteHandler;
    private ActualizarEstadoReporteHandler $actualizarEstadoReporteHandler;
    private ObtenerReportesPorUsuarioHandler $obtenerReportesPorUsuarioHandler;
    private ObtenerChatHandler $obtenerChatHandler;
    private ObtenerUsuariosChatHandler $obtenerUsuariosChatHandler;
    private ObtenerChatCompletoHandler $obtenerChatCompletoHandler;
    private ObtenerReportePorIdHandler $obtenerReportePorIdHandler;

    public function __construct(
        CrearReporteHandler $crearReporteHandler,
        ActualizarEstadoReporteHandler $actualizarEstadoReporteHandler,
        ObtenerReportesPorUsuarioHandler $obtenerReportesPorUsuarioHandler,
        ObtenerChatHandler $obtenerChatHandler,
        ObtenerUsuariosChatHandler $obtenerUsuariosChatHandler,
        ObtenerChatCompletoHandler $obtenerChatCompletoHandler,
        ObtenerReportePorIdHandler $obtenerReportePorIdHandler
    ) {
        $this->crearReporteHandler              = $crearReporteHandler;
        $this->actualizarEstadoReporteHandler   = $actualizarEstadoReporteHandler;
        $this->obtenerReportesPorUsuarioHandler = $obtenerReportesPorUsuarioHandler;
        $this->obtenerChatHandler               = $obtenerChatHandler;
        $this->obtenerUsuariosChatHandler       = $obtenerUsuariosChatHandler;
        $this->obtenerChatCompletoHandler       = $obtenerChatCompletoHandler;
        $this->obtenerReportePorIdHandler       = $obtenerReportePorIdHandler;
    }

    #[OA\Post(
        path: "/v1/reportes/crear",
        summary: "Crear un nuevo reporte",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["descripcion"],
                properties: [
                    new OA\Property(property: "descripcion", type: "string"),
                    new OA\Property(property: "idUsuarioDestinatario", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Reporte creado exitosamente"),
            new OA\Response(response: 400, description: "Descripción requerida"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
public function create(Request $request): Response
{
    $response = new Response();
    try {
        $data = $request->json();
        error_log("ReporteController::create - Datos recibidos: " . json_encode($data));
        
        if (!isset($data['descripcion'])) {
            throw new DomainException('La descripción es requerida', 400);
        }
        
        $userId = $_SESSION['ID_Usuario'] ?? $data['ID_Usuario_Emisor'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }
        
        $destinatarioId = $data['ID_Usuario_Destinatario'] ?? $data['idUsuarioDestinatario'] ?? null;
        if (empty($destinatarioId)) {
            throw new DomainException('El destinatario es requerido', 400);
        }
        
        error_log("Creando reporte - Emisor: $userId, Destinatario: $destinatarioId");
        
        $command = new CrearReporteCommand($userId, $destinatarioId, $data['descripcion']);
        $idReporte = $this->crearReporteHandler->handle($command);
        
        error_log("Reporte creado con ID: $idReporte");
        
        return $response->json([
            'success' => true,
            'message' => 'Reporte creado exitosamente',
            'id' => $idReporte
        ], 201);
        
    } catch (\Throwable $e) {
        error_log("Error en ReporteController::create: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return $response->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}

    #[OA\Get(
        path: "/v1/reportes/usuario/{uuid}",
        summary: "Obtener reportes de un usuario",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de reportes del usuario"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getByUser(Request $request, string $idUsuario): Response
    {
        if (!ValidationHelper::isValidUUID($idUsuario)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $query = new ObtenerReportesPorUsuarioQuery($idUsuario);
        $reportes = $this->obtenerReportesPorUsuarioHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'reportes' => $reportes]);
        return $response;
    }

    #[OA\Get(
        path: "/v1/reportes/chat/{emisorId}/{destinatarioId}",
        summary: "Obtener hilo de chat entre dos usuarios",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "emisorId", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "destinatarioId", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Reportes del chat"),
            new OA\Response(response: 400, description: "IDs inválidos"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getChat(Request $request, string $emisorId, string $destinatarioId): Response
    {
        if (!ValidationHelper::isValidUUID($emisorId) || !ValidationHelper::isValidUUID($destinatarioId)) {
            throw new DomainException('IDs de usuario inválidos', 400);
        }

        $query = new ObtenerChatQuery($emisorId, $destinatarioId);
        $reportes = $this->obtenerChatHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'reportes' => $reportes]);
        return $response;
    }

    #[OA\Put(
        path: "/v1/reportes/{uuid}/estado",
        summary: "Actualizar el estado de un reporte",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["estado"],
                properties: [
                    new OA\Property(property: "estado", type: "string", enum: ["Pendiente", "En proceso", "Resuelto"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Estado actualizado"),
            new OA\Response(response: 400, description: "Estado no válido o UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function updateStatus(Request $request, string $idReporte): Response
    {
        if (!ValidationHelper::isValidUUID($idReporte)) {
            throw new DomainException('ID de reporte inválido', 400);
        }

        $data = $request->json();
        if (!isset($data['estado'])) {
            throw new DomainException('Estado requerido', 400);
        }

        $estadosPermitidos = ['Pendiente', 'En proceso', 'Resuelto'];
        if (!in_array($data['estado'], $estadosPermitidos, true)) {
            throw new DomainException('Estado no válido', 400);
        }

        $command = new ActualizarEstadoReporteCommand($idReporte, $data['estado']);
        $this->actualizarEstadoReporteHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        return $response;
    }

    #[OA\Get(
        path: "/v1/reportes/usuarios-chat",
        summary: "Obtener usuarios con los que el usuario autenticado ha chateado",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de usuarios del chat"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function getUsuariosChat(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query = new ObtenerUsuariosChatQuery($userId);
        $usuarios = $this->obtenerUsuariosChatHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'usuarios' => $usuarios]);
        return $response;
    }

    #[OA\Get(
        path: "/v1/reportes/chat-completo",
        summary: "Obtener chat completo incluyendo comentarios",
        tags: ["Reportes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "emisorId", in: "query", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "destinatarioId", in: "query", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "reporteId", in: "query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Chat completo con comentarios"),
            new OA\Response(response: 400, description: "IDs de emisor y destinatario requeridos"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getCompleteChat(Request $request): Response
    {
        $emisorId = $request->query('emisorId');
        $destinatarioId = $request->query('destinatarioId');
        $reporteId = $request->query('reporteId');

        if (!$emisorId || !$destinatarioId) {
            throw new DomainException('IDs de emisor y destinatario requeridos', 400);
        }

        if (!ValidationHelper::isValidUUID($emisorId) || !ValidationHelper::isValidUUID($destinatarioId)) {
            throw new DomainException('IDs de usuario inválidos', 400);
        }

        $query = new ObtenerChatCompletoQuery($emisorId, $destinatarioId, $reporteId);
        $result = $this->obtenerChatCompletoHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'reportes' => $result['reportes'], 'comentarios' => $result['comentarios']]);
        return $response;
    }
}