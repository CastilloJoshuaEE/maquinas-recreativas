<?php
/**
 * application/commands/maquina/PonerOperativaHandler.php
 *
 * Manejador del comando PonerOperativa.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class PonerOperativaHandler
 */
final class PonerOperativaHandler
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

    public function handle(PonerOperativa $command): void
    {
        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);

        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $maquina->ponerOperativa();
        $this->maquinaRepository->save($maquina);

        // Actualizar informe de distribución
        $this->distribucionRepository->updateEstado($idMaquina, 'Operativa');

        // Registrar historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            new Uuid($_SESSION['ID_Usuario'] ?? 'sistema'),
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