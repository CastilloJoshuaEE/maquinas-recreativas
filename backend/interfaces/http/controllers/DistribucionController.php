<?php
/**
 * maquinas_recreativas - Controlador de Distribución
 *
 * Maneja las operaciones de distribución.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionQuery;
use maquinas_recreativas\Application\Queries\Distribucion\ObtenerInformesDistribucionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class DistribucionController
{
    private ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler;

    public function __construct(
        ObtenerInformesDistribucionHandler $obtenerInformesDistribucionHandler
    ) {
        $this->obtenerInformesDistribucionHandler = $obtenerInformesDistribucionHandler;
    }

    /**
     * Obtener informes de distribución con filtros
     * @route GET /v1/distribucion/informes
     */
    public function obtenerInformesDistribucion(Request $request): Response
    {
        $query = new ObtenerInformesDistribucionQuery(
            $request->query('estado'),
            $request->query('idComercio'),
            $request->query('idMaquina'),
            $request->query('fechaInicio'),
            $request->query('fechaFin'),
            (int)($request->query('limit') ?? 100),
            (int)($request->query('offset') ?? 0)
        );

        $result = $this->obtenerInformesDistribucionHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'informes' => $result['informes'],
            'total' => $result['total']
        ]);
    }
}