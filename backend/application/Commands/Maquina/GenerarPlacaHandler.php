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

        $idUsuario = new Uuid($command->idTecnico());
        $usuario = $this->usuarioRepository->findById($idUsuario);
        
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }
        
        // Permitir que logística también pueda generar placas
        $esLogistica = $usuario->getTipo()->isLogistica();
        $esTecnico = $usuario->getTipo()->isTecnico();
        
        if (!$esLogistica && !$esTecnico) {
            throw new DomainException('El usuario no tiene permisos para generar placas');
        }
        
        $numeroPlaca = $this->componenteRepository->generarNumeroPlaca();
        $placa = Componente::generarPlaca($numeroPlaca);
        $placa->asignarAUso($idUsuario);
        $this->componenteRepository->save($placa);
        
        return [
            'placa' => $numeroPlaca,
            'idComponente' => $placa->id()->value()
        ];
    }    
}