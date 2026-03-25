<?php
/**
 * Controlador para operaciones específicas de técnicos ensambladores.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaCommand;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

/**
 * Class TecnicoEnsambladorController
 */
class TecnicoEnsambladorController
{
    private ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerMaquinasHandler;
    private RegistrarMontajeHandler $registrarMontajeHandler;
    private GenerarPlacaHandler $generarPlacaHandler;

    public function __construct(
        ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerMaquinasHandler,
        RegistrarMontajeHandler $registrarMontajeHandler,
        GenerarPlacaHandler $generarPlacaHandler
    ) {
        $this->obtenerMaquinasHandler = $obtenerMaquinasHandler;
        $this->registrarMontajeHandler = $registrarMontajeHandler;
        $this->generarPlacaHandler = $generarPlacaHandler;
    }

    /**
     * Obtener máquinas asignadas al técnico ensamblador.
     * @route GET /tecnico/ensamblador/maquinas
     */
    public function obtenerMaquinasAsignadas(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $query = new ObtenerMaquinasPorTecnicoEnsambladorQuery($tecnicoId);
        $maquinas = $this->obtenerMaquinasHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * Registrar montaje de un componente en una máquina.
     * @route POST /tecnico/ensamblador/montaje
     */
    public function registrarMontaje(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['idComponente'])) {
            throw new DomainException('Faltan datos obligatorios', 400);
        }

        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $command = new RegistrarMontajeCommand(
            $data['idMaquina'],
            $data['idComponente'],
            $tecnicoId,
            $data['detalle'] ?? null
        );
        $this->registrarMontajeHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Montaje registrado']);
    }

    /**
     * Generar una placa (componente logístico).
     * @route POST /tecnico/ensamblador/generar-placa
     */
    public function generarPlaca(Request $request): Response
    {
        $tecnicoId = $_SESSION['ID_Usuario'] ?? null;
        if (!$tecnicoId) throw new DomainException('No autorizado', 401);

        $command = new GenerarPlacaCommand($tecnicoId);
        $result = $this->generarPlacaHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'placa' => $result['placa'],
            'idComponente' => $result['idComponente']
        ]);
    }
}