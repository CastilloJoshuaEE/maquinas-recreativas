<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Attributes as OA;

use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMaquinaHandler;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaCommand;
use maquinas_recreativas\Application\Commands\Maquina\GenerarPlacaHandler;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeCommand;
use maquinas_recreativas\Application\Commands\Maquina\RegistrarMontajeHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAComprobacionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarAComprobacionHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarAReensamblarHandler;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionCommand;
use maquinas_recreativas\Application\Commands\Maquina\MandarADistribucionHandler;
use maquinas_recreativas\Application\Commands\Maquina\PonerOperativaCommand;
use maquinas_recreativas\Application\Commands\Maquina\PonerOperativaHandler;
use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoCommand;
use maquinas_recreativas\Application\Commands\Maquina\DarMantenimientoHandler;
use maquinas_recreativas\Application\Commands\Maquina\FinalizarMantenimientoCommand;
use maquinas_recreativas\Application\Commands\Maquina\FinalizarMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoEnsambladorHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoComprobadorHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoMantenimientoQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorTecnicoMantenimientoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEstadoHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasPorEtapaHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerMaquinasParaDistribucionHandler;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerComponentesMaquinaQuery;
use maquinas_recreativas\Application\Queries\Maquina\ObtenerComponentesMaquinaHandler;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Infrastructure\Security\ValidationHelper;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class MaquinaController
{
    private RegistrarMaquinaHandler $registrarMaquinaHandler;
    private GenerarPlacaHandler $generarPlacaHandler;
    private RegistrarMontajeHandler $registrarMontajeHandler;
    private MandarAComprobacionHandler $mandarAComprobacionHandler;
    private MandarAReensamblarHandler $mandarAReensamblarHandler;
    private MandarADistribucionHandler $mandarADistribucionHandler;
    private PonerOperativaHandler $ponerOperativaHandler;
    private DarMantenimientoHandler $darMantenimientoHandler;
    private FinalizarMantenimientoHandler $finalizarMantenimientoHandler;
    private ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerPorTecnicoEnsambladorHandler;
    private ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerPorTecnicoComprobadorHandler;
    private ObtenerMaquinasPorTecnicoMantenimientoHandler $obtenerPorTecnicoMantenimientoHandler;
    private ObtenerMaquinasPorEstadoHandler $obtenerPorEstadoHandler;
    private ObtenerMaquinasPorEtapaHandler $obtenerPorEtapaHandler;
    private ObtenerMaquinasParaDistribucionHandler $obtenerMaquinasParaDistribucionHandler;
    private ObtenerComponentesMaquinaHandler $obtenerComponentesPorMaquinaHandler;

    public function __construct(
        RegistrarMaquinaHandler $registrarMaquinaHandler,
        GenerarPlacaHandler $generarPlacaHandler,
        RegistrarMontajeHandler $registrarMontajeHandler,
        MandarAComprobacionHandler $mandarAComprobacionHandler,
        MandarAReensamblarHandler $mandarAReensamblarHandler,
        MandarADistribucionHandler $mandarADistribucionHandler,
        PonerOperativaHandler $ponerOperativaHandler,
        DarMantenimientoHandler $darMantenimientoHandler,
        FinalizarMantenimientoHandler $finalizarMantenimientoHandler,
        ObtenerMaquinasPorTecnicoEnsambladorHandler $obtenerPorTecnicoEnsambladorHandler,
        ObtenerMaquinasPorTecnicoComprobadorHandler $obtenerPorTecnicoComprobadorHandler,
        ObtenerMaquinasPorTecnicoMantenimientoHandler $obtenerPorTecnicoMantenimientoHandler,
        ObtenerMaquinasPorEstadoHandler $obtenerPorEstadoHandler,
        ObtenerMaquinasPorEtapaHandler $obtenerPorEtapaHandler,
        ObtenerMaquinasParaDistribucionHandler $obtenerMaquinasParaDistribucionHandler,
        ObtenerComponentesMaquinaHandler $obtenerComponentesPorMaquinaHandler
    ) {
        $this->registrarMaquinaHandler              = $registrarMaquinaHandler;
        $this->generarPlacaHandler                  = $generarPlacaHandler;
        $this->registrarMontajeHandler              = $registrarMontajeHandler;
        $this->mandarAComprobacionHandler           = $mandarAComprobacionHandler;
        $this->mandarAReensamblarHandler            = $mandarAReensamblarHandler;
        $this->mandarADistribucionHandler           = $mandarADistribucionHandler;
        $this->ponerOperativaHandler                = $ponerOperativaHandler;
        $this->darMantenimientoHandler              = $darMantenimientoHandler;
        $this->finalizarMantenimientoHandler        = $finalizarMantenimientoHandler;
        $this->obtenerPorTecnicoEnsambladorHandler  = $obtenerPorTecnicoEnsambladorHandler;
        $this->obtenerPorTecnicoComprobadorHandler  = $obtenerPorTecnicoComprobadorHandler;
        $this->obtenerPorTecnicoMantenimientoHandler = $obtenerPorTecnicoMantenimientoHandler;
        $this->obtenerPorEstadoHandler              = $obtenerPorEstadoHandler;
        $this->obtenerPorEtapaHandler               = $obtenerPorEtapaHandler;
        $this->obtenerMaquinasParaDistribucionHandler = $obtenerMaquinasParaDistribucionHandler;
        $this->obtenerComponentesPorMaquinaHandler  = $obtenerComponentesPorMaquinaHandler;
    }

    #[OA\Post(
        path: "/v1/maquina/register",
        summary: "Registrar una nueva máquina recreativa",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nombre", "tipo", "idComercio", "idPlaca", "idCarcasa"],
                properties: [
                    new OA\Property(property: "nombre", type: "string"),
                    new OA\Property(property: "tipo", type: "string"),
                    new OA\Property(property: "idComercio", type: "string"),
                    new OA\Property(property: "idPlaca", type: "string"),
                    new OA\Property(property: "idCarcasa", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Máquina registrada exitosamente"),
            new OA\Response(response: 400, description: "Datos incompletos"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function register(Request $request): Response
    {
        $data     = $request->json();
        $required = ['nombre', 'tipo', 'idComercio', 'idPlaca', 'idCarcasa'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new DomainException("El campo {$field} es requerido", 400);
            }
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }
$idEnsamblador  = $data['idEnsamblador'] ?? null;
$idComprobador  = $data['idComprobador'] ?? null;
$command = new RegistrarMaquinaCommand(
    $data['nombre'], $data['tipo'], $data['idComercio'], $userId,
    $data['idPlaca'], $data['idCarcasa'],
    $idEnsamblador, $idComprobador   // nuevos parámetros
);
        $idMaquina = $this->registrarMaquinaHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Máquina registrada exitosamente', 'idMaquina' => $idMaquina], 201);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/generar-placa",
        summary: "Generar una nueva placa de componente",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Placa generada con su ID de componente"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function generarPlaca(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new GenerarPlacaCommand($userId);
        $result  = $this->generarPlacaHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'placa' => $result['placa'], 'idComponente' => $result['idComponente']]);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/registrar-montaje",
        summary: "Registrar montaje de un componente en una máquina",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "idComponente"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "idComponente", type: "string"),
                    new OA\Property(property: "detalle", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Montaje registrado exitosamente"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function registrarMontaje(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['idComponente'])) {
            throw new DomainException('ID de máquina y componente requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new RegistrarMontajeCommand($data['idMaquina'], $data['idComponente'], $userId, $data['detalle'] ?? null);
        $this->registrarMontajeHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Montaje registrado exitosamente']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/mandar-comprobacion",
        summary: "Enviar máquina a etapa de comprobación",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Máquina enviada a comprobación"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function mandarAComprobacion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new MandarAComprobacionCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarAComprobacionHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Máquina enviada a comprobación']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/mandar-reensamblar",
        summary: "Enviar máquina a reensamblar",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Máquina enviada a reensamblar"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function mandarAReensamblar(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new MandarAReensamblarCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarAReensamblarHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Máquina enviada a reensamblar']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/mandar-distribucion",
        summary: "Enviar máquina a distribución",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Máquina enviada a distribución"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function mandarADistribucion(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new MandarADistribucionCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarADistribucionHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Máquina enviada a distribución']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/poner-operativa",
        summary: "Marcar una máquina como operativa",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Máquina puesta en operativa"),
            new OA\Response(response: 400, description: "ID de máquina requerido"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function ponerOperativa(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'])) {
            throw new DomainException('ID de máquina requerido', 400);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new PonerOperativaCommand($data['idMaquina']);
        $this->ponerOperativaHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Máquina puesta en operativa']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/dar-mantenimiento",
        summary: "Solicitar mantenimiento para una máquina",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Mantenimiento solicitado"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function darMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['mensaje'])) {
            throw new DomainException('ID de máquina y mensaje requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new DarMantenimientoCommand($data['idMaquina'], $data['mensaje'], $userId);
        $this->darMantenimientoHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Mantenimiento solicitado']);
        return $response;
    }

    #[OA\Post(
        path: "/v1/maquina/finalizar-mantenimiento",
        summary: "Finalizar el mantenimiento de una máquina",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["idMaquina", "exito", "mensaje"],
                properties: [
                    new OA\Property(property: "idMaquina", type: "string"),
                    new OA\Property(property: "exito", type: "boolean"),
                    new OA\Property(property: "mensaje", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Mantenimiento finalizado"),
            new OA\Response(response: 400, description: "Datos requeridos faltantes"),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function finalizarMantenimiento(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'], $data['exito'], $data['mensaje'])) {
            throw new DomainException('ID de máquina, éxito y mensaje requeridos', 400);
        }

        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        if (!ValidationHelper::isValidUUID($data['idMaquina'])) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $command = new FinalizarMantenimientoCommand($data['idMaquina'], $userId, (bool) $data['exito'], $data['mensaje']);
        $this->finalizarMantenimientoHandler->handle($command);

        $response = new Response();
        $response->json(['success' => true, 'message' => 'Mantenimiento finalizado']);
        return $response;
    }

    #[OA\Get(
        path: "/v1/maquina/ensamblador/{uuid}",
        summary: "Obtener máquinas asignadas a un técnico ensamblador",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
public function obtenerPorTecnicoEnsamblador(Request $request, string $idTecnico): Response
{
    error_log("=== obtenerPorTecnicoEnsamblador - ID: $idTecnico ===");
    
    $query = new ObtenerMaquinasPorTecnicoEnsambladorQuery($idTecnico);
    $resultado = $this->obtenerPorTecnicoEnsambladorHandler->handle($query);
    
    error_log("Resultado: " . json_encode($resultado));
    
    $response = new Response();
    if (isset($resultado['success']) && $resultado['success'] === false) {
        $response->json(['success' => false, 'maquinas' => [], 'error' => $resultado['error'] ?? 'Error desconocido'], 500);
    } else {
        $response->json(['success' => true, 'maquinas' => $resultado['maquinas'] ?? []]);
    }
    return $response;
}

    #[OA\Get(
        path: "/v1/maquina/comprobador/{uuid}",
        summary: "Obtener máquinas asignadas a un técnico comprobador",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
public function obtenerPorTecnicoComprobador(Request $request, string $idTecnico): Response
{
    $query = new ObtenerMaquinasPorTecnicoComprobadorQuery($idTecnico);
    $resultado = $this->obtenerPorTecnicoComprobadorHandler->handle($query);
    
    $response = new Response();
    // Si el handler devuelve un array simple, lo envolvemos
    if (isset($resultado['success'])) {
        // Ya tiene la estructura correcta
        $response->json($resultado);
    } else {
        // Es un array simple de máquinas
        $response->json(['success' => true, 'maquinas' => $resultado]);
    }
    return $response;
}
    #[OA\Get(
        path: "/v1/maquina/mantenimiento/{uuid}",
        summary: "Obtener máquinas asignadas a un técnico de mantenimiento",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerPorTecnicoMantenimiento(Request $request, string $idTecnico): Response
    {
        if (!ValidationHelper::isValidUUID($idTecnico)) {
            throw new DomainException('ID de técnico inválido', 400);
        }

        $query   = new ObtenerMaquinasPorTecnicoMantenimientoQuery($idTecnico);
        $maquinas = $this->obtenerPorTecnicoMantenimientoHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'maquinas' => $maquinas]);
        return $response;
    }

    #[OA\Get(
        path: "/v1/maquina/estado/{estado}",
        summary: "Obtener máquinas filtradas por estado",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "estado", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas por estado"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
public function obtenerPorEstado(Request $request, string $estado): Response
{
    error_log("=== obtenerPorEstado: estado=$estado ===");
    $response = new Response();
    try {
        $query = new ObtenerMaquinasPorEstadoQuery($estado);
        $resultado = $this->obtenerPorEstadoHandler->handle($query);
        error_log("Resultado del handler: " . json_encode($resultado));

        if (isset($resultado['success']) && $resultado['success'] === false) {
            $response->json(['success' => false, 'maquinas' => [], 'error' => $resultado['error']], 500);
        } else {
            $response->json(['success' => true, 'maquinas' => $resultado['maquinas'] ?? []]);
        }
    } catch (\Throwable $e) {
        error_log("Excepción en obtenerPorEstado: " . $e->getMessage());
        $response->json(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()], 500);
    }
    return $response;
}
    #[OA\Get(
        path: "/v1/maquina/etapa/{etapa}",
        summary: "Obtener máquinas filtradas por etapa",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "etapa", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas por etapa"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
 public function obtenerPorEtapa(Request $request, string $etapa): Response
{
    $query = new ObtenerMaquinasPorEtapaQuery($etapa);
    $resultado = $this->obtenerPorEtapaHandler->handle($query);
    
    $response = new Response();
    if (isset($resultado['success']) && $resultado['success'] === false) {
        $response->json(['success' => false, 'maquinas' => [], 'error' => $resultado['error'] ?? 'Error desconocido'], 500);
    } else {
        $response->json(['success' => true, 'maquinas' => $resultado['maquinas'] ?? []]);
    }
    return $response;
}

    #[OA\Get(
        path: "/v1/maquina/distribucion",
        summary: "Obtener máquinas disponibles para distribución",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista de máquinas para distribución"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerMaquinasParaDistribucion(Request $request): Response
    {
        $query   = new ObtenerMaquinasParaDistribucionQuery();
        $maquinas = $this->obtenerMaquinasParaDistribucionHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'maquinas' => $maquinas]);
        return $response;
    }

    #[OA\Get(
        path: "/v1/maquina/componentes/{uuid}",
        summary: "Obtener componentes instalados en una máquina",
        tags: ["Máquinas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de componentes de la máquina"),
            new OA\Response(response: 400, description: "UUID inválido"),
            new OA\Response(response: 401, description: "No autorizado")
        ]
    )]
    public function obtenerComponentesPorMaquina(Request $request, string $idMaquina): Response
    {
        if (!ValidationHelper::isValidUUID($idMaquina)) {
            throw new DomainException('ID de máquina inválido', 400);
        }

        $query       = new ObtenerComponentesMaquinaQuery($idMaquina);
        $componentes = $this->obtenerComponentesPorMaquinaHandler->handle($query);

        $response = new Response();
        $response->json(['success' => true, 'componentes' => $componentes]);
        return $response;
    }
}