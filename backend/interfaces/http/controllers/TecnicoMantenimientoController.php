<?php
/**
 * Controlador para técnicos de mantenimiento.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

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
        $this->obtenerMaquinasHandler = $obtenerMaquinasHandler;
        $this->finalizarMantenimientoHandler = $finalizarMantenimientoHandler;
    }

    /**
     * Obtener máquinas asignadas para mantenimiento.
     */
    public function obtenerMaquinasAsignadas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $query = new ObtenerMaquinasPorTecnicoMantenimientoQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);
        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * Finalizar mantenimiento de una máquina.
     */
    public function finalizarMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['exito'], $data['mensaje'])) {
            throw new DomainException('ID de máquina, éxito y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $command = new FinalizarMantenimientoCommand(
            $data['idMaquina'],
            $tecnicoId,
            (bool) $data['exito'],
            $data['mensaje']
        );
        $this->finalizarMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento finalizado']);
    }
}