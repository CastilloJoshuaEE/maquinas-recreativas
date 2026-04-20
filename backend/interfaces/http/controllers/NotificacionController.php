<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

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
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
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

    #[OA\Get(
        path: "/v1/notificaciones_maquina/{uuid}",
        summary: "Obtener notificaciones de máquinas de un usuario",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de notificaciones de máquinas"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
public function obtenerPorUsuario(Request $request, string $idDestinatario): Response
{
    if (!ValidationHelper::isValidUUID($idDestinatario)) {
        throw new DomainException('ID de destinatario inválido', 400);
    }

    $query  = new ObtenerNotificacionesMaquinaQuery($idDestinatario);
    $result = $this->obtenerNotificacionesMaquinaHandler->handle($query);

    $response = new Response();
    $response->json(['success' => true, 'notificaciones' => $result['notificaciones'], 'count' => $result['count']]);
    return $response;
}

    #[OA\Get(
        path: "/v1/notificaciones/{uuid}",
        summary: "Obtener notificaciones de reportes de un usuario",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de notificaciones de reportes"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
 public function getNotificaciones(Request $request, string $idUsuario): Response
{
    if (!ValidationHelper::isValidUUID($idUsuario)) {
        throw new DomainException('ID de usuario inválido', 400);
    }

    $query  = new ObtenerNotificacionesReporteQuery($idUsuario);
    $result = $this->obtenerNotificacionesReporteHandler->handle($query);

    $response = new Response();
    $response->json(['success' => true, 'notificaciones' => $result['notificaciones'], 'count' => $result['count']]);
    return $response;
}

    #[OA\Post(
        path: "/v1/notificaciones/{uuid}/marcarla-leida",
        summary: "Marcar una notificación específica como leída",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Notificación marcada como leída"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
   public function marcarComoLeidaNotificacion(Request $request, string $idNotificacion): Response
{
    if (!ValidationHelper::isValidUUID($idNotificacion)) {
        throw new DomainException('ID de notificación inválido', 400);
    }

    $userId = $_SESSION['ID_Usuario'] ?? null;
    if (!$userId) {
        throw new DomainException('Usuario no autenticado', 401);
    }

    $command = new MarcarComoLeidaCommand($idNotificacion, $userId);
    $this->marcarComoLeidaHandler->handle($command);

    $response = new Response();
    $response->json(['success' => true, 'message' => 'Notificación marcada como leída']);
    return $response;
}
    #[OA\Post(
        path: "/v1/notificaciones/marcarla-todas-leidas",
        summary: "Marcar todas las notificaciones del usuario como leídas",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Todas las notificaciones marcadas como leídas"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
  public function marcarTodasComoLeidas(Request $request): Response
{
    $userId = $_SESSION['ID_Usuario'] ?? null;
    if (!$userId) {
        throw new DomainException('Usuario no autenticado', 401);
    }

    $command = new MarcarTodasComoLeidasCommand($userId);
    $this->marcarTodasComoLeidasHandler->handle($command);

    $response = new Response();
    $response->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    return $response;
}

    #[OA\Post(
        path: "/v1/notificaciones/create",
        summary: "Crear una notificación de máquina",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idRemitente", "idDestinatario", "idMaquina", "tipo", "mensaje"],
                properties: [
                    new OA\Property(property: "idRemitente", type: "string"),
                    new OA\Property(property: "idDestinatario", type: "string"),
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "tipo", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Notificación creada exitosamente"),
            new OA\Response(response: 400, description: "Datos incompletos"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
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

    $response = new Response();
    $response->json(['success' => true, 'message' => 'Notificación creada exitosamente'], 201);
    return $response;
}


    #[OA\Post(
        path: "/v1/notificaciones/marcar-leida",
        summary: "Marcar una notificación como leída (versión legacy por body)",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idNotificacion"],
                properties: [
                    new OA\Property(property: "idNotificacion", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Notificación marcada como leída"),
            new OA\Response(response: 400, description: "ID requerido"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
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

    if (!ValidationHelper::isValidUUID($data['idNotificacion'])) {
        throw new DomainException('ID de notificación inválido', 400);
    }

    $command = new MarcarComoLeidaCommand($data['idNotificacion'], $userId);
    $this->marcarComoLeidaHandler->handle($command);

    $response = new Response();
    $response->json(['success' => true, 'message' => 'Notificación marcada como leída']);
    return $response;
}

    #[OA\Get(
        path: "/v1/notificaciones/no-leidas/{uuid}",
        summary: "Obtener cantidad de notificaciones no leídas de un usuario",
        tags: ["Notificaciones"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Cantidad de notificaciones no leídas"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
public function obtenerNoLeidas(Request $request, string $idUsuario): Response
{
    if (!ValidationHelper::isValidUUID($idUsuario)) {
        throw new DomainException('ID de usuario inválido', 400);
    }

    $query  = new ObtenerCantidadNoLeidasQuery($idUsuario);
    $result = $this->obtenerCantidadNoLeidasHandler->handle($query);

    $response = new Response();
    $response->json(['success' => true, 'cantidad' => $result['cantidad']]);
    return $response;
}


}