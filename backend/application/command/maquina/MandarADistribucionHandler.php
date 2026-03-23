<?php
/**
 * application/commands/maquina/MandarADistribucionHandler.php
 *
 * Manejador del comando MandarADistribucion.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Maquina\MaquinaRecreativa;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Comercio\ComercioRepository;
use Reconocimiento\Domain\Distribucion\InformeDistribucion;
use Reconocimiento\Domain\Distribucion\DistribucionRepository;
use Reconocimiento\Domain\Notificacion\NotificacionMaquina;
use Reconocimiento\Domain\Notificacion\NotificacionRepository;
use Reconocimiento\Domain\Historial\HistorialMaquina;
use Reconocimiento\Domain\Historial\HistorialRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class MandarADistribucionHandler
 */
final class MandarADistribucionHandler
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

    public function handle(MandarADistribucion $command): void
    {
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

        $comercio = $this->comercioRepository->findById($maquina->idComercio());

        $maquina->enviarADistribucion();
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $nombreComercio = $comercio ? $comercio->nombre() : 'N/A';
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idRemitente,
            $remitente->tipo()->value(),
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

        // Crear informe de distribución
        $informe = InformeDistribucion::crear(
            $idMaquina,
            $maquina->idTecnicoComprobador(),
            $maquina->idComercio()
        );
        $this->distribucionRepository->save($informe);

        // Notificar a todos los logísticos
        $logisticos = $this->usuarioRepository->findByTipo('Logistica');
        $mensajeCompleto = $command->mensaje() . " - Máquina: {$maquina->nombre()}, Comercio: {$nombreComercio}";

        foreach ($logisticos as $logistico) {
            $notificacion = NotificacionMaquina::crear(
                $idRemitente,
                $logistico->id(),
                $idMaquina,
                'Distribuir máquina recreativa',
                $mensajeCompleto
            );
            $this->notificacionRepository->saveMaquina($notificacion);
        }
    }
}