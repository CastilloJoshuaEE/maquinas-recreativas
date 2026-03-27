<?php
/**
 * maquinas_recreativas - Controlador de Reportes
 *
 * Maneja las operaciones CRUD de reportes.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

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
        $this->crearReporteHandler = $crearReporteHandler;
        $this->actualizarEstadoReporteHandler = $actualizarEstadoReporteHandler;
        $this->obtenerReportesPorUsuarioHandler = $obtenerReportesPorUsuarioHandler;
        $this->obtenerChatHandler = $obtenerChatHandler;
        $this->obtenerUsuariosChatHandler = $obtenerUsuariosChatHandler;
        $this->obtenerChatCompletoHandler = $obtenerChatCompletoHandler;
        $this->obtenerReportePorIdHandler = $obtenerReportePorIdHandler;
    }

    /**
     * Crear un nuevo reporte
     * @route POST /v1/reportes/crear
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

        $command = new CrearReporteCommand(
            $userId,
            $data['idUsuarioDestinatario'] ?? null,
            $data['descripcion']
        );

        $idReporte = $this->crearReporteHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Reporte creado exitosamente',
            'id' => $idReporte
        ], 201);
    }

    /**
     * Obtener reportes por usuario
     * @route GET /v1/reportes/usuario/{uuid}
     */
    public function getByUser(Request $request, string $idUsuario): Response
    {
        $query = new ObtenerReportesPorUsuarioQuery($idUsuario);
        $reportes = $this->obtenerReportesPorUsuarioHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'reportes' => $reportes
        ]);
    }

    /**
     * Obtener chat entre dos usuarios
     * @route GET /v1/reportes/chat/{emisorId}/{destinatarioId}
     */
    public function getChat(Request $request, string $emisorId, string $destinatarioId): Response
    {
        $query = new ObtenerChatQuery($emisorId, $destinatarioId);
        $reportes = $this->obtenerChatHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'reportes' => $reportes
        ]);
    }

    /**
     * Actualizar estado de reporte
     * @route PUT /v1/reportes/{uuid}/estado
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

        return (new Response())->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Obtener usuarios con los que ha chateado
     * @route GET /v1/reportes/usuarios-chat
     */
    public function getUsuariosChat(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query = new ObtenerUsuariosChatQuery($userId);
        $usuarios = $this->obtenerUsuariosChatHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Obtener chat completo con comentarios
     * @route GET /v1/reportes/chat-completo
     */
    public function getCompleteChat(Request $request): Response
    {
        $emisorId = $request->query('emisorId');
        $destinatarioId = $request->query('destinatarioId');
        $reporteId = $request->query('reporteId');

        if (!$emisorId || !$destinatarioId) {
            throw new DomainException('IDs de emisor y destinatario requeridos', 400);
        }

        $query = new ObtenerChatCompletoQuery($emisorId, $destinatarioId, $reporteId);
        $result = $this->obtenerChatCompletoHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'reportes' => $result['reportes'],
            'comentarios' => $result['comentarios']
        ]);
    }
}