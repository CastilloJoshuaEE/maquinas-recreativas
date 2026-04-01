<?php
/**
 * application/commands/maquina/GenerarPlacaHandler.php
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class GenerarPlacaHandler implements CommandHandler
{
    private ComponenteRepository $componenteRepository;
    private UsuarioRepository $usuarioRepository;
    
    public function __construct(ComponenteRepository $componenteRepository, UsuarioRepository $usuarioRepository)
    {
        $this->componenteRepository = $componenteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(Command $command): array
    {
        if (!$command instanceof GenerarPlacaCommand) {
            throw new DomainException('Comando inválido');
        }

        $idTecnico = new Uuid($command->idTecnico());
        $tecnico = $this->usuarioRepository->findById($idTecnico);
        
        if (!$tecnico) {
            throw new DomainException('Técnico no encontrado');
        }
        
        if (!$tecnico->getTipo()->isTecnico()) {
            throw new DomainException('El usuario no es un técnico válido');
        }
        
        $numeroPlaca = $this->componenteRepository->generarNumeroPlaca();
        $placa = Componente::generarPlaca($numeroPlaca);
        $placa->asignarAUso($idTecnico);
        $this->componenteRepository->save($placa);
        
        return [
            'placa' => $numeroPlaca,
            'idComponente' => $placa->id()->value()
        ];
    }    
}