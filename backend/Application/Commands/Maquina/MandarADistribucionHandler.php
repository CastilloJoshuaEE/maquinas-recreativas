<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Distribucion\InformeDistribucion;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class MandarADistribucionHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private DistribucionRepository $distribucionRepository;
    private NotificacionRepository $notificacionRepository;
    private HistorialRepository $historialRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        DistribucionRepository $distribucionRepository,
        NotificacionRepository $notificacionRepository,
        HistorialRepository $historialRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->distribucionRepository = $distribucionRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->historialRepository = $historialRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof MandarADistribucionCommand) {
            throw new DomainException('Comando inválido');
        }

        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);

        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $idRemitente = new Uuid($command->idRemitente());
        $remitente = $this->usuarioRepository->findById($idRemitente);

        if (!$remitente) {
            throw new DomainException('Usuario remitente no encontrado');
        }

        $comercio = $this->comercioRepository->buscarPorId($maquina->idComercio()->value());

        $maquina->enviarADistribucion();
        $this->maquinaRepository->save($maquina);

        $nombreComercio = $comercio ? $comercio->getNombre() : 'N/A';
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->getTipo()->value(),
            'Envío a distribución',
            "Máquina aprobada y enviada a distribución. Comercio: {$nombreComercio}. Mensaje: {$command->mensaje()}",
            'Comprobandose',
            'Distribuyendose',
            'Montaje',
            'Distribucion',
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['comercio' => $nombreComercio, 'mensaje' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        $informe = InformeDistribucion::crear(
            $idMaquina,
            $maquina->idTecnicoComprobador(),
            $maquina->idComercio()
        );
        $this->distribucionRepository->save($informe);

        $logisticos = $this->usuarioRepository->findByTipo('Logistica');
        $mensajeCompleto = $command->mensaje() . " - Máquina: {$maquina->getNombre()}, Comercio: {$nombreComercio}";

        foreach ($logisticos as $logistico) {
            $notificacion = NotificacionMaquina::crear(
                $idRemitente,
                $logistico->getId(),
                $idMaquina,
                'Distribuir máquina recreativa',
                $mensajeCompleto
            );
            $this->notificacionRepository->saveMaquina($notificacion);
        }
    }
}