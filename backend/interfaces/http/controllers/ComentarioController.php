<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

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
        $this->crearComentarioHandler    = $crearComentarioHandler;
        $this->obtenerComentariosHandler = $obtenerComentariosHandler;
    }

    /**
     * @OA\Post(
     *     path="/v1/comentarios",
     *     summary="Crear un nuevo comentario en un reporte",
     *     tags={"Comentarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idReporte","comentario"},
     *             @OA\Property(property="idReporte", type="string", format="uuid"),
     *             @OA\Property(property="comentario", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comentario creado exitosamente"),
     *     @OA\Response(response=400, description="Faltan datos requeridos"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
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

        $command     = new CrearComentarioCommand($data['idReporte'], $userId, $data['comentario']);
        $idComentario = $this->crearComentarioHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Comentario creado exitosamente', 'id' => $idComentario], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/comentarios/reporte/{uuid}",
     *     summary="Obtener comentarios de un reporte",
     *     tags={"Comentarios"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de comentarios"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function getByReporte(Request $request, string $idReporte): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query       = new ObtenerComentariosPorReporteQuery($idReporte, $userId);
        $comentarios = $this->obtenerComentariosHandler->handle($query);

        return (new Response())->json(['success' => true, 'comentarios' => $comentarios]);
    }
}