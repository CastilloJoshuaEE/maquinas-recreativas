<?php
/**
 * maquinas_recreativas - Controlador de Historial de Máquinas
 *
 * Maneja las consultas de historial.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorMaquinaQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorMaquinaHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorUsuarioQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorUsuarioHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialGeneralQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialGeneralHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerResumenRecienteQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerResumenRecienteHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class HistorialMaquinaController
{
    private ObtenerHistorialPorMaquinaHandler $historialPorMaquinaHandler;
    private ObtenerHistorialPorUsuarioHandler $historialPorUsuarioHandler;
    private ObtenerHistorialGeneralHandler $historialGeneralHandler;
    private ObtenerResumenRecienteHandler $resumenRecienteHandler;

    public function __construct(
        ObtenerHistorialPorMaquinaHandler $historialPorMaquinaHandler,
        ObtenerHistorialPorUsuarioHandler $historialPorUsuarioHandler,
        ObtenerHistorialGeneralHandler $historialGeneralHandler,
        ObtenerResumenRecienteHandler $resumenRecienteHandler
    ) {
        $this->historialPorMaquinaHandler = $historialPorMaquinaHandler;
        $this->historialPorUsuarioHandler = $historialPorUsuarioHandler;
        $this->historialGeneralHandler = $historialGeneralHandler;
        $this->resumenRecienteHandler = $resumenRecienteHandler;
    }

    /**
     * Obtener historial por máquina
     * @route GET /v1/historial/maquina/{uuid}
     */
    public function getHistorialPorMaquina(Request $request, string $idMaquina): Response
    {
        $pagina = (int)($request->query('pagina') ?? 1);
        $porPagina = (int)($request->query('por_pagina') ?? 50);

        $query = new ObtenerHistorialPorMaquinaQuery($idMaquina, $pagina, $porPagina);
        $result = $this->historialPorMaquinaHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'historial' => $result['historial'],
            'paginacion' => $result['paginacion']
        ]);
    }

    /**
     * Obtener historial por usuario
     * @route GET /v1/historial/usuario/{uuid}
     */
    public function getHistorialPorUsuario(Request $request, string $idUsuario): Response
    {
        $pagina = (int)($request->query('pagina') ?? 1);
        $porPagina = (int)($request->query('por_pagina') ?? 50);

        $query = new ObtenerHistorialPorUsuarioQuery($idUsuario, $pagina, $porPagina);
        $result = $this->historialPorUsuarioHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'historial' => $result['historial'],
            'paginacion' => $result['paginacion']
        ]);
    }

    /**
     * Obtener historial general con filtros
     * @route GET /v1/historial/general
     */
    public function getHistorialGeneral(Request $request): Response
    {
        $query = new ObtenerHistorialGeneralQuery(
            $request->query('idMaquina'),
            $request->query('idUsuario'),
            $request->query('tipoUsuario'),
            $request->query('accion'),
            $request->query('fechaInicio'),
            $request->query('fechaFin'),
            (int)($request->query('pagina') ?? 1),
            (int)($request->query('por_pagina') ?? 100)
        );

        $result = $this->historialGeneralHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'historial' => $result['historial'],
            'paginacion' => $result['paginacion']
        ]);
    }

    /**
     * Obtener resumen de actividades recientes
     * @route GET /v1/historial/resumen
     */
    public function getResumenReciente(Request $request): Response
    {
        $limite = (int)($request->query('limite') ?? 20);
        $query = new ObtenerResumenRecienteQuery($limite);
        $resumen = $this->resumenRecienteHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'resumen' => $resumen
        ]);
    }
}