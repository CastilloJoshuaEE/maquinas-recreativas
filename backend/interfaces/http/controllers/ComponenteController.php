<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Application\Commands\Componente\UsarComponenteCommand;
use maquinas_recreativas\Application\Commands\Componente\UsarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponenteCommand;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\AsignarCarcasaCommand;
use maquinas_recreativas\Application\Commands\Componente\AsignarCarcasaHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponentesCancelacionCommand;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponentesCancelacionHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesDisponiblesQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesDisponiblesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesEnUsoQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesEnUsoHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class ComponenteController
{
    private ObtenerComponentesHandler $obtenerComponentesHandler;
    private ObtenerComponentesDisponiblesHandler $obtenerComponentesDisponiblesHandler;
    private UsarComponenteHandler $usarComponenteHandler;
    private LiberarComponenteHandler $liberarComponenteHandler;
    private AsignarCarcasaHandler $asignarCarcasaHandler;
    private LiberarComponentesCancelacionHandler $liberarComponentesCancelacionHandler;
    private ObtenerComponentesEnUsoHandler $obtenerComponentesEnUsoHandler;

    public function __construct(
        ObtenerComponentesHandler $obtenerComponentesHandler,
        ObtenerComponentesDisponiblesHandler $obtenerComponentesDisponiblesHandler,
        UsarComponenteHandler $usarComponenteHandler,
        LiberarComponenteHandler $liberarComponenteHandler,
        AsignarCarcasaHandler $asignarCarcasaHandler,
        LiberarComponentesCancelacionHandler $liberarComponentesCancelacionHandler,
        ObtenerComponentesEnUsoHandler $obtenerComponentesEnUsoHandler
    ) {
        $this->obtenerComponentesHandler            = $obtenerComponentesHandler;
        $this->obtenerComponentesDisponiblesHandler = $obtenerComponentesDisponiblesHandler;
        $this->usarComponenteHandler                = $usarComponenteHandler;
        $this->liberarComponenteHandler             = $liberarComponenteHandler;
        $this->asignarCarcasaHandler                = $asignarCarcasaHandler;
        $this->liberarComponentesCancelacionHandler = $liberarComponentesCancelacionHandler;
        $this->obtenerComponentesEnUsoHandler       = $obtenerComponentesEnUsoHandler;
    }

    #[OA\Get(
        path: "/v1/componentes",
        summary: "Obtener componentes con paginación",
        tags: ["Componentes"],
        parameters: [
            new OA\Parameter(name: "tipo", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "offset", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 0))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de componentes con total")
        ]
    )]
    public function obtenerComponentes(Request $request): Response
    {
        $tipo   = $request->query('tipo');
        $limit  = (int) ($request->query('limit') ?? 10);
        $offset = (int) ($request->query('offset') ?? 0);

        $query  = new ObtenerComponentesQuery($tipo, $limit, $offset);
        $result = $this->obtenerComponentesHandler->handle($query);

        return (new Response())->json(['success' => true, 'componentes' => $result['componentes'], 'total' => $result['total']]);
    }

    #[OA\Get(
        path: "/v1/componentes/disponibles",
        summary: "Obtener componentes disponibles",
        tags: ["Componentes"],
        parameters: [
            new OA\Parameter(name: "tipo", in: "query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de componentes disponibles")
        ]
    )]
    public function obtenerComponentesDisponibles(Request $request): Response
    {
        $tipo        = $request->query('tipo');
        $query       = new ObtenerComponentesDisponiblesQuery($tipo);
        $componentes = $this->obtenerComponentesDisponiblesHandler->handle($query);

        return (new Response())->json(['success' => true, 'componentes' => $componentes]);
    }

    #[OA\Post(
        path: "/v1/componentes/usar",
        summary: "Asignar/usar un componente",
        tags: ["Componentes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idComponente"],
                properties: [
                    new OA\Property(property: "idComponente", type: "string"),
                    new OA\Property(property: "idMaquina", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Componente asignado correctamente"),
            new OA\Response(response: 400, description: "ID de componente requerido"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function usarComponente(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new UsarComponenteCommand($data['idComponente'], $userId, $data['idMaquina'] ?? null);
        $this->usarComponenteHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Componente asignado correctamente']);
    }

    #[OA\Post(
        path: "/v1/componentes/liberar",
        summary: "Liberar un componente",
        tags: ["Componentes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idComponente"],
                properties: [
                    new OA\Property(property: "idComponente", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Componente liberado correctamente"),
            new OA\Response(response: 400, description: "ID de componente requerido"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function liberarComponente(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new LiberarComponenteCommand($data['idComponente'], $userId);
        $this->liberarComponenteHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Componente liberado correctamente']);
    }

    #[OA\Post(
        path: "/v1/componentes/asignar-carcasa",
        summary: "Asignar carcasa a un técnico",
        tags: ["Componentes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idComponente"],
                properties: [
                    new OA\Property(property: "idComponente", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Carcasa asignada correctamente"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function asignarCarcasa(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new AsignarCarcasaCommand($data['idComponente'], $userId);
        $this->asignarCarcasaHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Carcasa asignada correctamente']);
    }

    #[OA\Post(
        path: "/v1/componentes/liberar-cancelacion",
        summary: "Liberar componentes por cancelación",
        tags: ["Componentes"],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "idPlaca", type: "string", nullable: true),
                    new OA\Property(property: "idCarcasa", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Componentes liberados"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function liberarComponentesCancelacion(Request $request): Response
    {
        $data   = $request->json();
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new LiberarComponentesCancelacionCommand($data['idPlaca'] ?? null, $data['idCarcasa'] ?? null, $userId);
        $result  = $this->liberarComponentesCancelacionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => $result['message']]);
    }

    #[OA\Get(
        path: "/v1/componentes/en-uso/{uuid}",
        summary: "Obtener componentes en uso por un usuario",
        tags: ["Componentes"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "idMaquina", in: "query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Componentes en uso")
        ]
    )]
    public function obtenerComponentesEnUso(Request $request, string $idUsuario): Response
    {
        $idMaquina   = $request->query('idMaquina');
        $query       = new ObtenerComponentesEnUsoQuery($idUsuario, $idMaquina);
        $componentes = $this->obtenerComponentesEnUsoHandler->handle($query);

        return (new Response())->json(['success' => true, 'componentes' => $componentes]);
    }
}