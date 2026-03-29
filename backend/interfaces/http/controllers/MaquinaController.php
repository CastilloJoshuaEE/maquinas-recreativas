<?php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use OpenApi\Annotations as OA;

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

    /**
     * @OA\Post(
     *     path="/v1/maquina/register",
     *     summary="Registrar una nueva máquina recreativa",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","tipo","idComercio","idPlaca","idCarcasa"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="tipo", type="string"),
     *             @OA\Property(property="idComercio", type="string"),
     *             @OA\Property(property="idPlaca", type="string"),
     *             @OA\Property(property="idCarcasa", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Máquina registrada exitosamente"),
     *     @OA\Response(response=400, description="Datos incompletos"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
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

        $command  = new RegistrarMaquinaCommand($data['nombre'], $data['tipo'], $data['idComercio'], $userId, $data['idPlaca'], $data['idCarcasa']);
        $idMaquina = $this->registrarMaquinaHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina registrada exitosamente', 'idMaquina' => $idMaquina], 201);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/generar-placa",
     *     summary="Generar una nueva placa de componente",
     *     tags={"Máquinas"},
     *     @OA\Response(response=200, description="Placa generada con su ID de componente"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function generarPlaca(Request $request): Response
    {
        $userId = $_SESSION['ID_Usuario'] ?? null;
        if (!$userId) {
            throw new DomainException('Usuario no autenticado', 401);
        }

        $command = new GenerarPlacaCommand($userId);
        $result  = $this->generarPlacaHandler->handle($command);

        return (new Response())->json(['success' => true, 'placa' => $result['placa'], 'idComponente' => $result['idComponente']]);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/registrar-montaje",
     *     summary="Registrar montaje de un componente en una máquina",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idMaquina","idComponente"},
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="idComponente", type="string"),
     *             @OA\Property(property="detalle", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Montaje registrado exitosamente"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes")
     * )
     */
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

        $command = new RegistrarMontajeCommand($data['idMaquina'], $data['idComponente'], $userId, $data['detalle'] ?? null);
        $this->registrarMontajeHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Montaje registrado exitosamente']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/mandar-comprobacion",
     *     summary="Enviar máquina a etapa de comprobación",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idMaquina","mensaje"}, @OA\Property(property="idMaquina", type="string"), @OA\Property(property="mensaje", type="string"))),
     *     @OA\Response(response=200, description="Máquina enviada a comprobación"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes")
     * )
     */
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

        $command = new MandarAComprobacionCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarAComprobacionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a comprobación']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/mandar-reensamblar",
     *     summary="Enviar máquina a reensamblar",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idMaquina","mensaje"}, @OA\Property(property="idMaquina", type="string"), @OA\Property(property="mensaje", type="string"))),
     *     @OA\Response(response=200, description="Máquina enviada a reensamblar")
     * )
     */
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

        $command = new MandarAReensamblarCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarAReensamblarHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a reensamblar']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/mandar-distribucion",
     *     summary="Enviar máquina a distribución",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idMaquina","mensaje"}, @OA\Property(property="idMaquina", type="string"), @OA\Property(property="mensaje", type="string"))),
     *     @OA\Response(response=200, description="Máquina enviada a distribución")
     * )
     */
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

        $command = new MandarADistribucionCommand($data['idMaquina'], $userId, $data['mensaje']);
        $this->mandarADistribucionHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina enviada a distribución']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/poner-operativa",
     *     summary="Marcar una máquina como operativa",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idMaquina"}, @OA\Property(property="idMaquina", type="string"))),
     *     @OA\Response(response=200, description="Máquina puesta en operativa"),
     *     @OA\Response(response=400, description="ID de máquina requerido")
     * )
     */
    public function ponerOperativa(Request $request): Response
    {
        $data = $request->json();
        if (!isset($data['idMaquina'])) {
            throw new DomainException('ID de máquina requerido', 400);
        }

        $command = new PonerOperativaCommand($data['idMaquina']);
        $this->ponerOperativaHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Máquina puesta en operativa']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/dar-mantenimiento",
     *     summary="Solicitar mantenimiento para una máquina",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"idMaquina","mensaje"}, @OA\Property(property="idMaquina", type="string"), @OA\Property(property="mensaje", type="string"))),
     *     @OA\Response(response=200, description="Mantenimiento solicitado"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
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

        $command = new DarMantenimientoCommand($data['idMaquina'], $data['mensaje'], $userId);
        $this->darMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento solicitado']);
    }

    /**
     * @OA\Post(
     *     path="/v1/maquina/finalizar-mantenimiento",
     *     summary="Finalizar el mantenimiento de una máquina",
     *     tags={"Máquinas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"idMaquina","exito","mensaje"},
     *             @OA\Property(property="idMaquina", type="string"),
     *             @OA\Property(property="exito", type="boolean"),
     *             @OA\Property(property="mensaje", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Mantenimiento finalizado"),
     *     @OA\Response(response=400, description="Datos requeridos faltantes")
     * )
     */
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

        $command = new FinalizarMantenimientoCommand($data['idMaquina'], $userId, (bool) $data['exito'], $data['mensaje']);
        $this->finalizarMantenimientoHandler->handle($command);

        return (new Response())->json(['success' => true, 'message' => 'Mantenimiento finalizado']);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/ensamblador/{uuid}",
     *     summary="Obtener máquinas asignadas a un técnico ensamblador",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de máquinas")
     * )
     */
    public function obtenerPorTecnicoEnsamblador(Request $request, string $idTecnico): Response
    {
        $query   = new ObtenerMaquinasPorTecnicoEnsambladorQuery($idTecnico);
        $maquinas = $this->obtenerPorTecnicoEnsambladorHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/comprobador/{uuid}",
     *     summary="Obtener máquinas asignadas a un técnico comprobador",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de máquinas")
     * )
     */
    public function obtenerPorTecnicoComprobador(Request $request, string $idTecnico): Response
    {
        $query   = new ObtenerMaquinasPorTecnicoComprobadorQuery($idTecnico);
        $maquinas = $this->obtenerPorTecnicoComprobadorHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/mantenimiento/{uuid}",
     *     summary="Obtener máquinas asignadas a un técnico de mantenimiento",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de máquinas")
     * )
     */
    public function obtenerPorTecnicoMantenimiento(Request $request, string $idTecnico): Response
    {
        $query   = new ObtenerMaquinasPorTecnicoMantenimientoQuery($idTecnico);
        $maquinas = $this->obtenerPorTecnicoMantenimientoHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/estado/{estado}",
     *     summary="Obtener máquinas filtradas por estado",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="estado", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de máquinas por estado")
     * )
     */
    public function obtenerPorEstado(Request $request, string $estado): Response
    {
        $query   = new ObtenerMaquinasPorEstadoQuery($estado);
        $maquinas = $this->obtenerPorEstadoHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/etapa/{etapa}",
     *     summary="Obtener máquinas filtradas por etapa",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="etapa", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de máquinas por etapa")
     * )
     */
    public function obtenerPorEtapa(Request $request, string $etapa): Response
    {
        $query   = new ObtenerMaquinasPorEtapaQuery($etapa);
        $maquinas = $this->obtenerPorEtapaHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/distribucion",
     *     summary="Obtener máquinas disponibles para distribución",
     *     tags={"Máquinas"},
     *     @OA\Response(response=200, description="Lista de máquinas para distribución")
     * )
     */
    public function obtenerMaquinasParaDistribucion(Request $request): Response
    {
        $query   = new ObtenerMaquinasParaDistribucionQuery();
        $maquinas = $this->obtenerMaquinasParaDistribucionHandler->handle($query);

        return (new Response())->json(['success' => true, 'maquinas' => $maquinas]);
    }

    /**
     * @OA\Get(
     *     path="/v1/maquina/componentes/{uuid}",
     *     summary="Obtener componentes instalados en una máquina",
     *     tags={"Máquinas"},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Lista de componentes de la máquina")
     * )
     */
    public function obtenerComponentesPorMaquina(Request $request, string $idMaquina): Response
    {
        $query       = new ObtenerComponentesMaquinaQuery($idMaquina);
        $componentes = $this->obtenerComponentesPorMaquinaHandler->handle($query);

        return (new Response())->json(['success' => true, 'componentes' => $componentes]);
    }
}