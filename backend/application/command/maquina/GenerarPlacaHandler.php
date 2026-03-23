<?php
/**
 * application/commands/maquina/GenerarPlacaHandler.php
 *
 * Manejador del comando GenerarPlaca.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Componente\Componente;
use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Componente\TipoComponente;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class GenerarPlacaHandler
 */
final class GenerarPlacaHandler
{
    private ComponenteRepository $componenteRepository;
    private \RecreaSys\Domain\Usuario\UsuarioRepository $usuarioRepository;
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