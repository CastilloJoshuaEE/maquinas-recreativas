<?php
/**
 * maquinas_recreativas - Controlador de Notificaciones
 *
 * Maneja las operaciones de notificaciones.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaCommand;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaHandler;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionReporteCommand;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionReporteHandler;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarComoLeidaCommand;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarComoLeidaHandler;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarTodasComoLeidasCommand;
use maquinas_recreativas\Application\Commands\Notificacion\MarcarTodasComoLeidasHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesMaquinaQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesMaquinaHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesReporteQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNotificacionesReporteHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerCantidadNoLeidasQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerCantidadNoLeidasHandler;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNoLeidasQuery;
use maquinas_recreativas\Application\Queries\Notificacion\ObtenerNoLeidasHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class NotificacionController
{
    private ObtenerNotificacionesMaquinaHandler $obtenerNotificacionesMaquinaHandler;
    private ObtenerNotificacionesReporteHandler $obtenerNotificacionesReporteHandler;
    private ObtenerCantidadNoLeidasHandler $obtenerCantidadNoLeidasHandler;
    private ObtenerNoLeidasHandler $obtenerNoLeidasHandler;
    private CrearNotificacionMaquinaHandler $crearNotificacionMaquinaHandler;
    private CrearNotificacionReporteHandler $crearNotificacionReporteHandler;
    private MarcarComoLeidaHandler $marcarComoLeidaHandler;
    private MarcarTodasComoLeidasHandler $marcarTodasComoLeidasHandler;

    public function __construct(
        ObtenerNotificacionesMaquinaHandler $obtenerNotificacionesMaquinaHandler,
        ObtenerNotificacionesReporteHandler $obtenerNotificacionesReporteHandler,
        ObtenerCantidadNoLeidasHandler $obtenerCantidadNoLeidasHandler,
        ObtenerNoLeidasHandler $obtenerNoLeidasHandler,
        CrearNotificacionMaquinaHandler $crearNotificacionMaquinaHandler,
        CrearNotificacionReporteHandler $crearNotificacionReporteHandler,
        MarcarComoLeidaHandler $marcarComoLeidaHandler,
        MarcarTodasComoLeidasHandler $marcarTodasComoLeidasHandler
    ) {
        $this->obtenerNotificacionesMaquinaHandler = $obtenerNotificacionesMaquinaHandler;
        $this->obtenerNotificacionesReporteHandler = $obtenerNotificacionesReporteHandler;
        $this->obtenerCantidadNoLeidasHandler = $obtenerCantidadNoLeidasHandler;
        $this->obtenerNoLeidasHandler = $obtenerNoLeidasHandler;
        $this->crearNotificacionMaquinaHandler = $crearNotificacionMaquinaHandler;
        $this->crearNotificacionReporteHandler = $crearNotificacionReporteHandler;
        $this->marcarComoLeidaHandler = $marcarComoLeidaHandler;
        $this->marcarTodasComoLeidasHandler = $marcarTodasComoLeidasHandler;
    }

    /**
     * Obtener notificaciones de máquinas por usuario
     * @route GET /v1/notificaciones_maquina/{uuid}
     */
    public function obtenerPorUsuario(Request $request, string $idDestinatario): Response
    {
        $query = new ObtenerNotificacionesMaquinaQuery($idDestinatario);
        $result = $this->obtenerNotificacionesMaquinaHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'notificaciones' => $result['notificaciones'],
            'count' => $result['count']
        ]);
    }

    /**
     * Obtener notificaciones de reportes por usuario
     * @route GET /v1/notificaciones/{uuid}
     */
    public function getNotificaciones(Request $request, string $idUsuario): Response
    {
        $query = new ObtenerNotificacionesReporteQuery($idUsuario);
        $result = $this->obtenerNotificacionesReporteHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'notificaciones' => $result['notificaciones'],
            'count' => $result['count']
        ]);
    }

    /**
     * Marcar notificación como leída
     * @route POST /v1/notificaciones/{uuid}/marcarla-leida
     */
    public function marcarComoLeidaNotificacion(Request $request, string $idNotificacion): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new MarcarComoLeidaCommand($idNotificacion, $userId);
        $this->marcarComoLeidaHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     * @route POST /v1/notificaciones/marcarla-todas-leidas
     */
    public function marcarTodasComoLeidas(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new MarcarTodasComoLeidasCommand($userId);
        $this->marcarTodasComoLeidasHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Crear notificación de máquina
     * @route POST /v1/notificaciones/create
     */
    public function create(Request $request): Response
    {
        $data = $request->json();

        $required = ['idRemitente', 'idDestinatario', 'idMaquina', 'tipo', 'mensaje'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $command = new CrearNotificacionMaquinaCommand(
            $data['idRemitente'],
            $data['idDestinatario'],
            $data['idMaquina'],
            $data['tipo'],
            $data['mensaje']
        );
        $this->crearNotificacionMaquinaHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Notificación creada exitosamente'
        ], 201);
    }

    /**
     * Marcar como leída (versión legacy)
     * @route POST /v1/notificaciones/marcar-leida
     */
    public function marcarComoLeida(Request $request): Response
    {
        $data = $request->json();

        if (!isset($data['idNotificacion'])) {
            throw new DomainException('ID de notificación requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new MarcarComoLeidaCommand($data['idNotificacion'], $userId);
        $this->marcarComoLeidaHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Obtener cantidad de notificaciones no leídas (reportes)
     * @route GET /v1/notificaciones/no-leidas/{uuid}
     */
    public function obtenerNoLeidas(Request $request, string $idUsuario): Response
    {
        $query = new ObtenerCantidadNoLeidasQuery($idUsuario);
        $result = $this->obtenerCantidadNoLeidasHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'cantidad' => $result['cantidad']
        ]);
    }
}