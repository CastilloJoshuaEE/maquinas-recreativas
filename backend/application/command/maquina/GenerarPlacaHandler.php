<?php
/**
 * application/commands/maquina/GenerarPlacaHandler.php
 *
 * Manejador del comando GenerarPlaca.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Componente\TipoComponente;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class GenerarPlacaHandler
 */
final class GenerarPlacaHandler
{
    private ComponenteRepository $componenteRepository;
    private \maquinas_recreativas\Domain\Usuario\UsuarioRepository $usuarioRepository;
    public function __construct(ComponenteRepository $componenteRepository, UsuarioRepository $usuarioRepository){
        $this->componenteRepository = $componenteRepository;
        $this->usuarioRepository = $usuarioRepository;

    }
    /**
     * @return array{placa: string, idComponente: string}
     */
    public function handle(GenerarPlaca $command):array{
        $idTecnico = new Uuid($command->idTecnico());
        $tecnico = $this->usuarioRepository->findById($idTecnico);
        if(!$tecnico){
            throw new DomainException('Técnico no encontrado');
        }
        if(!$tecnico->esTecnico()){
            throw new DomainException('El usuario no es un técnico válido');
        }
        $numeroPlaca = $this->componenteRepository->generarNumeroPlaca();
        $placa = Componente::generarPlaca($numeroPlaca);
        $placa->asignarAUso($idTecnico);
        $this->componenteRepository->save($placa);
        return [
            'placa'=> $numeroPlaca,
            'idComponente'=> $placa->id()->value()
        ];
    }    
}