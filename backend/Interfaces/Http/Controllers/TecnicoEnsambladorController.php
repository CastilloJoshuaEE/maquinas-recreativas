<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaCommand;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class TecnicoEnsambladorController
{
    private ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerMaquinasHandler;
    private RegistrarMontajeHandler $registrarMontajeHandler;
    private GenerarPlacaHandler $generarPlacaHandler;

    public function __construct(
        ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerMaquinasHandler,
        RegistrarMontajeHandler $registrarMontajeHandler,
        GenerarPlacaHandler $generarPlacaHandler
    ) {
        $this->obtenerMaquinasHandler  = $obtenerMaquinasHandler;
        $this->registrarMontajeHandler = $registrarMontajeHandler;
        $this->generarPlacaHandler     = $generarPlacaHandler;
    }

    #[OA\Get(
        path: "/v1/tecnico/ensamblador/maquinas",
        summary: "Obtener máquinas asignadas al técnico ensamblador autenticado",
        tags: ["Técnico Ensamblador"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas asignadas"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerMaquinasAsignadas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        $query   = new ObtenerMaquinasPorTecnicoEnsambladorQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    #[OA\Post(
        path: "/v1/tecnico/ensamblador/montaje",
        summary: "Registrar montaje de un componente en una máquina",
        tags: ["Técnico Ensamblador"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "idComponente"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "idComponente", type: "string"),
                    new OA\Property(property: "detalle", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Montaje registrado"),
            new OA\Response(response: 400, description: "Datos obligatorios faltantes o IDs inválidos"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function registrarMontaje(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['idComponente'])) {
            throw new DomainException('Faltan datos obligatorios', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new RegistrarMontajeCommand($data['idMaquina'], $data['idComponente'], $tecnicoId, $data['detalle'] ?? null);
        $this->registrarMontajeHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Montaje registrado']);
    }

    #[OA\Post(
        path: "/v1/tecnico/ensamblador/generar-placa",
        summary: "Generar una nueva placa de componente logístico",
        tags: ["Técnico Ensamblador"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Placa generada con ID de componente"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function generarPlaca(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        $command = new GenerarPlacaCommand($tecnicoId);
        $result  = $this->generarPlacaHandler->handle($command);

        return (new Response())->json(['success' => true, 'placa' => $result['placa'], 'idComponente' => $result['idComponente']]);
    }
}