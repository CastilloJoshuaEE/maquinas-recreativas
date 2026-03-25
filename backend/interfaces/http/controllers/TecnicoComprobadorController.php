<?php
/**
 * Controlador para técnicos comprobadores.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class TecnicoComprobadorController
{
    private ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerMaquinasHandler;
    private MandarADistribucionHandler $mandarADistribucionHandler;
    private MandarAReensamblarHandler $mandarAReensamblarHandler;

    public function __construct(
        ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerMaquinasHandler,
        MandarADistribucionHandler $mandarADistribucionHandler,
        MandarAReensamblarHandler $mandarAReensamblarHandler
    ) {
        $this->obtenerMaquinasHandler = $obtenerMaquinasHandler;
        $this->mandarADistribucionHandler = $mandarADistribucionHandler;
        $this->mandarAReensamblarHandler = $mandarAReensamblarHandler;
    }

    /**
     * Obtener máquinas pendientes de comprobación.
     */
    public function obtenerMaquinas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $query = new ObtenerMaquinasPorTecnicoComprobadorQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);
        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * Aprobar máquina y enviarla a distribución.
     */
    public function aprobarYEnviarADistribucion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $command = new MandarADistribucionCommand($data['idMaquina'], $tecnicoId, $data['mensaje']);
        $this->mandarADistribucionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a distribución']);
    }

    /**
     * Rechazar máquina y enviarla a reensamblar.
     */
    public function rechazarYEnviarAReensamblar(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $command = new MandarAReensamblarCommand($data['idMaquina'], $tecnicoId, $data['mensaje']);
        $this->mandarAReensamblarHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a reensamblar']);
    }
}