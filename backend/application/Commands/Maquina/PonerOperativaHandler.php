<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class PonerOperativaHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private DistribucionRepository $distribucionRepository;
    private HistorialRepository $historialRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        DistribucionRepository $distribucionRepository,
        HistorialRepository $historialRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->distribucionRepository = $distribucionRepository;
        $this->historialRepository = $historialRepository;
    }

 public function handle(Command $command): void
{
    if (!$command instanceof PonerOperativaCommand) {
        throw new DomainException('Comando inválido');
    }

    $idMaquina = new Uuid($command->idMaquina());
    $maquina = $this->maquinaRepository->findById($idMaquina);

    if (!$maquina) {
        throw new DomainException('Máquina no encontrada');
    }

    $maquina->ponerOperativa();
    $this->maquinaRepository->save($maquina);

    $this->distribucionRepository->updateEstado($idMaquina, 'Operativa');

    // Solo registrar historial si hay sesión activa con usuario válido
    $idUsuarioSesion = $_SESSION['ID_Usuario'] ?? null;
    if ($idUsuarioSesion !== null) {
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            new Uuid($idUsuarioSesion),
            $_SESSION['rol'] ?? 'Sistema',
            'Puesta en operativa',
            "Máquina marcada como operativa y en etapa de recaudación",
            'Distribuyendose',
            'Operativa',
            'Distribucion',
            'Recaudacion',
            $_SERVER['REMOTE_ADDR'] ?? null,
            []
        );
        $this->historialRepository->save($historial);
    }
}
}