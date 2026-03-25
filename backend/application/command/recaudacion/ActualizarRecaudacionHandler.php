<?php
/**
 * application/commands/recaudacion/ActualizarRecaudacionHandler.php
 *
 * Manejador del comando ActualizarRecaudacion.
 *
 * @package Reconocimiento\Application\Commands\Recaudacion
 */

namespace Reconocimiento\Application\Commands\Recaudacion;

use Reconocimiento\Domain\Recaudacion\Recaudacion;
use Reconocimiento\Domain\Recaudacion\RecaudacionRepository;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class ActualizarRecaudacionHandler
 */
final class ActualizarRecaudacionHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private MaquinaRepository $maquinaRepository;
    public function __construct(RecaudacionRepository $recaudacionRepository, MaquinaRepository $maquinaRepository){
        $this->recaudacionRepository= $recaudacionRepository;
        $this->maquinaRepository=$maquinaRepository;
    }
    public function handle(ActualizarRecaudacion $command) : void{
        $idRecaudacion = new Uuid($command->idRecaudacion());
        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);
        if(!$recaudacion){
            throw new DomainException('Recaudación no encontrada');
        }
        $idMaquina = new Uuid($command->idMaquina());
        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $recaudacion->actualizarMontos($command->montoTotal(), $command->porcentajeComercio());
        $this->recaudacionRepository->save($recaudacion);
    }
    
}