<?php
/**
 * application/commands/componente/LiberarComponenteHandler.php
 *
 * Manejador del comando LiberarComponente.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class LiberarComponenteHandler
 */
final class LiberarComponenteHandler
{
    private ComponenteRepository $componenteRepository;
    public function __construct(ComponenteRepository $componenteRepository){
        $this->componenteRepository = $componenteRepository;
    }
    public function handle(LiberarComponente $command): void{
        $idComponente = new Uuid($command->idComponente());
        $idUsuario = new Uuid($command->idUsuario());
        $componente = $this->componenteRepository->findById($idComponente);
        if(!$componente){
            throw new DomainException('Componente no encontrado');
        }
        if(!$componente->estaAsignado()||!$componente->usuarioAsignado()?->equals($idUsuario)){
            throw new DomainException('El componente no está asignado a este usuario');
        }
        $componente->liberar();
        $this->componenteRepository->save($componente);
    }
}