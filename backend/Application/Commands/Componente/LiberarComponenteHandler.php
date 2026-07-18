<?php
/**
 * application/commands/componente/LiberarComponenteHandler.php
 *
 * Manejador del comando LiberarComponenteCommand.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class LiberarComponenteHandler
 */
final class LiberarComponenteHandler implements CommandHandler
{
    private ComponenteRepository $componenteRepository;
    
    public function __construct(ComponenteRepository $componenteRepository)
    {
        $this->componenteRepository = $componenteRepository;
    }
    
    public function handle(Command $command): void
    {
        if (!$command instanceof LiberarComponenteCommand) {
            throw new DomainException('Comando inválido');
        }
        
        $idComponente = new Uuid($command->idComponente());
        $idUsuario = new Uuid($command->idUsuario());
        
        $componente = $this->componenteRepository->findById($idComponente);
        if (!$componente) {
            throw new DomainException('Componente no encontrado');
        }
        
        if (!$componente->estaAsignado() || !$componente->usuarioAsignado()?->equals($idUsuario)) {
            throw new DomainException('El componente no está asignado a este usuario');
        }
        
        $componente->liberar();
        $this->componenteRepository->save($componente);
    }
}