<?php
/**
 * maquinas_recreativas - Controlador de Comentarios
 *
 * Maneja las operaciones CRUD de comentarios en reportes.
 *
 * @package maquinas_recreativas\Interfaces\Http\Controllers
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteQuery;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class ComentarioController
{
    private CrearComentarioHandler $crearComentarioHandler;
    private ObtenerComentariosPorReporteHandler $obtenerComentariosHandler;

    public function __construct(
        CrearComentarioHandler $crearComentarioHandler,
        ObtenerComentariosPorReporteHandler $obtenerComentariosHandler
    ) {
        $this->crearComentarioHandler = $crearComentarioHandler;
        $this->obtenerComentariosHandler = $obtenerComentariosHandler;
    }

    /**
     * Crear un nuevo comentario
     * @route POST /v1/comentarios
     */
    public function create(Request $request): Response
    {
        $data = $request->json();

        if (!isset($data['idReporte'], $data['comentario'])) {
            throw new DomainException('Faltan datos requeridos: idReporte, comentario', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new CrearComentarioCommand(
            $data['idReporte'],
            $userId,
            $data['comentario']
        );

        $idComentario = $this->crearComentarioHandler->handle($command);

        return (new Response())->json([
            'success' => true,
            'message' => 'Comentario creado exitosamente',
            'id' => $idComentario
        ], 201);
    }

    /**
     * Obtener comentarios por reporte
     * @route GET /v1/comentarios/reporte/{uuid}
     */
    public function getByReporte(Request $request, string $idReporte): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query = new ObtenerComentariosPorReporteQuery($idReporte, $userId);
        $comentarios = $this->obtenerComentariosHandler->handle($query);

        return (new Response())->json([
            'success' => true,
            'comentarios' => $comentarios
        ]);
    }
}