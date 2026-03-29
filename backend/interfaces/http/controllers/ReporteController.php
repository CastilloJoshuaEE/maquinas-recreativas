<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

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

    /**
     * @OA\Post(
     *     path="/v1/reportes/crear",
     *     summary="Crear un nuevo reporte",
     *     tags={"Reportes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"descripcion"},
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="idUsuarioDestinatario", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reporte creado exitosamente"),
     *     @OA\Response(response=400, description="Descripción requerida"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function create(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['descripcion'])) {
            throw new DomainException('La descripción es requerida', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command   = new CrearReporteCommand($userId, $data['idUsuarioDestinatario'] ?? null, $data['descripcion']);
        $idReporte = $this->crearReporteHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Reporte creado exitosamente', 'id' => $idReporte], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/reportes/usuario/{uuid}",
     *     summary="Obtener reportes de un usuario",
     *     tags={"Reportes"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de reportes del usuario")
     * )
     */
    public function getByUser(Request $request, string $idUsuario): Response
    {
        $query    = new ObtenerReportesPorUsuarioQuery($idUsuario);
        $reportes = $this->obtenerReportesPorUsuarioHandler->handle($query);

        return (new Response())->json(['success' => true, 'reportes' => $reportes]);
    }

    /**
     * @OA\Get(
     *     path="/v1/reportes/chat/{emisorId}/{destinatarioId}",
     *     summary="Obtener hilo de chat entre dos usuarios",
     *     tags={"Reportes"},
     *     @OA\Parameter(name="emisorId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="destinatarioId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Reportes del chat")
     * )
     */
    public function getChat(Request $request, string $emisorId, string $destinatarioId): Response
    {
        $query    = new ObtenerChatQuery($emisorId, $destinatarioId);
        $reportes = $this->obtenerChatHandler->handle($query);

        return (new Response())->json(['success' => true, 'reportes' => $reportes]);
    }

    /**
     * @OA\Put(
     *     path="/v1/reportes/{uuid}/estado",
     *     summary="Actualizar el estado de un reporte",
     *     tags={"Reportes"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"estado"},
     *             @OA\Property(property="estado", type="string", enum={"Pendiente","En proceso","Resuelto"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Estado actualizado"),
     *     @OA\Response(response=400, description="Estado no válido")
     * )
     */
    public function updateStatus(Request $request, string $idReporte): Response
    {
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

        return (new Response())->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }

    /**
     * @OA\Get(
     *     path="/v1/reportes/usuarios-chat",
     *     summary="Obtener usuarios con los que el usuario autenticado ha chateado",
     *     tags={"Reportes"},
     *     @OA\Response(response=200, description="Lista de usuarios del chat"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function getUsuariosChat(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query    = new ObtenerUsuariosChatQuery($userId);
        $usuarios = $this->obtenerUsuariosChatHandler->handle($query);

        return (new Response())->json(['success' => true, 'usuarios' => $usuarios]);
    }

    /**
     * @OA\Get(
     *     path="/v1/reportes/chat-completo",
     *     summary="Obtener chat completo incluyendo comentarios",
     *     tags={"Reportes"},
     *     @OA\Parameter(name="emisorId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="destinatarioId", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="reporteId", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Chat completo con comentarios"),
     *     @OA\Response(response=400, description="IDs de emisor y destinatario requeridos")
     * )
     */
    public function getCompleteChat(Request $request): Response
    {
        $emisorId       = $request->query('emisorId');
        $destinatarioId = $request->query('destinatarioId');
        $reporteId      = $request->query('reporteId');

        if (!$emisorId || !$destinatarioId) {
            throw new DomainException('IDs de emisor y destinatario requeridos', 400);
        }

        $query  = new ObtenerChatCompletoQuery($emisorId, $destinatarioId, $reporteId);
        $result = $this->obtenerChatCompletoHandler->handle($query);

        return (new Response())->json(['success' => true, 'reportes' => $result['reportes'], 'comentarios' => $result['comentarios']]);
    }
}