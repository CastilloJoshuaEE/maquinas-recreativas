<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\CrearComentarioHandler;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteQuery;
use maquinas_recreativas\Application\Queries\Comentario\ObtenerComentariosPorReporteHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
use maquinas_recreativas\Application\Commands\Comentario\EditarComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\EditarComentarioHandler;
use maquinas_recreativas\Application\Commands\Comentario\EliminarComentarioCommand;
use maquinas_recreativas\Application\Commands\Comentario\EliminarComentarioHandler;
class ComentarioController
{
    private CrearComentarioHandler $crearComentarioHandler;
    private ObtenerComentariosPorReporteHandler $obtenerComentariosHandler;
private EditarComentarioHandler $editarComentarioHandler;
    private EliminarComentarioHandler $eliminarComentarioHandler;
    public function __construct(
        CrearComentarioHandler $crearComentarioHandler,
        ObtenerComentariosPorReporteHandler $obtenerComentariosHandler
        ,EditarComentarioHandler $editarComentarioHandler,
        EliminarComentarioHandler $eliminarComentarioHandler
        ) {
        $this->crearComentarioHandler    = $crearComentarioHandler;
        $this->obtenerComentariosHandler = $obtenerComentariosHandler;
        $this->editarComentarioHandler = $editarComentarioHandler;
        $this->eliminarComentarioHandler = $eliminarComentarioHandler;
    }

    #[OA\Post(
        path: "/v1/comentarios",
        summary: "Crear un nuevo comentario en un reporte",
        tags: ["Comentarios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idReporte", "comentario"],
                properties: [
                    new OA\Property(property: "idReporte", type: "string", format: "uuid"),
                    new OA\Property(property: "comentario", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Comentario creado exitosamente"),
            new OA\Response(response: 400, description: "Faltan datos requeridos"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function create(Request $request): Response
    {
        $data = $request->json();

        if (!isset($data['idReporte'], $data['comentario'])) {
            throw new DomainException('Faltan datos requeridos: idReporte, comentario', 400);
        }

        if (!ValidationHelper::isValidUUID($data['idReporte'])) {
            throw new DomainException('ID de reporte inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new CrearComentarioCommand($data['idReporte'], $userId, $data['comentario']);
        $idComentario = $this->crearComentarioHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Comentario creado exitosamente', 'id' => $idComentario], 201);
        return $response;
    }

    #[OA\Get(
        path: "/v1/comentarios/reporte/{uuid}",
        summary: "Obtener comentarios de un reporte",
        tags: ["Comentarios"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de comentarios"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
  public function getByReporte(Request $request, string $idReporte): Response
    {
        if (!ValidationHelper::isValidUUID($idReporte)) {
            throw new DomainException('ID de reporte inválido', 400);
        }
        
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }
        
        $query = new ObtenerComentariosPorReporteQuery($idReporte, $userId);
        $comentarios = $this->obtenerComentariosHandler->handle($query);
        
        // Añadir flags de permisos
        $ahora = time();
        foreach ($comentarios as &$comentario) {
            $fechaComentario = strtotime($comentario['fecha_hora']);
            $diferenciaMinutos = ($ahora - $fechaComentario) / 60;
            $esPropio = $comentario['ID_Usuario_Emisor'] === $userId;
            
            $comentario['puede_editar'] = $esPropio && $diferenciaMinutos <= 15;
            $comentario['puede_eliminar'] = $esPropio && $diferenciaMinutos <= 15;
            $comentario['es_propio'] = $esPropio;
            $comentario['editado'] = isset($comentario['fecha_edicion']) && $comentario['fecha_edicion'] !== null;
        }
        
        $response = new Response();
        $response->json(['success' => true, 'data' => $comentarios]);
        return $response;
    }


    /**
     * Editar un comentario
     */
    public function update(Request $request, string $idComentario): Response
    {
        $data = $request->json();
        
        if (!isset($data['comentario'])) {
            throw new DomainException('El comentario es requerido', 400);
        }
        
        if (!ValidationHelper::isValidUUID($idComentario)) {
            throw new DomainException('ID de comentario inválido', 400);
        }
        
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }
        
        $command = new EditarComentarioCommand($idComentario, $userId, $data['comentario']);
        $success = $this->editarComentarioHandler->handle($command);
        
        $response = new Response();
        $response->json(['success' => $success, 'message' => 'Comentario editado exitosamente']);
        return $response;
    }
    
    /**
     * Eliminar un comentario
     */
    public function delete(Request $request, string $idComentario): Response
    {
        if (!ValidationHelper::isValidUUID($idComentario)) {
            throw new DomainException('ID de comentario inválido', 400);
        }
        
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }
        
        $command = new EliminarComentarioCommand($idComentario, $userId);
        $success = $this->eliminarComentarioHandler->handle($command);
        
        $response = new Response();
        $response->json(['success' => $success, 'message' => 'Comentario eliminado exitosamente']);
        return $response;
    }
    
}