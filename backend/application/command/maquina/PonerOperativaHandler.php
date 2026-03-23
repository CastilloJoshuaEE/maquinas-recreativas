<?php
/**
 * application/commands/maquina/PonerOperativaHandler.php
 *
 * Manejador del comando PonerOperativa.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Maquina\MaquinaRecreativa;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Distribucion\DistribucionRepository;
use Reconocimiento\Domain\Historial\HistorialMaquina;
use Reconocimiento\Domain\Historial\HistorialRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

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