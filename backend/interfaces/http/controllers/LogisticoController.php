<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoCommand;
use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionHandler;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionQuery;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class LogisticoController
{
    private ObtenerMaquinasParaDistribucionHandler $obtenerMaquinasParaDistribucionHandler;
    private ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler;
    private DarMantenimientoHandler $darMantenimientoHandler;

    public function __construct(
        ObtenerMaquinasParaDistribucionHandler $obtenerMaquinasParaDistribucionHandler,
        ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler,
        DarMantenimientoHandler $darMantenimientoHandler
    ) {
        $this->obtenerMaquinasParaDistribucionHandler = $obtenerMaquinasParaDistribucionHandler;
        $this->obtenerInformesDistribucionHandler     = $obtenerInformesDistribucionHandler;
        $this->darMantenimientoHandler                = $darMantenimientoHandler;
    }

    #[OA\Get(
        path: "/v1/logistico/maquinas-distribucion",
        summary: "Obtener máquinas listas para distribución",
        tags: ["Logístico"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas listas para distribuir"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerMaquinasParaDistribucion(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('No autorizado', 401);
        }

        $query   = new ObtenerMaquinasParaDistribucionQuery();
        $maquinas = $this->obtenerMaquinasParaDistribucionHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    #[OA\Get(
        path: "/v1/logistico/informes-distribucion",
        summary: "Obtener informes de distribución",
        tags: ["Logístico"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "estado", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "idComercio", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "idMaquina", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "fechaInicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fechaFin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Informes de distribución"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerInformesDistribucion(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('No autorizado', 401);
        }

        $query   = new ObtenerInformesDistribucionQuery(
            $request->query('estado'),
            $request->query('idComercio'),
            $request->query('idMaquina'),
            $request->query('fechaInicio'),
            $request->query('fechaFin')
        );
        $informes = $this->obtenerInformesDistribucionHandler->handle($query);

        return (new Response())->json(['success' => true, 'informes' => $informes]);
    }

    #[OA\Post(
        path: "/v1/logistico/solicitar-mantenimiento",
        summary: "Solicitar mantenimiento para una máquina",
        tags: ["Logístico"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Mantenimiento solicitado"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function solicitarMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $logisticaId = $_SESSION['ID_Usuario'] ?? null;
        if (!$logisticaId) {
            throw new DomainException('No autorizado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new DarMantenimientoCommand($data['idMaquina'], $data['mensaje'], $logisticaId);
        $this->darMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento solicitado']);
    }
}