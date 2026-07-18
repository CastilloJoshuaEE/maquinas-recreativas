<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\RegistrarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\ActualizarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\ActualizarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\EliminarRecaudacionCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\EliminarRecaudacionHandler;
use maquinas_recreativas\Application\Commands\Recaudacion\GuardarInformeCommand;
use maquinas_recreativas\Application\Commands\Recaudacion\GuardarInformeHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionesQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerResumenRecaudacionesQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerResumenRecaudacionesHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionPorIdQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerRecaudacionPorIdHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasOperativasPorComercioQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerMaquinasOperativasPorComercioHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerComercioRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerComercioRecaudacionHandler;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerInformePorRecaudacionQuery;
use maquinas_recreativas\Application\Queries\Recaudacion\ObtenerInformePorRecaudacionHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class InformeController
{
    private RegistrarRecaudacionHandler $registrarRecaudacionHandler;
    private ActualizarRecaudacionHandler $actualizarRecaudacionHandler;
    private EliminarRecaudacionHandler $eliminarRecaudacionHandler;
    private GuardarInformeHandler $guardarInformeHandler;
    private ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler;
    private ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler;
    private ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler;
    private ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler;
    private ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler;
    private ObtenerComercioRecaudacionHandler $obtenerComercioRecaudacionHandler;
    private ObtenerInformePorRecaudacionHandler $obtenerInformePorRecaudacionHandler;

    public function __construct(
        RegistrarRecaudacionHandler $registrarRecaudacionHandler,
        ActualizarRecaudacionHandler $actualizarRecaudacionHandler,
        EliminarRecaudacionHandler $eliminarRecaudacionHandler,
        GuardarInformeHandler $guardarInformeHandler,
        ObtenerRecaudacionesHandler $obtenerRecaudacionesHandler,
        ObtenerResumenRecaudacionesHandler $obtenerResumenRecaudacionesHandler,
        ObtenerRecaudacionPorIdHandler $obtenerRecaudacionPorIdHandler,
        ObtenerMaquinasRecaudacionHandler $obtenerMaquinasRecaudacionHandler,
        ObtenerMaquinasOperativasPorComercioHandler $obtenerMaquinasOperativasPorComercioHandler,
        ObtenerComercioRecaudacionHandler $obtenerComercioRecaudacionHandler,
        ObtenerInformePorRecaudacionHandler $obtenerInformePorRecaudacionHandler
    ) {
        $this->registrarRecaudacionHandler                  = $registrarRecaudacionHandler;
        $this->actualizarRecaudacionHandler                 = $actualizarRecaudacionHandler;
        $this->eliminarRecaudacionHandler                   = $eliminarRecaudacionHandler;
        $this->guardarInformeHandler                        = $guardarInformeHandler;
        $this->obtenerRecaudacionesHandler                  = $obtenerRecaudacionesHandler;
        $this->obtenerResumenRecaudacionesHandler           = $obtenerResumenRecaudacionesHandler;
        $this->obtenerRecaudacionPorIdHandler               = $obtenerRecaudacionPorIdHandler;
        $this->obtenerMaquinasRecaudacionHandler            = $obtenerMaquinasRecaudacionHandler;
        $this->obtenerMaquinasOperativasPorComercioHandler  = $obtenerMaquinasOperativasPorComercioHandler;
        $this->obtenerComercioRecaudacionHandler            = $obtenerComercioRecaudacionHandler;
        $this->obtenerInformePorRecaudacionHandler          = $obtenerInformePorRecaudacionHandler;
    }

    #[OA\Post(
        path: "/v1/contabilidad/registrar-recaudacion",
        summary: "Registrar una nueva recaudación",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "tipoComercio", "montoTotal", "porcentajeComercio"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "tipoComercio", type: "string"),
                    new OA\Property(property: "montoTotal", type: "number", format: "float"),
                    new OA\Property(property: "porcentajeComercio", type: "number", format: "float"),
                    new OA\Property(property: "detalle", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Recaudación registrada exitosamente"),
            new OA\Response(response: 400, description: "Datos incompletos"),
            new OA\Response(response: 401, description: "Usuario no autenticado")
        ]
    )]
    public function registrarRecaudacion(Request $request): Response
    {
        $data     = $request->json();
        $required = ['idMaquina', 'tipoComercio', 'montoTotal', 'porcentajeComercio'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command      = new RegistrarRecaudacionCommand($data['idMaquina'], $userId, $data['tipoComercio'], (float) $data['montoTotal'], (float) $data['porcentajeComercio'], $data['detalle'] ?? '');
        $idRecaudacion = $this->registrarRecaudacionHandler->handle($command);
        
        $response = new Response();
        $response->json(['success' => true, 'message' => 'Recaudación registrada exitosamente', 'idRecaudacion' => $idRecaudacion], 201);
        return $response;
    }

    #[OA\Get(
        path: "/v1/contabilidad/recaudaciones",
        summary: "Obtener recaudaciones con filtros",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "fechaInicio", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fechaFin", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "idMaquina", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "tipoComercio", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 100)),
            new OA\Parameter(name: "offset", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 0))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de recaudaciones"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]

public function obtenerRecaudaciones(Request $request): Response
{
    $userId = $_SESSION['ID_Usuario'] ?? null;
    if (!$userId) {
        throw new DomainException('Usuario no autenticado', 401);
    }

    //  Usar los nombres correctos de los parámetros
    $query = new ObtenerRecaudacionesQuery(
        $request->query('fechaInicio'),   //  Cambiado
        $request->query('fechaFin'),      //  Cambiado
        $request->query('idMaquina'),     //  Cambiado
        $request->query('tipoComercio'),  //  Cambiado
        (int) ($request->query('limit') ?? 100),
        (int) ($request->query('offset') ?? 0)
    );
    
    $result = $this->obtenerRecaudacionesHandler->handle($query);

    $response = new Response();
    $response->json(['success' => true, 'recaudaciones' => $result['recaudaciones'], 'total' => $result['total']]);
    return $response;
}

    #[OA\Get(
        path: "/v1/contabilidad/recaudaciones/{uuid}",
        summary: "Obtener una recaudación por ID",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Datos de la recaudación"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 404, description: "Recaudación no encontrada"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerRecaudacion(Request $request, string $idRecaudacion): Response
    {
        if (!ValidationHelper::isValidUUID($idRecaudacion)) {
            throw new DomainException('ID de recaudación inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query  = new ObtenerRecaudacionPorIdQuery($idRecaudacion);
        $result = $this->obtenerRecaudacionPorIdHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'recaudacion' => $result['recaudacion']]);
        return $response;
    }

    #[OA\Get(
        path: "/v1/contabilidad/resumen-recaudaciones",
        summary: "Obtener resumen agregado de recaudaciones",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Resumen de recaudaciones"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerResumenRecaudaciones(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $limit   = $request->query('limit') ? (int) $request->query('limit') : null;
        $query   = new ObtenerResumenRecaudacionesQuery($limit);
        $resumen = $this->obtenerResumenRecaudacionesHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'resumen' => $resumen]);
        return $response;
    }

    #[OA\Put(
        path: "/v1/contabilidad/actualizar-recaudacion",
        summary: "Actualizar una recaudación existente",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idRecaudacion", "idMaquina", "montoTotal", "porcentajeComercio"],
                properties: [
                    new OA\Property(property: "idRecaudacion", type: "string"),
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "montoTotal", type: "number"),
                    new OA\Property(property: "porcentajeComercio", type: "number"),
                    new OA\Property(property: "tipoComercio", type: "string"),
                    new OA\Property(property: "detalle", type: "string"),
                    new OA\Property(property: "fecha", type: "string", format: "date")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Recaudación actualizada"),
            new OA\Response(response: 400, description: "Datos incompletos o IDs inválidos"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function actualizarRecaudacion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idRecaudacion'], $data['idMaquina'], $data['montoTotal'], $data['porcentajeComercio'])) {
            throw new DomainException('Datos incompletos para actualizar recaudación', 400);
        }

        if (!ValidationHelper::isValidUUID($data['idRecaudacion'])) {
            throw new DomainException('ID de recaudación inválido', 400);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new ActualizarRecaudacionCommand(
            $data['idRecaudacion'], $data['idMaquina'], $data['tipoComercio'] ?? '',
            (float) $data['montoTotal'], (float) $data['porcentajeComercio'],
            $data['detalle'] ?? '', $data['fecha'] ?? ''
        );
        $this->actualizarRecaudacionHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Recaudación actualizada correctamente']);
        return $response;
    }

    #[OA\Delete(
        path: "/v1/contabilidad/eliminar-recaudacion/{uuid}",
        summary: "Eliminar una recaudación",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Recaudación eliminada"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 404, description: "No encontrada"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function eliminarRecaudacion(Request $request, string $idRecaudacion): Response
    {
        if (!ValidationHelper::isValidUUID($idRecaudacion)) {
            throw new DomainException('ID de recaudación inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new EliminarRecaudacionCommand($idRecaudacion);
        $this->eliminarRecaudacionHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Recaudación eliminada correctamente']);
        return $response;
    }

    #[OA\Get(
        path: "/v1/contabilidad/maquinas-recaudacion",
        summary: "Obtener máquinas disponibles para recaudación",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerMaquinasRecaudacion(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query   = new ObtenerMaquinasRecaudacionQuery();
        $maquinas = $this->obtenerMaquinasRecaudacionHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'maquinas' => $maquinas]);
        return $response;
    }

#[OA\Get(
    path: "/v1/contabilidad/maquinas-operativas-por-comercio",
    summary: "Obtener máquinas operativas filtradas por comercio",
    tags: ["Contabilidad"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(name: "idComercio", in: "query", required: true, schema: new OA\Schema(type: "string"))
    ],
    responses: [
        new OA\Response(response: 200, description: "Lista de máquinas operativas"),
        new OA\Response(response: 400, description: "ID de comercio requerido o inválido"),
        new OA\Response(response: 401, description: "No autorizado")
    ]
)]
public function obtenerMaquinasOperativasPorComercio(Request $request): Response
{
    $idComercio = $request->query('idComercio');
    if (!$idComercio) {
        throw new DomainException('ID de comercio requerido', 400);
    }

    if (!ValidationHelper::isValidUUID($idComercio)) {
        throw new DomainException('ID de comercio inválido', 400);
    }

    $userId = $_SESSION['ID_Usuario'] ?? null;
    if (!$userId) {
        throw new DomainException('Usuario no autenticado', 401);
    }

    $query   = new ObtenerMaquinasOperativasPorComercioQuery($idComercio);
    $maquinas = $this->obtenerMaquinasOperativasPorComercioHandler->handle($query);

    error_log("obtenerMaquinasOperativasPorComercio: comercio=$idComercio, máquinas=" . count($maquinas));

    $response = new Response();
    // Enviar directamente el array, no envuelto en otro objeto
    $response->json(['success' => true, 'maquinas' => $maquinas]);
    return $response;
}

    #[OA\Get(
        path: "/v1/contabilidad/comercio-recaudacion/{uuid}",
        summary: "Obtener datos de comercio para recaudación",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Datos del comercio"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerComercioRecaudacion(Request $request, string $idComercio): Response
    {
        if (!ValidationHelper::isValidUUID($idComercio)) {
            throw new DomainException('ID de comercio inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $query   = new ObtenerComercioRecaudacionQuery($idComercio);
        $comercio = $this->obtenerComercioRecaudacionHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'comercio' => $comercio]);
        return $response;
    }

    #[OA\Post(
        path: "/v1/contabilidad/guardar-informe",
        summary: "Guardar informe de recaudación",
        tags: ["Contabilidad"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idRecaudacion", "ciUsuario", "nombreMaquina", "idComercio", "nombreComercio", "direccionComercio", "telefonoComercio", "montoTotal"],
                properties: [
                    new OA\Property(property: "idRecaudacion", type: "string"),
                    new OA\Property(property: "ciUsuario", type: "string"),
                    new OA\Property(property: "nombreMaquina", type: "string"),
                    new OA\Property(property: "idComercio", type: "string"),
                    new OA\Property(property: "nombreComercio", type: "string"),
                    new OA\Property(property: "direccionComercio", type: "string"),
                    new OA\Property(property: "telefonoComercio", type: "string"),
                    new OA\Property(property: "montoTotal", type: "number"),
                    new OA\Property(property: "componentes", type: "array", items: new OA\Items(type: "object"), nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Informe guardado exitosamente"),
            new OA\Response(response: 400, description: "Datos incompletos o IDs inválidos"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function guardarInforme(Request $request): Response
    {
        $data     = $request->json();
        $required = ['idRecaudacion', 'ciUsuario', 'nombreMaquina', 'idComercio', 'nombreComercio', 'direccionComercio', 'telefonoComercio', 'montoTotal'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        if (!ValidationHelper::isValidUUID($data['idRecaudacion'])) {
            throw new DomainException('ID de recaudación inválido', 400);
        }

        if (!ValidationHelper::isValidUUID($data['idComercio'])) {
            throw new DomainException('ID de comercio inválido', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command  = new GuardarInformeCommand(
            $data['idRecaudacion'], $data['ciUsuario'], $data['nombreMaquina'],
            $data['idComercio'], $data['nombreComercio'], $data['direccionComercio'],
            $data['telefonoComercio'], (float) $data['montoTotal'], $data['componentes'] ?? null
        );
        $idInforme = $this->guardarInformeHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Informe guardado exitosamente', 'idInforme' => $idInforme], 201);
        return $response;
    }
#[OA\Get(
    path: "/v1/contabilidad/informe/{uuid}",
    summary: "Obtener informe asociado a una recaudación",
    tags: ["Contabilidad"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
    ],
    responses: [
        new OA\Response(response: 200, description: "Informe y componentes asociados"),
        new OA\Response(response: 400, description: "UUID inválido"),
        new OA\Response(response: 404, description: "No encontrado"),
        new OA\Response(response: 401, description: "No autorizado")
    ]
)]
public function obtenerInformePorRecaudacion(Request $request, string $idRecaudacion): Response
{
    if (!ValidationHelper::isValidUUID($idRecaudacion)) {
        throw new DomainException('ID de recaudación inválido', 400);
    }

    $userId = $_SESSION['ID_Usuario'] ?? null;
    if (!$userId) {
        throw new DomainException('Usuario no autenticado', 401);
    }

    $query  = new ObtenerInformePorRecaudacionQuery($idRecaudacion);
    $result = $this->obtenerInformePorRecaudacionHandler->handle($query);

    $response = new Response();
    $response->json([
        'success' => true, 
        'informe' => $result['informe'], 
        'componentes' => $result['componentes'],
        'tecnicos' => $result['tecnicos'] ?? []  // ← Agregar técnicos
    ]);
    return $response;
}
    
    /**
     * @deprecated Este método está obsoleto. Usar obtenerMaquinasRecaudacion() en su lugar.
     */
    public function obtenerMaquinaRecaudacion(Request $request): Response
    {
        return $this->obtenerMaquinasRecaudacion($request);
    }
}