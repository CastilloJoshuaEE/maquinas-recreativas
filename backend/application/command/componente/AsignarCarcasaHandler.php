<?php
/**
 * application/commands/componente/AsignarCarcasaHandler.php
 *
 * Manejador del comando AsignarCarcasa.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Componente\TipoComponente;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class AsignarCarcasaHandler
 */
final class AsignarCarcasaHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(AsignarCarcasa $command): void
    {
        $idComponente = new Uuid($command->idComponente());
        $idUsuario = new Uuid($command->idUsuario());

        $componente = $this->componenteRepository->findById($idComponente);
        if (!$componente) {
            throw new DomainException('Componente no encontrado');
        }

        if (!$componente->tipo()->equals(TipoComponente::ESTRUCTURAL())) {
            throw new DomainException('El componente no es una carcasa válida');
        }

        $componente->asignarAUso($idUsuario);
        $this->componenteRepository->save($componente);
    }
}