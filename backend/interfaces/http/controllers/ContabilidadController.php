<?php
/**
 * Controlador para usuarios de contabilidad.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionHandler;
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
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class ContabilidadController
{
    private RegistrarRecaudacionHandler $registrarRecaudacionHandler;
    private GuardarInformeHandler $guardarInformeHandler;
    private ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler;
    private ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler;
    private ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler;
    private ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler;
    private ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler;

    public function __construct(
        RegistrarRecaudacionHandler $registrarRecaudacionHandler,
        GuardarInformeHandler $guardarInformeHandler,
        ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler,
        ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler,
        ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler,
        ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler,
        ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler
    ) {
        $this->registrarRecaudacionHandler = $registrarRecaudacionHandler;
        $this->guardarInformeHandler = $guardarInformeHandler;
        $this->obtenerRecaudacionesHandler = $obtenerRecaudacionesHandler;
        $this->obtenerResumenRecaudacionesHandler = $obtenerResumenRecaudacionesHandler;
        $this->obtenerRecaudacionPorIdHandler = $obtenerRecaudacionPorIdHandler;
        $this->obtenerMaquinasRecaudacionHandler = $obtenerMaquinasRecaudacionHandler;
        $this->obtenerMaquinasOperativasPorComercioHandler = $obtenerMaquinasOperativasPorComercioHandler;
    }

    /**
     * Registrar una recaudación.
     */
    public function registrarRecaudacion(Request $request): Response
    {
        $data = $request->json();
        $required = ['ID_Maquina', 'Tipo_Comercio', 'Monto_Total', 'fecha'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo $field es requerido", 400);
            }
        }

        $usuarioId = $_SESSION['ID_Usuario'] ?? null;
        if (!$usuarioId) throw new DomainException('No autorizado', 401);

        $command = new RegistrarRecaudacionCommand(
            $data['ID_Maquina'],
            $usuarioId,
            $data['Tipo_Comercio'],
            (float) $data['Monto_Total'],
            (float) ($data['Porcentaje_Comercio'] ?? 0),
            $data['detalle'] ?? ''
        );
        $id = $this->registrarRecaudacionHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Recaudación registrada',
            'idRecaudacion' => $id
        ]);
    }

    /**
     * Guardar informe de recaudación.
     */
    public function guardarInforme(Request $request): Response
    {
        $data = $request->json();
        $required = [
            'ID_Recaudacion', 'CI_Usuario', 'Nombre_Maquina', 'ID_Comercio',
            'Nombre_Comercio', 'Direccion_Comercio', 'Telefono_Comercio', 'Monto_Total'
        ];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo $field es requerido", 400);
            }
        }

        $command = new GuardarInformeCommand(
            $data['ID_Recaudacion'],
            $data['CI_Usuario'],
            $data['Nombre_Maquina'],
            $data['ID_Comercio'],
            $data['Nombre_Comercio'],
            $data['Direccion_Comercio'],
            $data['Telefono_Comercio'],
            (float) $data['Monto_Total'],
            $data['componentes'] ?? null
        );
        $idInforme = $this->guardarInformeHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Informe guardado',
            'idInforme' => $idInforme
        ]);
    }

    /**
     * Obtener listado de recaudaciones.
     */
    public function obtenerRecaudaciones(Request $request): Response
    {
        $filters = [
            'fecha_inicio' => $request->query('fecha_inicio'),
            'fecha_fin' => $request->query('fecha_fin'),
            'ID_Maquina' => $request->query('idMaquina'),
            'Tipo_Comercio' => $request->query('tipoComercio')
        ];
        $query = new ObtenerRecaudacionesQuery(
            $filters['fecha_inicio'],
            $filters['fecha_fin'],
            $filters['ID_Maquina'],
            $filters['Tipo_Comercio']
        );
        $result = $this->obtenerRecaudacionesHandler->handle($query);
        return (new Response())->json($result);
    }

    /**
     * Obtener resumen de recaudaciones.
     */
    public function obtenerResumenRecaudaciones(Request $request): Response
    {
        $limit = $request->query('limit') ? (int) $request->query('limit') : null;
        $query = new ObtenerResumenRecaudacionesQuery($limit);
        $resumen = $this->obtenerResumenRecaudacionesHandler->handle($query);
        return (new Response())->json(['success' => true, 'resumen' => $resumen]);
    }

    /**
     * Obtener una recaudación por ID.
     */
    public function obtenerRecaudacionPorId(Request $request, string $id): Response
    {
        $query = new ObtenerRecaudacionPorIdQuery($id);
        $recaudacion = $this->obtenerRecaudacionPorIdHandler->handle($query);
        return (new Response())->json(['success' => true, 'recaudacion' => $recaudacion]);
    }

    /**
     * Obtener máquinas disponibles para recaudación.
     */
    public function obtenerMaquinasRecaudacion(Request $request): Response
    {
        $query = new ObtenerMaquinasRecaudacionQuery();
        $maquinas = $this->obtenerMaquinasRecaudacionHandler->handle($query);
        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * Obtener máquinas operativas por comercio.
     */
    public function obtenerMaquinasOperativasPorComercio(Request $request): Response
    {
        $idComercio = $request->query('ID_Comercio');
        if (!$idComercio) throw new DomainException('ID de comercio requerido', 400);
        $query = new ObtenerMaquinasOperativasPorComercioQuery($idComercio);
        $maquinas = $this->obtenerMaquinasOperativasPorComercioHandler->handle($query);
        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }
}