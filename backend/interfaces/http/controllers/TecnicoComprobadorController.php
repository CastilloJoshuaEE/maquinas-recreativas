<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class TecnicoComprobadorController
{
    private ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerMaquinasHandler;
    private MandarADistribucionHandler $mandarADistribucionHandler;
    private MandarAReensamblarHandler $mandarAReensamblarHandler;

    public function __construct(
        ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerMaquinasHandler,
        MandarADistribucionHandler $mandarADistribucionHandler,
        MandarAReensamblarHandler $mandarAReensamblarHandler
    ) {
        $this->obtenerMaquinasHandler    = $obtenerMaquinasHandler;
        $this->mandarADistribucionHandler = $mandarADistribucionHandler;
        $this->mandarAReensamblarHandler  = $mandarAReensamblarHandler;
    }

    #[OA\Get(
        path: "/v1/tecnico/comprobador/maquinas",
        summary: "Obtener máquinas pendientes de comprobación del técnico autenticado",
        tags: ["Técnico Comprobador"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas pendientes de comprobación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerMaquinas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        $query   = new ObtenerMaquinasPorTecnicoComprobadorQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    #[OA\Post(
        path: "/v1/tecnico/comprobador/aprobar-distribucion",
        summary: "Aprobar máquina y enviarla a distribución",
        tags: ["Técnico Comprobador"],
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
            new OA\Response(response: 200, description: "Máquina enviada a distribución"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes o ID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function aprobarYEnviarADistribucion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new MandarADistribucionCommand($data['idMaquina'], $tecnicoId, $data['mensaje']);
        $this->mandarADistribucionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a distribución']);
    }

    #[OA\Post(
        path: "/v1/tecnico/comprobador/rechazar-reensamblar",
        summary: "Rechazar máquina y enviarla a reensamblar",
        tags: ["Técnico Comprobador"],
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
            new OA\Response(response: 200, description: "Máquina enviada a reensamblar"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes o ID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function rechazarYEnviarAReensamblar(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new MandarAReensamblarCommand($data['idMaquina'], $tecnicoId, $data['mensaje']);
        $this->mandarAReensamblarHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a reensamblar']);
    }
}