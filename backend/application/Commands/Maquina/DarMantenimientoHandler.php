<?php
/**
 * application/commands/maquina/DarMantenimientoHandler.php
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Notificacion\NotificacionMaquina;
use maquinas_recreativas\Domain\Notificacion\NotificacionRepository;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class DarMantenimientoHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private NotificacionRepository $notificacionRepository;
    private DistribucionRepository $distribucionRepository;
    private HistorialRepository $historialRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        NotificacionRepository $notificacionRepository,
        DistribucionRepository $distribucionRepository,
        HistorialRepository $historialRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->notificacionRepository = $notificacionRepository;
        $this->distribucionRepository = $distribucionRepository;
        $this->historialRepository = $historialRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof DarMantenimientoCommand) {
            throw new DomainException('Comando inválido');
        }

        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);

        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $idLogistica = new Uuid($command->idLogistica());
        $logistica = $this->usuarioRepository->findById($idLogistica);

        if (!$logistica || $logistica->getTipo()->value() !== 'Logistica') {
            throw new DomainException('Usuario logística no válido');
        }

        $tecnicosMantenimiento = $this->usuarioRepository->findTecnicosByEspecialidad('Mantenimiento');

        if (empty($tecnicosMantenimiento)) {
            throw new DomainException('No hay técnicos de mantenimiento disponibles');
        }

        //  $tecnicosMantenimiento[0] es un array, no un objeto
        $primerTecnico = $tecnicosMantenimiento[0];
        
        if (is_array($primerTecnico)) {
            // Es un array, obtener el ID de la clave 'id'
            $tecnicoId = new Uuid($primerTecnico['id']);
            $tecnicoNombre = $primerTecnico['nombre'] ?? '';
            $tecnicoApellido = $primerTecnico['apellido'] ?? '';
        } else {
            // Es un objeto, usar métodos del objeto
            $tecnicoId = $primerTecnico->getId();
            $tecnicoNombre = $primerTecnico->getNombre();
            $tecnicoApellido = $primerTecnico->getApellido();
        }

        $comercio = $this->comercioRepository->buscarPorId($maquina->idComercio()->value());

        $maquina->solicitarMantenimiento($tecnicoId);
        $this->maquinaRepository->save($maquina);

        // Registrar historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idLogistica,
            $logistica->getTipo()->value(),
            'Solicitud de mantenimiento',
            "Máquina enviada a mantenimiento. Técnico asignado: {$tecnicoNombre} {$tecnicoApellido}. Motivo: {$command->mensaje()}",
            'Operativa',
            'No operativa',
            'Recaudacion',
            'Montaje',
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['tecnico_asignado' => $tecnicoNombre, 'motivo' => $command->mensaje()]
        );
        $this->historialRepository->save($historial);

        // Crear notificación al técnico
        $nombreComercio = $comercio ? $comercio->getNombre() : 'N/A';
        $mensajeCompleto = $command->mensaje() . " - Máquina: {$maquina->nombre()}, Comercio: {$nombreComercio}";

        $notificacion = NotificacionMaquina::crear(
            $idLogistica,
            $tecnicoId,
            $idMaquina,
            'Dar mantenimiento a máquina recreativa',
            $mensajeCompleto
        );
        $this->notificacionRepository->saveMaquina($notificacion);

        // Actualizar informe de distribución
        $this->distribucionRepository->updateEstado($idMaquina, 'No operativa');
    }
}