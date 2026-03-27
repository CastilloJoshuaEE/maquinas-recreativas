<?php
/**
 * maquinas_recreativas - Controlador de Informes/Recaudación
 *
 * Maneja las operaciones de recaudación e informes.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

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
        $this->registrarRecaudacionHandler = $registrarRecaudacionHandler;
        $this->actualizarRecaudacionHandler = $actualizarRecaudacionHandler;
        $this->eliminarRecaudacionHandler = $eliminarRecaudacionHandler;
        $this->guardarInformeHandler = $guardarInformeHandler;
        $this->obtenerRecaudacionesHandler = $obtenerRecaudacionesHandler;
        $this->obtenerResumenRecaudacionesHandler = $obtenerResumenRecaudacionesHandler;
        $this->obtenerRecaudacionPorIdHandler = $obtenerRecaudacionPorIdHandler;
        $this->obtenerMaquinasRecaudacionHandler = $obtenerMaquinasRecaudacionHandler;
        $this->obtenerMaquinasOperativasPorComercioHandler = $obtenerMaquinasOperativasPorComercioHandler;
        $this->obtenerComercioRecaudacionHandler = $obtenerComercioRecaudacionHandler;
        $this->obtenerInformePorRecaudacionHandler = $obtenerInformePorRecaudacionHandler;
    }

    /**
     * Registrar una nueva recaudación
     * @route POST /v1/contabilidad/registrar-recaudacion
     */
    public function registrarRecaudacion(Request $request): Response
    {
        $data = $request->json();

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

        $command = new RegistrarRecaudacionCommand(
            $data['idMaquina'],
            $userId,
            $data['tipoComercio'],
            (float) $data['montoTotal'],
            (float) $data['porcentajeComercio'],
            $data['detalle'] ?? ''
        );

        $idRecaudacion = $this->registrarRecaudacionHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Recaudación registrada exitosamente',
            'idRecaudacion' => $idRecaudacion
        ], 201);
    }

    /**
     * Obtener recaudaciones con filtros
     * @route GET /v1/contabilidad/recaudaciones
     */
    public function obtenerRecaudaciones(Request $request): Response
    {
        $query = new ObtenerRecaudacionesQuery(
            $request->query('fechaInicio'),
            $request->query('fechaFin'),
            $request->query('idMaquina'),
            $request->query('tipoComercio'),
            (int)($request->query('limit') ?? 100),
            (int)($request->query('offset') ?? 0)
        );

        $result = $this->obtenerRecaudacionesHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'recaudaciones' => $result['recaudaciones'],
            'total' => $result['total']
        ]);
    }

    /**
     * Obtener recaudación por ID
     * @route GET /v1/contabilidad/recaudaciones/{uuid}
     */
    public function obtenerRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $query = new ObtenerRecaudacionPorIdQuery($idRecaudacion);
        $result = $this->obtenerRecaudacionPorIdHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'recaudacion' => $result['recaudacion']
        ]);
    }

    /**
     * Obtener resumen de recaudaciones
     * @route GET /v1/contabilidad/resumen-recaudaciones
     */
    public function obtenerResumenRecaudaciones(Request $request): Response
    {
        $limit = $request->query('limit') ? (int) $request->query('limit') : null;
        $query = new ObtenerResumenRecaudacionesQuery($limit);
        $resumen = $this->obtenerResumenRecaudacionesHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'resumen' => $resumen
        ]);
    }

    /**
     * Actualizar recaudación
     * @route PUT /v1/contabilidad/actualizar-recaudacion
     */
    public function actualizarRecaudacion(Request $request): Response
    {
        $data = $request->json();

        if (!isset($data['idRecaudacion'], $data['idMaquina'], $data['montoTotal'], $data['porcentajeComercio'])) {
            throw new DomainException('Datos incompletos para actualizar recaudación', 400);
        }

        $command = new ActualizarRecaudacionCommand(
            $data['idRecaudacion'],
            $data['idMaquina'],
            $data['tipoComercio'] ?? '',
            (float) $data['montoTotal'],
            (float) $data['porcentajeComercio'],
            $data['detalle'] ?? '',
            $data['fecha'] ?? ''
        );

        $this->actualizarRecaudacionHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Recaudación actualizada correctamente'
        ]);
    }

    /**
     * Eliminar recaudación
     * @route DELETE /v1/contabilidad/eliminar-recaudacion/{uuid}
     */
    public function eliminarRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $command = new EliminarRecaudacionCommand($idRecaudacion);
        $this->eliminarRecaudacionHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Recaudación eliminada correctamente'
        ]);
    }

    /**
     * Obtener máquinas para recaudación
     * @route GET /v1/contabilidad/maquinas-recaudacion
     */
    public function obtenerMaquinasRecaudacion(Request $request): Response
    {
        $query = new ObtenerMaquinasRecaudacionQuery();
        $maquinas = $this->obtenerMaquinasRecaudacionHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'maquinas' => $maquinas
        ]);
    }

    /**
     * Obtener máquinas operativas por comercio
     * @route GET /v1/contabilidad/maquinas-operativas-por-comercio
     */
    public function obtenerMaquinasOperativasPorComercio(Request $request): Response
    {
        $idComercio = $request->query('idComercio');
        if (!$idComercio) {
            throw new DomainException('ID de comercio requerido', 400);
        }

        $query = new ObtenerMaquinasOperativasPorComercioQuery($idComercio);
        $maquinas = $this->obtenerMaquinasOperativasPorComercioHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'maquinas' => $maquinas
        ]);
    }

    /**
     * Obtener comercio para recaudación
     * @route GET /v1/contabilidad/comercio-recaudacion/{uuid}
     */
    public function obtenerComercioRecaudacion(Request $request, string $idComercio): Response
    {
        $query = new ObtenerComercioRecaudacionQuery($idComercio);
        $comercio = $this->obtenerComercioRecaudacionHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'comercio' => $comercio
        ]);
    }

    /**
     * Guardar informe de recaudación
     * @route POST /v1/contabilidad/guardar-informe
     */
    public function guardarInforme(Request $request): Response
    {
        $data = $request->json();

        $required = ['idRecaudacion', 'ciUsuario', 'nombreMaquina', 'idComercio', 'nombreComercio', 'direccionComercio', 'telefonoComercio', 'montoTotal'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $command = new GuardarInformeCommand(
            $data['idRecaudacion'],
            $data['ciUsuario'],
            $data['nombreMaquina'],
            $data['idComercio'],
            $data['nombreComercio'],
            $data['direccionComercio'],
            $data['telefonoComercio'],
            (float) $data['montoTotal'],
            $data['componentes'] ?? null
        );

        $idInforme = $this->guardarInformeHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Informe guardado exitosamente',
            'idInforme' => $idInforme
        ], 201);
    }

    /**
     * Obtener informe por recaudación
     * @route GET /v1/contabilidad/informe/{uuid}
     */
    public function obtenerInformePorRecaudacion(Request $request, string $idRecaudacion): Response
    {
        $query = new ObtenerInformePorRecaudacionQuery($idRecaudacion);
        $result = $this->obtenerInformePorRecaudacionHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'informe' => $result['informe'],
            'componentes' => $result['componentes']
        ]);
    }
}