<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorMaquinaQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorMaquinaHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorUsuarioQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialPorUsuarioHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialGeneralQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerHistorialGeneralHandler;
use maquinas_recreativas\Application\Queries\Historial\ObtenerResumenRecienteQuery;
use maquinas_recreativas\Application\Queries\Historial\ObtenerResumenRecienteHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
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
        $this->historialGeneralHandler    = $historialGeneralHandler;
        $this->resumenRecienteHandler     = $resumenRecienteHandler;
    }

    #[OA\Get(
        path: "/v1/historial/maquina/{uuid}",
        summary: "Obtener historial de eventos de una máquina",
        tags: ["Historial"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Historial de la máquina con paginación"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getHistorialPorMaquina(Request $request, string $idMaquina): Response
    {
        if (!ValidationHelper::isValidUUID($idMaquina)) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $pagina   = (int) ($request->query('pagina') ?? 1);
        $porPagina = (int) ($request->query('por_pagina') ?? 50);

        $query  = new ObtenerHistorialPorMaquinaQuery($idMaquina, $pagina, $porPagina);
        $result = $this->historialPorMaquinaHandler->handle($query);

        return (new Response())->json(['success' => true, 'historial' => $result['historial'], 'paginacion' => $result['paginacion']]);
    }

    #[OA\Get(
        path: "/v1/historial/usuario/{uuid}",
        summary: "Obtener historial de actividades de un usuario",
        tags: ["Historial"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Historial del usuario con paginación"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getHistorialPorUsuario(Request $request, string $idUsuario): Response
    {
        if (!ValidationHelper::isValidUUID($idUsuario)) {
            throw new DomainException('ID de usuario inválido', 400);
        }

        $pagina   = (int) ($request->query('pagina') ?? 1);
        $porPagina = (int) ($request->query('por_pagina') ?? 50);

        $query  = new ObtenerHistorialPorUsuarioQuery($idUsuario, $pagina, $porPagina);
        $result = $this->historialPorUsuarioHandler->handle($query);

        return (new Response())->json(['success' => true, 'historial' => $result['historial'], 'paginacion' => $result['paginacion']]);
    }

    #[OA\Get(
        path: "/v1/historial/general",
        summary: "Obtener historial general con filtros avanzados",
        tags: ["Historial"],
        parameters: [
            new OA\Parameter(name: "idMaquina", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "idUsuario", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "tipoUsuario", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "accion", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "fechaInicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fechaFin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Historial general con paginación"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getHistorialGeneral(Request $request): Response
    {
        $query  = new ObtenerHistorialGeneralQuery(
            $request->query('idMaquina'), $request->query('idUsuario'),
            $request->query('tipoUsuario'), $request->query('accion'),
            $request->query('fechaInicio'), $request->query('fechaFin'),
            (int) ($request->query('pagina') ?? 1),
            (int) ($request->query('por_pagina') ?? 100)
        );
        $result = $this->historialGeneralHandler->handle($query);

        return (new Response())->json(['success' => true, 'historial' => $result['historial'], 'paginacion' => $result['paginacion']]);
    }

    #[OA\Get(
        path: "/v1/historial/resumen",
        summary: "Obtener resumen de actividades recientes",
        tags: ["Historial"],
        parameters: [
            new OA\Parameter(name: "limite", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Resumen de actividades recientes"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function getResumenReciente(Request $request): Response
    {
        $limite  = (int) ($request->query('limite') ?? 20);
        $query   = new ObtenerResumenRecienteQuery($limite);
        $resumen = $this->resumenRecienteHandler->handle($query);

        return (new Response())->json(['success' => true, 'resumen' => $resumen]);
    }
}