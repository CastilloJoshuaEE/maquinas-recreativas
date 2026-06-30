<?php
/**
 * application/commands/componente/AsignarCarcasaHandler.php
 *
 * Manejador del comando AsignarCarcasaCommand.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class AsignarCarcasaHandler
 */
final class AsignarCarcasaHandler implements CommandHandler
{
    private ComponenteRepository $componenteRepository;

    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof AsignarCarcasaCommand) {
            throw new DomainException('Comando inválido');
        }

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