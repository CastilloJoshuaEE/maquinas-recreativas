<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoCommand;
use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionHandler;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionQuery;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
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

    /**
     * @OA\Get(
     *     path="/v1/logistico/maquinas-distribucion",
     *     summary="Obtener máquinas listas para distribución",
     *     tags={"Logístico"},
     *     @OA\Response(response=200, description="Lista de máquinas listas para distribuir")
     * )
     */
    public function obtenerMaquinasParaDistribucion(Request $request): Response
    {
        $query   = new ObtenerMaquinasParaDistribucionQuery();
        $maquinas = $this->obtenerMaquinasParaDistribucionHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/logistico/informes-distribucion",
     *     summary="Obtener informes de distribución",
     *     tags={"Logístico"},
     *     @OA\Parameter(name="estado", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="idComercio", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="idMaquina", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="fechaInicio", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="fechaFin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Informes de distribución")
     * )
     */
    public function obtenerInformesDistribucion(Request $request): Response
    {
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

    /**
     * @OA\Post(
     *     path="/v1/logistico/solicitar-mantenimiento",
     *     summary="Solicitar mantenimiento para una máquina",
     *     tags={"Logístico"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idMaquina","mensaje"},
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="mensaje", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Mantenimiento solicitado"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
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

        $command = new DarMantenimientoCommand($data['idMaquina'], $data['mensaje'], $logisticaId);
        $this->darMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento solicitado']);
    }
}