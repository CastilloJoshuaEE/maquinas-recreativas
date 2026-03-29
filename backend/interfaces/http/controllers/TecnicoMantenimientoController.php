<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

use maquinas_recreativas\Application\Commands\Maquina\FinalizarMantenimientoCommand;
use maquinas_recreativas\Application\Commands\Maquina\FinalizarMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoMantenimientoQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoMantenimientoHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class TecnicoMantenimientoController
{
    private ObtenerMaquinasPorTecnicoMantenimientoHandler $obtenerMaquinasHandler;
    private FinalizarMantenimientoHandler $finalizarMantenimientoHandler;

    public function __construct(
        ObtenerMaquinasPorTecnicoMantenimientoHandler $obtenerMaquinasHandler,
        FinalizarMantenimientoHandler $finalizarMantenimientoHandler
    ) {
        $this->obtenerMaquinasHandler       = $obtenerMaquinasHandler;
        $this->finalizarMantenimientoHandler = $finalizarMantenimientoHandler;
    }

    /**
     * @OA\Get(
     *     path="/v1/tecnico/mantenimiento/maquinas",
     *     summary="Obtener máquinas asignadas para mantenimiento del técnico autenticado",
     *     tags={"Técnico Mantenimiento"},
     *     @OA\Response(response=200, description="Lista de máquinas en mantenimiento"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function obtenerMaquinasAsignadas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        $query   = new ObtenerMaquinasPorTecnicoMantenimientoQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Post(
     *     path="/v1/tecnico/mantenimiento/finalizar",
     *     summary="Finalizar el mantenimiento de una máquina",
     *     tags={"Técnico Mantenimiento"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idMaquina","exito","mensaje"},
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="exito", type="boolean"),
     *             @OA\Property(property="mensaje", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Mantenimiento finalizado"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function finalizarMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['exito'], $data['mensaje'])) {
            throw new DomainException('ID de máquina, éxito y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) {
            throw new DomainException('No autorizado', 401);
        }

        $command = new FinalizarMantenimientoCommand($data['idMaquina'], $tecnicoId, (bool) $data['exito'], $data['mensaje']);
        $this->finalizarMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento finalizado']);
    }
}