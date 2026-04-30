<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\RegistrarComercioHandler;
use maquinas_recreativas\Application\Queries\Comercio\ObtenerComerciosQuery;
use maquinas_recreativas\Application\Queries\Comercio\ObtenerComerciosHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;
use maquinas_recreativas\Application\Commands\Comercio\ActualizarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\ActualizarComercioHandler;
use maquinas_recreativas\Application\Commands\Comercio\EliminarComercioCommand;
use maquinas_recreativas\Application\Commands\Comercio\EliminarComercioHandler;

class ComercioController
{
    private ObtenerComerciosHandler $obtenerComerciosHandler;
    private RegistrarComercioHandler $registrarComercioHandler;
private ActualizarComercioHandler $actualizarComercioHandler;
private EliminarComercioHandler $eliminarComercioHandler;

    public function __construct(
         ObtenerComerciosHandler $obtenerComerciosHandler,
    RegistrarComercioHandler $registrarComercioHandler,
    ActualizarComercioHandler $actualizarComercioHandler,
    EliminarComercioHandler $eliminarComercioHandler
    ) {
            $this->obtenerComerciosHandler  = $obtenerComerciosHandler;
            $this->registrarComercioHandler = $registrarComercioHandler;
            $this->actualizarComercioHandler = $actualizarComercioHandler;
            $this->eliminarComercioHandler = $eliminarComercioHandler;
        }

    #[OA\Post(
        path: "/v1/comercios",
        summary: "Registrar un nuevo comercio",
        tags: ["Comercios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "tipo", "direccion", "telefono"],
                properties: [
                    new OA\Property(property: "nombre", type: "string"),
                    new OA\Property(property: "tipo", type: "string", enum: ["Minorista", "Mayorista"]),
                    new OA\Property(property: "direccion", type: "string"),
                    new OA\Property(property: "telefono", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Comercio registrado correctamente"),
            new OA\Response(response: 400, description: "Datos inválidos")
        ]
    )]
    public function register(Request $request): Response
    {
        $data = $request->json();
        $required = ['nombre', 'tipo', 'direccion', 'telefono'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $tiposPermitidos = ['Minorista', 'Mayorista'];
        if (!in_array($data['tipo'], $tiposPermitidos)) {
            throw new DomainException('Tipo de comercio no válido. Debe ser Minorista o Mayorista', 400);
        }

        if (!preg_match('/^[0-9+\-\s]+$/', $data['telefono'])) {
            throw new DomainException('Formato de teléfono inválido', 400);
        }

        $data = ValidationHelper::sanitizeInput($data);
        $command = new RegistrarComercioCommand(
            $data['nombre'], $data['tipo'], $data['direccion'],
            $data['telefono'], $_SESSION['ID_Usuario'] ?? 'system'
        );

        $comercio = $this->registrarComercioHandler->handle($command);

        // : Crear la respuesta primero, luego retornarla
        $response = new Response();
        $response->json(['success' => true, 'message' => 'Comercio registrado correctamente', 'idComercio' => $comercio->getId()], 201);
        return $response;
    }

    #[OA\Get(
        path: "/v1/comercios",
        summary: "Obtener lista de comercios con filtros opcionales",
        tags: ["Comercios"],
        parameters: [
            new OA\Parameter(name: "nombre", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "tipo", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["Minorista", "Mayorista"])),
            new OA\Parameter(name: "pagina", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "por_pagina", in: "query", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "ordenar_por", in: "query", required: false, schema: new OA\Schema(type: "string", default: "nombre")),
            new OA\Parameter(name: "direccion", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["ASC", "DESC"], default: "ASC"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de comercios"),
            new OA\Response(response: 400, description: "Tipo de comercio no válido")
        ]
    )]
public function obtenerComercios(Request $request): Response
{
    try {
        $query = new ObtenerComerciosQuery();
        $comercios = $this->obtenerComerciosHandler->handle($query);
        
        error_log("obtenerComercios: " . count($comercios) . " comercios encontrados");
        
        $response = new Response();
        // Devolver estructura clara y consistente
        $response->json([
            'success' => true,
            'comercios' => $comercios,
            'total' => count($comercios)
        ]);
        return $response;
    } catch (\Exception $e) {
        error_log("Error en obtenerComercios: " . $e->getMessage());
        $response = new Response();
        $response->json([
            'success' => false,
            'message' => $e->getMessage(),
            'comercios' => []
        ], 500);
        return $response;
    }
}
#[OA\Put(
    path: "/v1/comercio/actualizar/{id}",
    summary: "Actualizar un comercio existente",
    tags: ["Comercios"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["nombre", "tipo", "direccion"],
            properties: [
                new OA\Property(property: "nombre", type: "string"),
                new OA\Property(property: "tipo", type: "string", enum: ["Minorista", "Mayorista"]),
                new OA\Property(property: "direccion", type: "string"),
                new OA\Property(property: "telefono", type: "string", nullable: true)
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Comercio actualizado correctamente"),
        new OA\Response(response: 400, description: "Datos inválidos"),
        new OA\Response(response: 404, description: "Comercio no encontrado")
    ]
)]
public function actualizar(Request $request, string $id): Response
{
    try {
        $data = $request->json();
        $required = ['nombre', 'tipo', 'direccion'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $tiposPermitidos = ['Minorista', 'Mayorista'];
        if (!in_array($data['tipo'], $tiposPermitidos)) {
            throw new DomainException('Tipo de comercio no válido', 400);
        }

        $command = new ActualizarComercioCommand(
            $id,
            $data['nombre'],
            $data['tipo'],
            $data['direccion'],
            $data['telefono'] ?? ''
        );

        $this->actualizarComercioHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Comercio actualizado correctamente']);
        return $response;
    } catch (DomainException $e) {
        $response = new Response();
        $response->json(['success' => false, 'message' => $e->getMessage()], 400);
        return $response;
    } catch (\Exception $e) {
        error_log("Error actualizando comercio: " . $e->getMessage());
        $response = new Response();
        $response->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        return $response;
    }
}

#[OA\Delete(
    path: "/v1/comercio/eliminar/{id}",
    summary: "Eliminar un comercio",
    tags: ["Comercios"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
    ],
    responses: [
        new OA\Response(response: 200, description: "Comercio eliminado correctamente"),
        new OA\Response(response: 400, description: "No se puede eliminar porque tiene máquinas asociadas"),
        new OA\Response(response: 404, description: "Comercio no encontrado")
    ]
)]
public function eliminar(Request $request, string $id): Response
{
    try {
        $command = new EliminarComercioCommand($id);
        $this->eliminarComercioHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Comercio eliminado correctamente']);
        return $response;
    } catch (DomainException $e) {
        $response = new Response();
        $response->json(['success' => false, 'message' => $e->getMessage()], 400);
        return $response;
    } catch (\Exception $e) {
        error_log("Error eliminando comercio: " . $e->getMessage());
        $response = new Response();
        $response->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        return $response;
    }
}



}