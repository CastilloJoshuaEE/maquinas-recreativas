<?php
/**
 * application/commands/recaudacion/EliminarRecaudacionHandler.php
 *
 * Manejador del comando EliminarRecaudacion.
 *
 * @package Reconocimiento\Application\Commands\Recaudacion
 */

namespace Reconocimiento\Application\Commands\Recaudacion;

use Reconocimiento\Domain\Recaudacion\RecaudacionRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class EliminarRecaudacionHandler
 */
final class EliminarRecaudacionHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    public function handle(EliminarRecaudacion $command): void
    {
        $idRecaudacion = new Uuid($command->idRecaudacion());
        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);

        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada');
        }

        $this->recaudacionRepository->delete($idRecaudacion);
    }
}