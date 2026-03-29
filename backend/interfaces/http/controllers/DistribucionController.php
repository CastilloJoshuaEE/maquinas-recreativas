<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionQuery;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class DistribucionController
{
    private ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler;

    public function __construct(ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler)
    {
        $this->obtenerInformesDistribucionHandler = $obtenerInformesDistribucionHandler;
    }

    /**
     * @OA\Get(
     *     path="/v1/distribucion/informes",
     *     summary="Obtener informes de distribución con filtros",
     *     tags={"Distribución"},
     *     @OA\Parameter(name="estado", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="idComercio", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="idMaquina", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="fechaInicio", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="fechaFin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=100)),
     *     @OA\Parameter(name="offset", in="query", required=false, @OA\Schema(type="integer", default=0)),
     *     @OA\Response(response=200, description="Lista de informes de distribución")
     * )
     */
    public function obtenerInformesDistribucion(Request $request): Response
    {
        $query  = new ObtenerInformesDistribucionQuery(
            $request->query('estado'),
            $request->query('idComercio'),
            $request->query('idMaquina'),
            $request->query('fechaInicio'),
            $request->query('fechaFin'),
            (int) ($request->query('limit') ?? 100),
            (int) ($request->query('offset') ?? 0)
        );
        $result = $this->obtenerInformesDistribucionHandler->handle($query);

        return (new Response())->json(['success' => true, 'informes' => $result['informes'], 'total' => $result['total']]);
    }
}