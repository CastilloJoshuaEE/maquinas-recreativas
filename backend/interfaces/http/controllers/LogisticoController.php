<?php
/**
 * Controlador para usuarios de logística.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

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
        $this->obtenerInformesDistribucionHandler = $obtenerInformesDistribucionHandler;
        $this->darMantenimientoHandler = $darMantenimientoHandler;
    }

    /**
     * Obtener máquinas listas para distribución.
     */
    public function obtenerMaquinasParaDistribucion(Request $request): Response
    {
        $query = new ObtenerMaquinasParaDistribucionQuery();
        $maquinas = $this->obtenerMaquinasParaDistribucionHandler->handle($query);
        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * Obtener informes de distribución.
     */
    public function obtenerInformesDistribucion(Request $request): Response
    {
        $filters = [
            'estado' => $request->query('estado'),
            'ID_Comercio' => $request->query('idComercio'),
            'ID_Maquina' => $request->query('idMaquina'),
            'fecha_inicio' => $request->query('fechaInicio'),
            'fecha_fin' => $request->query('fechaFin')
        ];
        $query = new ObtenerInformesDistribucionQuery(
            $filters['estado'],
            $filters['ID_Comercio'],
            $filters['ID_Maquina'],
            $filters['fecha_inicio'],
            $filters['fecha_fin']
        );
        $informes = $this->obtenerInformesDistribucionHandler->handle($query);
        return (new Response())->json(['success' => true, 'informes' => $informes]);
    }

    /**
     * Solicitar mantenimiento de una máquina.
     */
    public function solicitarMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $logisticaId = $_SESSION['ID_Usuario'] ?? null;
        if (!$logisticaId) throw new DomainException('No autorizado', 401);

        $command = new DarMantenimientoCommand($data['idMaquina'], $data['mensaje'], $logisticaId);
        $this->darMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento solicitado']);
    }
}