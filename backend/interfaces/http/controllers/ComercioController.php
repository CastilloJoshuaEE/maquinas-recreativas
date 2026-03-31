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

class ComercioController
{
    private ObtenerComerciosHandler $obtenerComerciosHandler;
    private RegistrarComercioHandler $registrarComercioHandler;

    public function __construct(
        ObtenerComerciosHandler $obtenerComerciosHandler,
        RegistrarComercioHandler $registrarComercioHandler
    ) {
        $this->obtenerComerciosHandler  = $obtenerComerciosHandler;
        $this->registrarComercioHandler = $registrarComercioHandler;
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
        $data     = $request->json();
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

        $data    = ValidationHelper::sanitizeInput($data);
        $command = new RegistrarComercioCommand(
            $data['nombre'], $data['tipo'], $data['direccion'],
            $data['telefono'], $_SESSION['ID_Usuario'] ?? 'system'
        );

        $comercio = $this->registrarComercioHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Comercio registrado correctamente', 'idComercio' => $comercio->getId()], 201);
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
        $filtros = [];

        if ($request->query('nombre')) {
            $filtros['nombre'] = ValidationHelper::sanitizeInput($request->query('nombre'));
        }

        if ($request->query('tipo')) {
            $tipo = $request->query('tipo');
            if (!in_array($tipo, ['Minorista', 'Mayorista'])) {
                throw new DomainException('Tipo de comercio no válido', 400);
            }
            $filtros['tipo'] = $tipo;
        }

        $pagina   = (int) ($request->query('pagina') ?? 1);
        $porPagina = (int) ($request->query('por_pagina') ?? ITEMS_POR_PAGINA);

        if ($porPagina > MAX_ITEMS_POR_PAGINA) {
            $porPagina = MAX_ITEMS_POR_PAGINA;
        }

        $query  = new ObtenerComerciosQuery($filtros, $pagina, $porPagina, $request->query('ordenar_por') ?? 'nombre', $request->query('direccion') ?? 'ASC');
        $result = $this->obtenerComerciosHandler->handle($query);

        return (new Response())->json($result);
    }
}