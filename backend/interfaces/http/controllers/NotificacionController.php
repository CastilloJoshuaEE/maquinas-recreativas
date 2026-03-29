<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaCommand;
use maquinas_recreativas\Application\Commands\Notificacion\CrearNotificacionMaquinaHandler;
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
    private MarcarComoLeidaHandler $marcarComoLeidaHandler;
    private MarcarTodasComoLeidasHandler $marcarTodasComoLeidasHandler;

    public function __construct(
        ObtenerNotificacionesMaquinaHandler $obtenerNotificacionesMaquinaHandler,
        ObtenerNotificacionesReporteHandler $obtenerNotificacionesReporteHandler,
        ObtenerCantidadNoLeidasHandler $obtenerCantidadNoLeidasHandler,
        ObtenerNoLeidasHandler $obtenerNoLeidasHandler,
        CrearNotificacionMaquinaHandler $crearNotificacionMaquinaHandler,
        MarcarComoLeidaHandler $marcarComoLeidaHandler,
        MarcarTodasComoLeidasHandler $marcarTodasComoLeidasHandler
    ) {
        $this->obtenerNotificacionesMaquinaHandler = $obtenerNotificacionesMaquinaHandler;
        $this->obtenerNotificacionesReporteHandler = $obtenerNotificacionesReporteHandler;
        $this->obtenerCantidadNoLeidasHandler      = $obtenerCantidadNoLeidasHandler;
        $this->obtenerNoLeidasHandler              = $obtenerNoLeidasHandler;
        $this->crearNotificacionMaquinaHandler     = $crearNotificacionMaquinaHandler;
        $this->marcarComoLeidaHandler              = $marcarComoLeidaHandler;
        $this->marcarTodasComoLeidasHandler        = $marcarTodasComoLeidasHandler;
    }

    /**
     * @OA\Get(
     *     path="/v1/notificaciones_maquina/{uuid}",
     *     summary="Obtener notificaciones de máquinas de un usuario",
     *     tags={"Notificaciones"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de notificaciones de máquinas")
     * )
     */
    public function obtenerPorUsuario(Request $request, string $idDestinatario): Response
    {
        $query  = new ObtenerNotificacionesMaquinaQuery($idDestinatario);
        $result = $this->obtenerNotificacionesMaquinaHandler->handle($query);

        return (new Response())->json(['success' => true, 'notificaciones' => $result['notificaciones'], 'count' => $result['count']]);
    }

    /**
     * @OA\Get(
     *     path="/v1/notificaciones/{uuid}",
     *     summary="Obtener notificaciones de reportes de un usuario",
     *     tags={"Notificaciones"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de notificaciones de reportes")
     * )
     */
    public function getNotificaciones(Request $request, string $idUsuario): Response
    {
        $query  = new ObtenerNotificacionesReporteQuery($idUsuario);
        $result = $this->obtenerNotificacionesReporteHandler->handle($query);

        return (new Response())->json(['success' => true, 'notificaciones' => $result['notificaciones'], 'count' => $result['count']]);
    }

    /**
     * @OA\Post(
     *     path="/v1/notificaciones/{uuid}/marcarla-leida",
     *     summary="Marcar una notificación específica como leída",
     *     tags={"Notificaciones"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Notificación marcada como leída"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function marcarComoLeidaNotificacion(Request $request, string $idNotificacion): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new MarcarComoLeidaCommand($idNotificacion, $userId);
        $this->marcarComoLeidaHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Notificación marcada como leída']);
    }

    /**
     * @OA\Post(
     *     path="/v1/notificaciones/marcarla-todas-leidas",
     *     summary="Marcar todas las notificaciones del usuario como leídas",
     *     tags={"Notificaciones"},
     *     @OA\Response(response=200, description="Todas las notificaciones marcadas como leídas"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function marcarTodasComoLeidas(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new MarcarTodasComoLeidasCommand($userId);
        $this->marcarTodasComoLeidasHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    }

    /**
     * @OA\Post(
     *     path="/v1/notificaciones/create",
     *     summary="Crear una notificación de máquina",
     *     tags={"Notificaciones"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idRemitente","idDestinatario","idMaquina","tipo","mensaje"},
     *             @OA\Property(property="idRemitente", type="string"),
     *             @OA\Property(property="idDestinatario", type="string"),
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="tipo", type="string"),
     *             @OA\Property(property="mensaje", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Notificación creada exitosamente"),
     *     @OA\Response(response=400, description="Datos incompletos")
     * )
     */
    public function create(Request $request): Response
    {
        $data     = $request->json();
        $required = ['idRemitente', 'idDestinatario', 'idMaquina', 'tipo', 'mensaje'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $command = new CrearNotificacionMaquinaCommand($data['idRemitente'], $data['idDestinatario'], $data['idMaquina'], $data['tipo'], $data['mensaje']);
        $this->crearNotificacionMaquinaHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Notificación creada exitosamente'], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/notificaciones/marcar-leida",
     *     summary="Marcar una notificación como leída (versión legacy por body)",
     *     tags={"Notificaciones"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idNotificacion"}, @OA\Property(property="idNotificacion", type="string"))),
     *     @OA\Response(response=200, description="Notificación marcada como leída"),
     *     @OA\Response(response=400, description="ID requerido"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
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

        return (new Response())->json(['success' => true, 'message' => 'Notificación marcada como leída']);
    }

    /**
     * @OA\Get(
     *     path="/v1/notificaciones/no-leidas/{uuid}",
     *     summary="Obtener cantidad de notificaciones no leídas de un usuario",
     *     tags={"Notificaciones"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Cantidad de notificaciones no leídas")
     * )
     */
    public function obtenerNoLeidas(Request $request, string $idUsuario): Response
    {
        $query  = new ObtenerCantidadNoLeidasQuery($idUsuario);
        $result = $this->obtenerCantidadNoLeidasHandler->handle($query);

        return (new Response())->json(['success' => true, 'cantidad' => $result['cantidad']]);
    }
}