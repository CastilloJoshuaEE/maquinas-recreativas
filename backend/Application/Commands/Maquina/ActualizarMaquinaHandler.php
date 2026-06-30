<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

class ActualizarMaquinaHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ActualizarMaquinaCommand $command): void
    {
        $id = new Uuid($command->getIdMaquina());
        $maquina = $this->maquinaRepository->findById($id);
        
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada', 404);
        }
        
        $maquina->setNombre($command->getNombre());
        $maquina->setTipo($command->getTipo());
        $maquina->setIdComercio(new Uuid($command->getIdComercio()));
        
        if ($command->getEstado()) {
            $maquina->setEstado($command->getEstado());
        }
        
        $this->maquinaRepository->save($maquina);
    }
}