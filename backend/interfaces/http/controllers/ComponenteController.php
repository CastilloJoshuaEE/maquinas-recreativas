<?php
/**
 * maquinas_recreativas - Controlador de Componentes
 *
 * Maneja las operaciones CRUD de componentes.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Componente\UsarComponenteCommand;
use maquinas_recreativas\Application\Commands\Componente\UsarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponenteCommand;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponenteHandler;
use maquinas_recreativas\Application\Commands\Componente\AsignarCarcasaCommand;
use maquinas_recreativas\Application\Commands\Componente\AsignarCarcasaHandler;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponentesCancelacionCommand;
use maquinas_recreativas\Application\Commands\Componente\LiberarComponentesCancelacionHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesDisponiblesQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesDisponiblesHandler;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesEnUsoQuery;
use maquinas_recreativas\Application\Queries\Componente\ObtenerComponentesEnUsoHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class ComponenteController
{
    private ObtenerComponentesHandler $obtenerComponentesHandler;
    private ObtenerComponentesDisponiblesHandler $obtenerComponentesDisponiblesHandler;
    private UsarComponenteHandler $usarComponenteHandler;
    private LiberarComponenteHandler $liberarComponenteHandler;
    private AsignarCarcasaHandler $asignarCarcasaHandler;
    private LiberarComponentesCancelacionHandler $liberarComponentesCancelacionHandler;
    private ObtenerComponentesEnUsoHandler $obtenerComponentesEnUsoHandler;

    public function __construct(
        ObtenerComponentesHandler $obtenerComponentesHandler,
        ObtenerComponentesDisponiblesHandler $obtenerComponentesDisponiblesHandler,
        UsarComponenteHandler $usarComponenteHandler,
        LiberarComponenteHandler $liberarComponenteHandler,
        AsignarCarcasaHandler $asignarCarcasaHandler,
        LiberarComponentesCancelacionHandler $liberarComponentesCancelacionHandler,
        ObtenerComponentesEnUsoHandler $obtenerComponentesEnUsoHandler
    ) {
        $this->obtenerComponentesHandler = $obtenerComponentesHandler;
        $this->obtenerComponentesDisponiblesHandler = $obtenerComponentesDisponiblesHandler;
        $this->usarComponenteHandler = $usarComponenteHandler;
        $this->liberarComponenteHandler = $liberarComponenteHandler;
        $this->asignarCarcasaHandler = $asignarCarcasaHandler;
        $this->liberarComponentesCancelacionHandler = $liberarComponentesCancelacionHandler;
        $this->obtenerComponentesEnUsoHandler = $obtenerComponentesEnUsoHandler;
    }

    /**
     * Obtener componentes con paginación
     * @route GET /v1/componentes
     */
    public function obtenerComponentes(Request $request): Response
    {
        $tipo = $request->query('tipo');
        $limit = (int)($request->query('limit') ?? 10);
        $offset = (int)($request->query('offset') ?? 0);

        $query = new ObtenerComponentesQuery($tipo, $limit, $offset);
        $result = $this->obtenerComponentesHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'componentes' => $result['componentes'],
            'total' => $result['total']
        ]);
    }

    /**
     * Obtener componentes disponibles
     * @route GET /v1/componentes/disponibles
     */
    public function obtenerComponentesDisponibles(Request $request): Response
    {
        $tipo = $request->query('tipo');
        $query = new ObtenerComponentesDisponiblesQuery($tipo);
        $componentes = $this->obtenerComponentesDisponiblesHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'componentes' => $componentes
        ]);
    }

    /**
     * Usar/asignar un componente
     * @route POST /v1/componentes/usar
     */
    public function usarComponente(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new UsarComponenteCommand(
            $data['idComponente'],
            $userId,
            $data['idMaquina'] ?? null
        );
        $this->usarComponenteHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Componente asignado correctamente'
        ]);
    }

    /**
     * Liberar un componente
     * @route POST /v1/componentes/liberar
     */
    public function liberarComponente(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new LiberarComponenteCommand($data['idComponente'], $userId);
        $this->liberarComponenteHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Componente liberado correctamente'
        ]);
    }

    /**
     * Asignar carcasa a un técnico
     * @route POST /v1/componentes/asignar-carcasa
     */
    public function asignarCarcasa(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idComponente'])) {
            throw new DomainException('ID de componente requerido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new AsignarCarcasaCommand($data['idComponente'], $userId);
        $this->asignarCarcasaHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Carcasa asignada correctamente'
        ]);
    }

    /**
     * Liberar componentes por cancelación
     * @route POST /v1/componentes/liberar-cancelacion
     */
    public function liberarComponentesCancelacion(Request $request): Response
    {
        $data = $request->json();

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new LiberarComponentesCancelacionCommand(
            $data['idPlaca'] ?? null,
            $data['idCarcasa'] ?? null,
            $userId
        );
        $result = $this->liberarComponentesCancelacionHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => $result['message']
        ]);
    }

    /**
     * Obtener componentes en uso por un usuario
     * @route GET /v1/componentes/en-uso/{uuid}
     */
    public function obtenerComponentesEnUso(Request $request, string $idUsuario): Response
    {
        $idMaquina = $request->query('idMaquina');
        $query = new ObtenerComponentesEnUsoQuery($idUsuario, $idMaquina);
        $componentes = $this->obtenerComponentesEnUsoHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'componentes' => $componentes
        ]);
    }
}