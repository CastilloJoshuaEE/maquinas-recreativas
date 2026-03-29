<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\ActualizarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\ActualizarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\EliminarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\EliminarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\GuardarInformeCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\GuardarInformeHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionesQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerResumenRecaudacionesQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerResumenRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionPorIdQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionPorIdHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasOperativasPorComercioQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasOperativasPorComercioHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerComercioRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerComercioRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerInformePorRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerInformePorRecaudacionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class InformeController
{
    private RegistrarRecaudacionHandler $registrarRecaudacionHandler;
    private ActualizarRecaudacionHandler $actualizarRecaudacionHandler;
    private EliminarRecaudacionHandler $eliminarRecaudacionHandler;
    private GuardarInformeHandler $guardarInformeHandler;
    private ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler;
    private ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler;
    private ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler;
    private ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler;
    private ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler;
    private ObtenerComercioRecaudacionHandler $obtenerComercioRecaudacionHandler;
    private ObtenerInformePorRecaudacionHandler $obtenerInformePorRecaudacionHandler;

    public function __construct(
        RegistrarRecaudacionHandler $registrarRecaudacionHandler,
        ActualizarRecaudacionHandler $actualizarRecaudacionHandler,
        EliminarRecaudacionHandler $eliminarRecaudacionHandler,
        GuardarInformeHandler $guardarInformeHandler,
        ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler,
        ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler,
        ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler,
        ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler,
        ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler,
        ObtenerComercioRecaudacionHandler $obtenerComercioRecaudacionHandler,
        ObtenerInformePorRecaudacionHandler $obtenerInformePorRecaudacionHandler
    ) {
        $this->registrarRecaudacionHandler                  = $registrarRecaudacionHandler;
        $this->actualizarRecaudacionHandler                 = $actualizarRecaudacionHandler;
        $this->eliminarRecaudacionHandler                   = $eliminarRecaudacionHandler;
        $this->guardarInformeHandler                        = $guardarInformeHandler;
        $this->obtenerRecaudacionesHandler                  = $obtenerRecaudacionesHandler;
        $this->obtenerResumenRecaudacionesHandler           = $obtenerResumenRecaudacionesHandler;
        $this->obtenerRecaudacionPorIdHandler               = $obtenerRecaudacionPorIdHandler;
        $this->obtenerMaquinasRecaudacionHandler            = $obtenerMaquinasRecaudacionHandler;
        $this->obtenerMaquinasOperativasPorComercioHandler  = $obtenerMaquinasOperativasPorComercioHandler;
        $this->obtenerComercioRecaudacionHandler            = $obtenerComercioRecaudacionHandler;
        $this->obtenerInformePorRecaudacionHandler          = $obtenerInformePorRecaudacionHandler;
    }

    /**
     * @OA\Post(
     *     path="/v1/contabilidad/registrar-recaudacion",
     *     summary="Registrar una nueva recaudación",
     *     tags={"Contabilidad"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idMaquina","tipoComercio","montoTotal","porcentajeComercio"},
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="tipoComercio", type="string"),
     *             @OA\Property(property="montoTotal", type="number", format="float"),
     *             @OA\Property(property="porcentajeComercio", type="number", format="float"),
     *             @OA\Property(property="detalle", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Recaudación registrada exitosamente"),
     *     @OA\Response(response=400, description="Datos incompletos"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function registrarRecaudacion(Request $request): Response
    {
        $data     = $request->json();
        $required = ['idMaquina', 'tipoComercio', 'montoTotal', 'porcentajeComercio'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command      = new RegistrarRecaudacionCommand($data['idMaquina'], $userId, $data['tipoComercio'], (float) $data['montoTotal'], (float) $data['porcentajeComercio'], $data['detalle'] ?? '');
        $idRecaudacion = $this->registrarRecaudacionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Recaudación registrada exitosamente', 'idRecaudacion' => $idRecaudacion], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/recaudaciones",
     *     summary="Obtener recaudaciones con filtros",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="fechaInicio", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="fechaFin", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="idMaquina", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="tipoComercio", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=100)),
     *     @OA\Parameter(name="offset", in="query", required=false, @OA\Schema(type="integer", default=0)),
     *     @OA\Response(response=200, description="Lista de recaudaciones")
     * )
     */
    public function obtenerRecaudaciones(Request $request): Response
    {
        $query  = new ObtenerRecaudacionesQuery(
            $request->query('fechaInicio'), $request->query('fechaFin'),
            $request->query('idMaquina'), $request->query('tipoComercio'),
            (int) ($request->query('limit') ?? 100), (int) ($request->query('offset') ?? 0)
        );
        $result = $this->obtenerRecaudacionesHandler->handle($query);

        return (new Response())->json(['success' => true, 'recaudaciones' => $result['recaudaciones'], 'total' => $result['total']]);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/recaudaciones/{uuid}",
     *     summary="Obtener una recaudación por ID",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Datos de la recaudación"),
     *     @OA\Response(response=404, description="Recaudación no encontrada")
     * )
     */
    public function obtenerRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $query  = new ObtenerRecaudacionPorIdQuery($idRecaudacion);
        $result = $this->obtenerRecaudacionPorIdHandler->handle($query);

        return (new Response())->json(['success' => true, 'recaudacion' => $result['recaudacion']]);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/resumen-recaudaciones",
     *     summary="Obtener resumen agregado de recaudaciones",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Resumen de recaudaciones")
     * )
     */
    public function obtenerResumenRecaudaciones(Request $request): Response
    {
        $limit   = $request->query('limit') ? (int) $request->query('limit') : null;
        $query   = new ObtenerResumenRecaudacionesQuery($limit);
        $resumen = $this->obtenerResumenRecaudacionesHandler->handle($query);

        return (new Response())->json(['success' => true, 'resumen' => $resumen]);
    }

    /**
     * @OA\Put(
     *     path="/v1/contabilidad/actualizar-recaudacion",
     *     summary="Actualizar una recaudación existente",
     *     tags={"Contabilidad"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idRecaudacion","idMaquina","montoTotal","porcentajeComercio"},
     *             @OA\Property(property="idRecaudacion", type="string"),
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="montoTotal", type="number"),
     *             @OA\Property(property="porcentajeComercio", type="number"),
     *             @OA\Property(property="tipoComercio", type="string"),
     *             @OA\Property(property="detalle", type="string"),
     *             @OA\Property(property="fecha", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Recaudación actualizada"),
     *     @OA\Response(response=400, description="Datos incompletos")
     * )
     */
    public function actualizarRecaudacion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idRecaudacion'], $data['idMaquina'], $data['montoTotal'], $data['porcentajeComercio'])) {
            throw new DomainException('Datos incompletos para actualizar recaudación', 400);
        }

        $command = new ActualizarRecaudacionCommand(
            $data['idRecaudacion'], $data['idMaquina'], $data['tipoComercio'] ?? '',
            (float) $data['montoTotal'], (float) $data['porcentajeComercio'],
            $data['detalle'] ?? '', $data['fecha'] ?? ''
        );
        $this->actualizarRecaudacionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Recaudación actualizada correctamente']);
    }

    /**
     * @OA\Delete(
     *     path="/v1/contabilidad/eliminar-recaudacion/{uuid}",
     *     summary="Eliminar una recaudación",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Recaudación eliminada"),
     *     @OA\Response(response=404, description="No encontrada")
     * )
     */
    public function eliminarRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $command = new EliminarRecaudacionCommand($idRecaudacion);
        $this->eliminarRecaudacionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Recaudación eliminada correctamente']);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/maquinas-recaudacion",
     *     summary="Obtener máquinas disponibles para recaudación",
     *     tags={"Contabilidad"},
     *     @OA\Response(response=200, description="Lista de máquinas")
     * )
     */
    public function obtenerMaquinasRecaudacion(Request $request): Response
    {
        $query   = new ObtenerMaquinasRecaudacionQuery();
        $maquinas = $this->obtenerMaquinasRecaudacionHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/maquinas-operativas-por-comercio",
     *     summary="Obtener máquinas operativas filtradas por comercio",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="idComercio", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de máquinas operativas"),
     *     @OA\Response(response=400, description="ID de comercio requerido")
     * )
     */
    public function obtenerMaquinasOperativasPorComercio(Request $request): Response
    {
        $idComercio = $request->query('idComercio');
        if (!$idComercio) {
            throw new DomainException('ID de comercio requerido', 400);
        }

        $query   = new ObtenerMaquinasOperativasPorComercioQuery($idComercio);
        $maquinas = $this->obtenerMaquinasOperativasPorComercioHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/comercio-recaudacion/{uuid}",
     *     summary="Obtener datos de comercio para recaudación",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Datos del comercio")
     * )
     */
    public function obtenerComercioRecaudacion(Request $request, string $idComercio): Response
    {
        $query   = new ObtenerComercioRecaudacionQuery($idComercio);
        $comercio = $this->obtenerComercioRecaudacionHandler->handle($query);

        return (new Response())->json(['success' => true, 'comercio' => $comercio]);
    }

    /**
     * @OA\Post(
     *     path="/v1/contabilidad/guardar-informe",
     *     summary="Guardar informe de recaudación",
     *     tags={"Contabilidad"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idRecaudacion","ciUsuario","nombreMaquina","idComercio","nombreComercio","direccionComercio","telefonoComercio","montoTotal"},
     *             @OA\Property(property="idRecaudacion", type="string"),
     *             @OA\Property(property="ciUsuario", type="string"),
     *             @OA\Property(property="nombreMaquina", type="string"),
     *             @OA\Property(property="idComercio", type="string"),
     *             @OA\Property(property="nombreComercio", type="string"),
     *             @OA\Property(property="direccionComercio", type="string"),
     *             @OA\Property(property="telefonoComercio", type="string"),
     *             @OA\Property(property="montoTotal", type="number"),
     *             @OA\Property(property="componentes", type="array", @OA\Items(type="object"), nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Informe guardado exitosamente"),
     *     @OA\Response(response=400, description="Datos incompletos")
     * )
     */
    public function guardarInforme(Request $request): Response
    {
        $data     = $request->json();
        $required = ['idRecaudacion', 'ciUsuario', 'nombreMaquina', 'idComercio', 'nombreComercio', 'direccionComercio', 'telefonoComercio', 'montoTotal'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $command  = new GuardarInformeCommand(
            $data['idRecaudacion'], $data['ciUsuario'], $data['nombreMaquina'],
            $data['idComercio'], $data['nombreComercio'], $data['direccionComercio'],
            $data['telefonoComercio'], (float) $data['montoTotal'], $data['componentes'] ?? null
        );
        $idInforme = $this->guardarInformeHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Informe guardado exitosamente', 'idInforme' => $idInforme], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/contabilidad/informe/{uuid}",
     *     summary="Obtener informe asociado a una recaudación",
     *     tags={"Contabilidad"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Informe y componentes asociados"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function obtenerInformePorRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $query  = new ObtenerInformePorRecaudacionQuery($idRecaudacion);
        $result = $this->obtenerInformePorRecaudacionHandler->handle($query);

        return (new Response())->json(['success' => true, 'informe' => $result['informe'], 'componentes' => $result['componentes']]);
    }
}